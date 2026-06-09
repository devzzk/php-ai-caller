<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

/**
 * AI 内容生成器
 */
class Generator
{
    public function __construct(
        private readonly AiCaller $caller,
        private readonly Conversation $conversation,
    ) {
    }

    /**
     * 根据提示词生成内容
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function generate(string $prompt, array $options = []): array
    {
        $this->conversation->addUserMessage($prompt);

        $payload = array_merge([
            'messages' => $this->conversation->getMessages(),
            'model'    => $options['model'] ?? 'gpt-3.5-turbo',
        ], $options);

        $result = $this->caller->call($payload);

        if (isset($result['choices'][0]['message']['content'])) {
            $this->conversation->addAssistantMessage($result['choices'][0]['message']['content']);
        }

        return $result;
    }
}
