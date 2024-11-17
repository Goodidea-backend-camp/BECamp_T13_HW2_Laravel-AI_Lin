<?php

namespace App\Services;

use App\Models\Thread;
use App\Models\User;
use App\Traits\ServiceResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ThreadService
{
    use AuthorizesRequests;
    use ServiceResponse;

    //免費用戶最大對話串數量
    public const MAX_FREE_THREADS = 3;

    // 建立新對話串
    public function createThread(User $user, array $data): Thread|array
    {
        //確認使用者是否為付費用戶
        if (! $user->is_premium) {
            //非付費用戶則檢查對話串數量
            $totalThreadCount = $this->getTotalThreadCount($user->id);
            if ($totalThreadCount >= self::MAX_FREE_THREADS) {
                return $this->formatResponse('error', 'You have reached the maximum limit of 3 threads as a free user.Please upgrade to premium to create more threads.', Response::HTTP_FORBIDDEN);
            }
        }

        //將對話儲存至資料庫
        $thread = $this->storeNewThread($user, $data);

        //增加免費用戶對話串數量
        if (! $user->is_premium) {
            $this->incrementTotalThreadCount($user->id);
        }

        return $thread;

    }

    //取得對話串數量
    private function getTotalThreadCount(int $userId): int
    {
        // 取得儲存在redis中的總對話串數量，如果沒有則回傳0
        $count = (int) Redis::get(sprintf('user:%d:total_thread_count', $userId)) ?? 0;

        return $count;
    }

    private function incrementTotalThreadCount(int $userId): void
    {
        Redis::incr(sprintf('user:%d:total_thread_count', $userId));
    }

    //將對話串儲存至資料庫
    private function storeNewThread(User $user, array $data): Thread
    {
        $thread = new Thread();
        $thread->user_id = $user->id;
        $thread->title = $data['title'];
        $thread->type = $data['type'];
        $thread->save();

        return $thread;
    }

    //查看該使用者建立的所有對話串名稱及類型
    public function getAllThreads(User $user)
    {
        return Thread::where('user_id', $user->id)->get();

    }

    //使用者更新對話串名稱
    public function updateThreadTitle(Thread $thread, array $data): Thread
    {
        $this->authorize('update', $thread);
        $thread->title = $data['title'];
        $thread->save();

        return $thread;
    }

    public function deleteThread(Thread $thread): array
    {
        $this->authorize('delete', $thread);
        $thread->delete();

        if (! $thread->user->is_premium) {
            $this->decrementTotalThreadCount($thread->user_id);
        }

        return $this->formatResponse('success', 'Thread deleted successfully', Response::HTTP_NO_CONTENT);

    }

    // 免費用戶在刪除對話時要同時刪除redis中的對話串數量
    private function decrementTotalThreadCount(int $userId): void
    {
        Redis::decr(sprintf('user:%d:total_thread_count', $userId));
    }
}
