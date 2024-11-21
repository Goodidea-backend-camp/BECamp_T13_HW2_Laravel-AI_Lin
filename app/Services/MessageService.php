<?php

namespace App\Services;

use App\AI\Assistant;
use App\Enums\MessageRoleType;
use App\Enums\MessageType;
use App\Enums\ThreadType;
use App\Models\Message;
use App\Models\Thread;
use App\Traits\ServiceResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    use AuthorizesRequests;
    use ServiceResponse;

    private const ROLE_MAPPING = [
        MessageRoleType::USER->value => 'user',
        MessageRoleType::ASSISTANT->value => 'assistant',
    ];

    public function __construct(private Assistant $assistant)
    {
    }

    public function createMessage(int $threadId, array $data): ?Message
    {
        $thread = Thread::findOrFail($threadId);

        $this->storeUserMessage($thread, $data['content']);

        if ($thread->type === ThreadType::CHAT->value) {
            $historyMessages = $this->getHistoryMessages($threadId);

            //根據傳入的data判斷是文字回應還是語音回應
            return isset($data['speech'])
                ? $this->handleSpeechResponse($thread, $historyMessages)
                : $this->handleChatResponse($thread, $historyMessages);
        }

        return null;
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
            ->take(20)
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
        $response = $this->generateAssistantChatMessage($historyMessages);

        return $this->storeAssistantChatMessage($thread, $response);
    }

    // 取得AI語音回應並將回應存入資料庫
    private function handleSpeechResponse(Thread $thread, string $historyMessages): Message
    {
        $response = $this->generateAssistantSpeechMessage($historyMessages);

        return $this->storeAssistantSpeechMessage($thread, $response);
    }

    //將用戶輸入的訊息和歷史訊息合併成一個字串，並傳給AI生成文字回應
    private function generateAssistantChatMessage(string $messages): string
    {
        return $this->assistant->send($messages, false);
    }

    //將用戶輸入的訊息和歷史訊息合併成一個字串，並傳給AI生成語音回應
    private function generateAssistantSpeechMessage(string $messages): string
    {
        return $this->assistant->send($messages, true);
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
        $filePath = 'audio/'.$fileName;
        Storage::disk('public')->put($filePath, $audioContent);

        return $this->storeAssistantMessage($thread, null, $filePath);
    }
}
