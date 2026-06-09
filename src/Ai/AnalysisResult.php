<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Ai;

/**
 * 语义分析结果
 *
 * @property-read array<array{name: string, type: string, value: string}> $entities
 * @property-read array<array{key: string, value: string}> $constraints
 */
readonly class AnalysisResult
{
    /**
     * @param string $intent        用户意图分类
     * @param array  $entities      提取的关键实体
     * @param array  $constraints   约束条件
     * @param string $refinedPrompt 拆解优化后的提示词
     */
    public function __construct(
        public string $intent,
        public array  $entities,
        public array  $constraints,
        public string $refinedPrompt,
    ) {
    }
}
