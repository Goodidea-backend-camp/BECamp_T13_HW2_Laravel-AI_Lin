<?php

namespace App\AI;

use OpenAI;

class Assistant
{
    public OpenAI\Client $client;

    public function __construct(protected array $messages = [])
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
    }

    public function hello(): void
    {
        echo 'hello world';
    }

    public function messages(): array
    {
        return $this->messages;
    }

    public function systemMessage(string $message): static
    {
        $this->addMessage($message, 'system');

        return $this;
    }

    public function send(string $message, ?bool $speech): ?string
    {
        $this->addMessage($message, 'assistant');

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $this->messages,
        ])->choices[0]->message->content;

        if ($response) {
            $this->addMessage($response, 'assistant');
        }

        return $speech === true ? $this->speech($response) : $response;
    }

    public function speech(string $message): string
    {
        return $this->client->audio()->speech([
            'model' => 'tts-1',
            'input' => $message,
            'voice' => 'alloy',
        ]);
    }

    public function visualize(string $description, array $options = []): string
    {
        $this->addMessage($description);

        $description = collect($this->messages)
            ->where('role', 'user')
            ->pluck('content')
            ->implode(' ');

        $options = array_merge([
            'prompt' => $description,
            'model' => 'dall-e-3',
        ], $options);

        $url = $this->client->images()->create($options)->data[0]->url;

        $this->addMessage($url, 'assistant');

        return $url;
    }

    public function reply(string $message): ?string
    {
        return $this->send($message, false);
    }

    protected function addMessage(string $message, string $role = 'user'): static
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $message,
        ];

        return $this;
    }

    //審核使用者名稱是否符合善良風俗
    public function isUsernameDecent(string $message): bool
    {
        $createResponse = $this->client->moderations()->create([
            'input' => $message,
        ]);

        // 如果flagged值為false，表示名稱符合善良風俗
        return $createResponse->results[0]->flagged === false;
    }
}
