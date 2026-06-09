<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

/**
 * AI 内容生成器
 */
class Generator
{
    private ?SemanticAnalyzer $analyzer = null;

    public function __construct(
        private readonly AiCaller $caller,
        private readonly Conversation $conversation,
    ) {
    }

    /**
     * 设置语义分析器（链式调用）
     */
    public function setAnalyzer(SemanticAnalyzer $analyzer): self
    {
        $this->analyzer = $analyzer;
        return $this;
    }

    /**
     * 根据提示词生成内容（不做语义分析）
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

    /**
     * 先语义分析拆解用户需求，再生成内容
     *
     * @param array<string, mixed> $options
     * @return array{result: array<string, mixed>, analysis: AnalysisResult}
     */
    public function generateAnalyzed(string $prompt, array $options = []): array
    {
        $analyzer = $this->analyzer ?? new SemanticAnalyzer($this->caller);

        $analysis = $analyzer->analyze($prompt);

        $result = $this->generate($analysis->refinedPrompt, $options);

        return [
            'result'   => $result,
            'analysis' => $analysis,
        ];
    }
}
