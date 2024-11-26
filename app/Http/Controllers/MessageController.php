<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\User;
use App\Services\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private MessageService $messageService)
    {
    }

    //使用者可以查看某對話串中的所有訊息
    public function index(): ResourceCollection
    {
        $threadId = request()->route('thread');
        $result = $this->messageService->getThreadMessages($threadId);

        return MessageResource::collection($result);
    }

    //使用者建立訊息
    public function store(CreateMessageRequest $createMessageRequest, int $threadId): MessageResource|JsonResponse
    {
        $validatedData = $createMessageRequest->validated();

        //將請求資料透過 MessageService 進行處理
        $result = $this->messageService->createMessage($threadId, $validatedData);

        return $result['status'] === 'error'
            ? $this->error($result['message'], $result['statusCode'])
            : new MessageResource($result);

    }
}
