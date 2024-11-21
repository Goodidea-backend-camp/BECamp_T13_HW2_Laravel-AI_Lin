<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Services\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private MessageService $messageService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMessageRequest $createMessageRequest, int $threadId): MessageResource|JsonResponse
    {
        $validatedData = $createMessageRequest->validated();

        //將請求資料透過 MessageService 進行處理
        $result = $this->messageService->createMessage($threadId, $validatedData);

        return $result['status'] === 'error'
            ? $this->error($result['message'], $result['statusCode'])
            : new MessageResource($result);

    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message): void
    {
        //
    }
}
