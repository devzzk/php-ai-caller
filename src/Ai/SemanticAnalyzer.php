<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

/**
 * 语义分析器 — 在调用 AI 前拆解用户需求
 *
 * 支持两种拆解模式：
 *   1. AI 元分析（meta_analyze = true，默认）— 用 AI 自身拆解用户输入
 *   2. 规则拆解（meta_analyze = false）— 基于关键词匹配，AI 元分析失败时自动降级
 */
class SemanticAnalyzer
{
    /**
     * @param AiCaller $caller   用于 AI 元分析的调用器
     * @param array<string, mixed> $options {
     *     meta_analyze: bool    是否用 AI 做元分析，默认 true
     *     meta_model: string    元分析用模型，默认 gpt-3.5-turbo
     *     max_tokens: int       元分析最大 token，默认 300
     * }
     */
    public function __construct(
        private readonly AiCaller $caller,
        private readonly array $options = []
    ) {
    }

    /**
     * 分析用户输入，返回结构化需求
     */
    public function analyze(string $userInput): AnalysisResult
    {
        if ($this->shouldMetaAnalyze()) {
            $result = $this->metaAnalyze($userInput);
            if ($result !== null) {
                return $result;
            }
        }

        return $this->ruleAnalyze($userInput);
    }

    // ─── AI 元分析 ────────────────────────────────────────────

    private function shouldMetaAnalyze(): bool
    {
        return $this->options['meta_analyze'] ?? true;
    }

    private function metaAnalyze(string $userInput): ?AnalysisResult
    {
        $metaPrompt = <<<PROMPT
你是一个需求分析器。分析以下用户输入，以 JSON 格式返回：

{
    "intent": "意图分类（代码生成 | 知识问答 | 文本创作 | 翻译 | 数据分析 | 语义检索 | 通用对话）",
    "entities": [{"name": "实体名", "type": "类型", "value": "原文值"}],
    "constraints": [{"key": "约束名", "value": "约束值"}],
    "refinedPrompt": "结构化优化后的提示词，包含意图说明 + 拆解后的子任务"
}

用户输入：
{$userInput}

只返回 JSON，不要其他内容。
PROMPT;

        try {
            $result = $this->caller->call([
                'model'    => $this->options['meta_model'] ?? 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $metaPrompt]],
                'max_tokens' => $this->options['max_tokens'] ?? 300,
            ]);

            $content = $result['choices'][0]['message']['content'] ?? '';
            $parsed = json_decode($content, true);

            if (is_array($parsed)) {
                return new AnalysisResult(
                    intent: (string) ($parsed['intent'] ?? '通用对话'),
                    entities: $parsed['entities'] ?? [],
                    constraints: $parsed['constraints'] ?? [],
                    refinedPrompt: (string) ($parsed['refinedPrompt'] ?? $userInput),
                );
            }
        } catch (\Throwable) {
            // AI 元分析失败，降级到规则拆解
        }

        return null;
    }

    // ─── 规则拆解（fallback） ─────────────────────────────────

    private function ruleAnalyze(string $userInput): AnalysisResult
    {
        $intent = $this->detectIntent($userInput);
        $entities = $this->extractEntities($userInput);
        $constraints = $this->extractConstraints($userInput);
        $refinedPrompt = $this->buildRefinedPrompt($intent, $entities, $constraints, $userInput);

        return new AnalysisResult($intent, $entities, $constraints, $refinedPrompt);
    }

    /**
     * @return array<int, array{key: string, value: string}>
     */
    private function extractConstraints(string $input): array
    {
        $constraints = [];

        $patterns = [
            '/不超过(\d+)/u'     => 'max_chars',
            '/至少(\d+)/u'      => 'min_chars',
            '/用(.*?)输出/u'    => 'output_format',
            '/([\u4e00-\u9fa5]+)风格/u' => 'style',
        ];

        foreach ($patterns as $pattern => $key) {
            if (preg_match($pattern, $input, $m)) {
                $constraints[] = ['key' => $key, 'value' => $m[1]];
            }
        }

        return $constraints;
    }

    /**
     * @return array<int, array{name: string, type: string, value: string}>
     */
    private function extractEntities(string $input): array
    {
        $entities = [];

        // 提取引号内的实体
        if (preg_match_all('/[「「『《"](.+?)[」」』》"]/u', $input, $m)) {
            foreach ($m[1] as $entity) {
                $entities[] = ['name' => $entity, 'type' => 'quoted', 'value' => $entity];
            }
        }

        // 提取 markdown 代码块中的语言标识
        if (preg_match_all('/```(\w+)/', $input, $m)) {
            foreach ($m[1] as $lang) {
                $entities[] = ['name' => $lang, 'type' => 'language', 'value' => $lang];
            }
        }

        return $entities;
    }

    private function detectIntent(string $input): string
    {
        $intentMap = [
            '/写|生成|编写|创建|实现|开发|代码/' => '代码生成',
            '/什么是|是什么|解释|介绍|原理|为什么/' => '知识问答',
            '/翻译|translate/'                     => '翻译',
            '/分析|统计|计算|数据/'                 => '数据分析',
            '/搜索|查找|检索|查一下/'                => '语义检索',
        ];

        foreach ($intentMap as $pattern => $intent) {
            if (preg_match($pattern, $input)) {
                return $intent;
            }
        }

        return '通用对话';
    }

    /**
     * 构建优化后的提示词
     *
     * @param array<int, array{name: string, type: string, value: string}> $entities
     * @param array<int, array{key: string, value: string}>               $constraints
     */
    private function buildRefinedPrompt(
        string $intent,
        array  $entities,
        array  $constraints,
        string $originalInput,
    ): string {
        $parts = ["【任务意图】{$intent}"];

        if ($entities !== []) {
            $entityList = implode('、', array_map(fn ($e) => $e['name'], $entities));
            $parts[] = "【关键信息】{$entityList}";
        }

        if ($constraints !== []) {
            $constraintList = implode('；', array_map(
                fn ($c) => "{$c['key']}：{$c['value']}",
                $constraints
            ));
            $parts[] = "【约束条件】{$constraintList}";
        }

        $parts[] = "【原始需求】{$originalInput}";

        return implode("\n", $parts);
    }
}
