<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Services\ThreadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

class ThreadController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ThreadService $ThreadService
    ) {
    }

    //使用者可以查看自己的所有 Thread（只包括對話串名稱以及類型）
    public function index(): ResourceCollection
    {
        $user = auth()->user();
        $result = $this->ThreadService->getAllThreads($user);

        return ThreadResource::collection($result);
    }

    //使用者建立對話串
    public function store(CreateThreadRequest $createThreadRequest): ThreadResource|JsonResponse
    {
        $validatedData = $createThreadRequest->validated();
        $user = auth()->user();

        //將請求資料透過 ThreadService 進行處理
        $result = $this->ThreadService->createThread($user, $validatedData);

        return $result['status'] === 'error'
            ? $this->error($result['message'], $result['statusCode'])
            : new ThreadResource($result);
    }

    //使用者更新對話串名稱
    public function update(UpdateThreadRequest $updateThreadRequest, Thread $thread): ThreadResource
    {
        $validatedData = $updateThreadRequest->validated();
        $result = $this->ThreadService->updateThreadTitle($thread, $validatedData);

        return new ThreadResource($result);
    }

    //使用者刪除對話串
    public function destroy(Thread $thread): JsonResponse
    {
        $result = $this->ThreadService->deleteThread($thread);

        return $this->success($result['message'], $result['statusCode']);
    }
}
