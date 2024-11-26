<?php

namespace App\Services;

use App\AI\Assistant;
use App\Enums\MessageRoleType;
use App\Enums\MessageType;
use App\Enums\ThreadType;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use App\Traits\ServiceResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MessageService
{
    use AuthorizesRequests;
    use ServiceResponse;

    private $redis;

    private const MAX_FREE_MESSAGES = 10;

    private const MESSAGE_HISTORY_LIMIT = 20;

    private const AUDIO_FILE_PATH = 'audio/';

    private const ROLE_MAPPING = [
        MessageRoleType::USER->value => 'user',
        MessageRoleType::ASSISTANT->value => 'assistant',
    ];

    public function __construct(private Assistant $assistant)
    {
        $this->redis = Redis::connection();
    }

    //使用者建立訊息，並在建立訊息前檢查是否為免費用戶，如果是免費用戶則檢查是否超過最大限制
    public function createMessage(int $threadId, array $data): Message|array
    {
        $thread = Thread::findOrFail($threadId);

        $user = User::findOrFail($thread->user_id);

        if (! $user->is_premium) {

            $newTotalMessageCount = $this->checkAndUpdateMessageCount($threadId);

            if ($newTotalMessageCount === -1) {
                return $this->formatResponse('error', 'You have reached the maximum limit of messages as a free user. Please upgrade to premium to create more messages.', Response::HTTP_FORBIDDEN);
            }
        }

        $this->storeUserMessage($thread, $data['content']);
        $historyMessages = $this->getHistoryMessages($threadId);

        //根據thread的type判斷是生成文字回應還是圖片回應
        if ($thread->type === ThreadType::CHAT->value) {

            //根據傳入的data判斷是文字回應還是語音回應
            return isset($data['speech'])
                ? $this->handleSpeechResponse($thread, $historyMessages)
                : $this->handleChatResponse($thread, $historyMessages);
        }

        return $this->handleImageGenerationResponse($thread, $historyMessages);

    }

    //使用者可以查看特定對話串的全部訊息
    public function getThreadMessages(int $threadId): Collection
    {
        $thread = Thread::findOrFail($threadId);
        $this->authorize('view', $thread);

        return Message::select(['role', 'content', 'file_path'])
            ->where('thread_id', $threadId)
            ->latest()
            ->get();
    }

    //檢查是否超過最大限制並更新訊息數量
    private function checkAndUpdateMessageCount(int $threadId): int
    {
        $key = sprintf('thread:%d:total_message_count', $threadId);

        // 透過 Lua Script 來檢查是否超過最大限制並新增訊息數量
        $newTotalMessageCount = $this->redis->eval("
            local key = KEYS[1]
            local current_count = tonumber(redis.call('GET', key))
            if current_count == nil then
                current_count = 0  -- 如果返回值是 nil，設定為 0
            end
            if current_count >= tonumber(ARGV[1]) then
                return -1  -- 超過最大限制
            end
            local new_count = current_count + 1
            redis.call('SET', key, new_count)
            return new_count
        ", 1, $key, self::MAX_FREE_MESSAGES);

        return $newTotalMessageCount;
    }

    //取得對話串中的所有歷史訊息並合併成一個字串
    private function getHistoryMessages(int $threadId): string
    {
        $messages = $this->fetchThreadMessages($threadId);

        return $this->formatMessagesContext($messages);
    }

    //取得對話串中的所有歷史訊息，最多20筆
    private function fetchThreadMessages(int $threadId): Collection
    {
        return Message::where('thread_id', $threadId)
            ->select(['role', 'content', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->take(self::MESSAGE_HISTORY_LIMIT)
            ->get()
            ->reverse();
    }

    //將歷史訊息合併成一個字串
    private function formatMessagesContext(Collection $messages): string
    {
        return $messages->map(function ($message): string {
            $role = self::ROLE_MAPPING[$message->role] ?? 'user';

            return sprintf('%s: %s', $role, $message->content);
        })->implode("\n");
    }

    // 取得AI文字回應並將回應存入資料庫
    private function handleChatResponse(Thread $thread, string $historyMessages): Message
    {
        $response = $this->assistant->send($historyMessages, false);

        return $this->storeAssistantChatMessage($thread, $response);
    }

    // 取得AI語音回應並將回應存入資料庫
    private function handleSpeechResponse(Thread $thread, string $historyMessages): Message
    {
        $response = $this->assistant->send($historyMessages, true);

        return $this->storeAssistantSpeechMessage($thread, $response);
    }

    // 取得AI圖片回應並將回應存入資料庫
    private function handleImageGenerationResponse(Thread $thread, string $historyMessages): Message
    {
        $response = $this->assistant->visualize($historyMessages);

        return $this->storeAssistantImageMessage($thread, $response);
    }

    //將用戶輸入的訊息儲存至資料庫
    private function storeUserMessage(Thread $thread, string $content): void
    {
        $message = new Message();
        $message->thread_id = $thread->id;
        $message->role = MessageRoleType::USER->value;
        $message->type = MessageType::TEXT->value;
        $message->content = $content;
        $message->save();
    }

    //將AI生成的回應儲存至資料庫
    private function storeAssistantMessage(Thread $thread, ?string $content, ?string $filePath = null): Message
    {
        $message = new Message();
        $message->thread_id = $thread->id;
        $message->role = MessageRoleType::ASSISTANT->value;
        $message->type = MessageType::TEXT->value;
        $message->content = $content;

        if ($filePath !== null && $filePath !== '' && $filePath !== '0') {
            $message->file_path = $filePath;
        }

        $message->save();

        return $message;
    }

    //將AI生成的文字回應儲存至資料庫
    private function storeAssistantChatMessage(Thread $thread, string $content): Message
    {
        return $this->storeAssistantMessage($thread, $content);
    }

    //將AI生成的語音回應檔案儲存進專案資料夾，並將語音回應檔案路徑儲存至資料庫
    private function storeAssistantSpeechMessage(Thread $thread, string $audioContent): Message
    {
        $timestamp = now()->format('Ymd_His');
        $fileName = sprintf('assistant_speech_thread_%s_%s.mp3', $thread->id, $timestamp);
        $filePath = self::AUDIO_FILE_PATH.$fileName;
        Storage::disk('public')->put($filePath, $audioContent);

        return $this->storeAssistantMessage($thread, null, $filePath);
    }

    //將AI生成的圖片路徑儲存至資料庫
    private function storeAssistantImageMessage(Thread $thread, string $imageFilePath): Message
    {
        return $this->storeAssistantMessage($thread, null, $imageFilePath);
    }
}
