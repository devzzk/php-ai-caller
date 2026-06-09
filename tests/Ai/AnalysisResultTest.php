<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Ai;

use Devzzk\PhpAiCaller\Ai\AnalysisResult;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Ai\AnalysisResult
 */
class AnalysisResultTest extends TestCase
{
    public function testConstructAndAccess(): void
    {
        $result = new AnalysisResult(
            intent: '代码生成',
            entities: [['name' => 'PHP', 'type' => 'language', 'value' => 'PHP']],
            constraints: [['key' => 'max_chars', 'value' => '100']],
            refinedPrompt: '结构化提示词',
        );

        $this->assertSame('代码生成', $result->intent);
        $this->assertSame([['name' => 'PHP', 'type' => 'language', 'value' => 'PHP']], $result->entities);
        $this->assertSame([['key' => 'max_chars', 'value' => '100']], $result->constraints);
        $this->assertSame('结构化提示词', $result->refinedPrompt);
    }

    public function testEmptyEntitiesAndConstraints(): void
    {
        $result = new AnalysisResult(
            intent: '通用对话',
            entities: [],
            constraints: [],
            refinedPrompt: 'hello',
        );

        $this->assertEmpty($result->entities);
        $this->assertEmpty($result->constraints);
    }
}
