<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

/**
 * AI 对话管理器
 */
class Conversation
{
    /**
     * @var array<int, array{role: string, content: string}>
     */
    private array $messages = [];

    /**
     * 添加用户消息
     */
    public function addUserMessage(string $content): self
    {
        $this->messages[] = ['role' => 'user', 'content' => $content];
        return $this;
    }

    /**
     * 添加助手消息
     */
    public function addAssistantMessage(string $content): self
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $content];
        return $this;
    }

    /**
     * 添加系统消息
     */
    public function addSystemMessage(string $content): self
    {
        $this->messages[] = ['role' => 'system', 'content' => $content];
        return $this;
    }

    /**
     * 获取所有消息
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * 清空对话历史
     */
    public function clear(): self
    {
        $this->messages = [];
        return $this;
    }
}
