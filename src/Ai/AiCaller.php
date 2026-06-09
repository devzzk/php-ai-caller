<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

use Devzzk\PhpAiCaller\HttpClient\Client;

/**
 * AI 接口调用器
 */
class AiCaller
{
    public function __construct(
        private readonly Client $client,
        private readonly string $apiUrl,
        private readonly string $apiKey,
    ) {
    }

    /**
     * 调用 AI 接口
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function call(array $payload): array
    {
        $response = $this->client->post($this->apiUrl, $payload, [
            'Authorization' => "Bearer {$this->apiKey}",
        ]);

        return json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
    }
}
