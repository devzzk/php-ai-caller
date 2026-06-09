<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Ai;

use Devzzk\PhpAiCaller\Ai\Conversation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Ai\Conversation
 */
class ConversationTest extends TestCase
{
    private Conversation $conv;

    protected function setUp(): void
    {
        $this->conv = new Conversation();
    }

    // ─── 添加消息 ────────────────────────────────────────────

    public function testAddUserMessage(): void
    {
        $this->conv->addUserMessage('Hello');

        $messages = $this->conv->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame('user', $messages[0]['role']);
        $this->assertSame('Hello', $messages[0]['content']);
    }

    public function testAddAssistantMessage(): void
    {
        $this->conv->addAssistantMessage('Hi there');

        $messages = $this->conv->getMessages();
        $this->assertSame('assistant', $messages[0]['role']);
        $this->assertSame('Hi there', $messages[0]['content']);
    }

    public function testAddSystemMessage(): void
    {
        $this->conv->addSystemMessage('You are helpful');

        $messages = $this->conv->getMessages();
        $this->assertSame('system', $messages[0]['role']);
        $this->assertSame('You are helpful', $messages[0]['content']);
    }

    // ─── 链式调用 ────────────────────────────────────────────

    public function testFluentChaining(): void
    {
        $this->conv
            ->addSystemMessage('system prompt')
            ->addUserMessage('user prompt')
            ->addAssistantMessage('assistant reply');

        $messages = $this->conv->getMessages();
        $this->assertCount(3, $messages);
    }

    // ─── 多轮对话 ────────────────────────────────────────────

    public function testMultiTurnConversation(): void
    {
        $this->conv->addSystemMessage('sys');
        $this->conv->addUserMessage('q1');
        $this->conv->addAssistantMessage('a1');
        $this->conv->addUserMessage('q2');
        $this->conv->addAssistantMessage('a2');

        $messages = $this->conv->getMessages();
        $this->assertCount(5, $messages);
        $this->assertSame('sys', $messages[0]['content']);
        $this->assertSame('a2', $messages[4]['content']);
    }

    // ─── 清空 ────────────────────────────────────────────────

    public function testClear(): void
    {
        $this->conv->addUserMessage('hello');
        $this->conv->addAssistantMessage('hi');

        $this->conv->clear();

        $this->assertEmpty($this->conv->getMessages());
    }

    public function testClearReturnsSelf(): void
    {
        $return = $this->conv->clear();
        $this->assertSame($this->conv, $return);
    }

    // ─── 初始状态 ────────────────────────────────────────────

    public function testInitialStateIsEmpty(): void
    {
        $this->assertEmpty($this->conv->getMessages());
    }
}
