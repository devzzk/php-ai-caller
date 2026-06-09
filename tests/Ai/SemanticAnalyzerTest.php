<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Ai;

use Devzzk\PhpAiCaller\Ai\AiCaller;
use Devzzk\PhpAiCaller\Ai\AnalysisResult;
use Devzzk\PhpAiCaller\Ai\SemanticAnalyzer;
use Devzzk\PhpAiCaller\HttpClient\Client;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Ai\SemanticAnalyzer
 */
class SemanticAnalyzerTest extends TestCase
{
    private SemanticAnalyzer $analyzer;

    protected function setUp(): void
    {
        // 规则模式：meta_analyze = false，不依赖真实 AI 调用
        $client = new Client();
        $caller = new AiCaller($client, 'https://fake-api.example', 'fake-key');

        $this->analyzer = new SemanticAnalyzer($caller, [
            'meta_analyze' => false,
        ]);
    }

    // ─── 意图识别 ────────────────────────────────────────────

    public function testDetectCodeGenerationIntent(): void
    {
        $result = $this->analyzer->analyze('用 PHP 写一个登录接口');
        $this->assertSame('代码生成', $result->intent);
    }

    public function testDetectKnowledgeIntent(): void
    {
        $result = $this->analyzer->analyze('什么是依赖注入');
        $this->assertSame('知识问答', $result->intent);
    }

    public function testDetectTranslationIntent(): void
    {
        $result = $this->analyzer->analyze('翻译这段话为英文');
        $this->assertSame('翻译', $result->intent);
    }

    public function testDetectDataAnalysisIntent(): void
    {
        $result = $this->analyzer->analyze('分析这些数据');
        $this->assertSame('数据分析', $result->intent);
    }

    public function testDetectDefaultIntent(): void
    {
        $result = $this->analyzer->analyze('你好，今天天气怎么样');
        $this->assertSame('通用对话', $result->intent);
    }

    // ─── 实体提取 ────────────────────────────────────────────

    public function testExtractQuotedEntities(): void
    {
        $result = $this->analyzer->analyze('请解释「依赖反转」和"控制反转"的区别');
        $this->assertCount(2, $result->entities);
        $this->assertSame('依赖反转', $result->entities[0]['name']);
        $this->assertSame('控制反转', $result->entities[1]['name']);
    }

    public function testExtractCodeLanguage(): void
    {
        $result = $this->analyzer->analyze("用 ```php\n``` 写代码");
        $languageEntities = array_filter($result->entities, fn (array $e) => $e['type'] === 'language');
        $this->assertCount(1, $languageEntities);
    }

    // ─── 约束提取 ────────────────────────────────────────────

    public function testExtractMaxCharsConstraint(): void
    {
        $result = $this->analyzer->analyze('写一段代码不超过100行');
        $this->assertCount(1, $result->constraints);
        $this->assertSame('max_chars', $result->constraints[0]['key']);
        $this->assertSame('100', $result->constraints[0]['value']);
    }

    public function testExtractStyleConstraint(): void
    {
        $result = $this->analyzer->analyze('用简洁风格输出内容');
        $this->assertSame('style', $result->constraints[0]['key']);
    }

    // ─── refinedPrompt ───────────────────────────────────────

    public function testRefinedPromptContainsIntent(): void
    {
        $result = $this->analyzer->analyze('写一个用户注册接口');
        $this->assertStringContainsString('代码生成', $result->refinedPrompt);
    }

    public function testRefinedPromptContainsOriginalInput(): void
    {
        $input = '用 PHP 写一个计算器';
        $result = $this->analyzer->analyze($input);
        $this->assertStringContainsString($input, $result->refinedPrompt);
    }

    // ─── 返回类型 ────────────────────────────────────────────

    public function testReturnsAnalysisResult(): void
    {
        $result = $this->analyzer->analyze('hello');
        $this->assertInstanceOf(AnalysisResult::class, $result);
    }
}
