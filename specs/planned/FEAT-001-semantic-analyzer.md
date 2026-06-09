# SPEC: 语义分析器

| 字段 | 值 |
|------|-----|
| **状态** | planned |
| **版本** | 1.0.0 |
| **更新日期** | 2026-06-09 |
| **编号** | FEAT-001 |

---

## 1. 契约 (Contract)

### 1.1 类与方法签名

```php
namespace Devzzk\PhpAiCaller\Ai;

class SemanticAnalyzer
{
    /**
     * @param AiCaller $caller     用于元分析（用 AI 拆解用户需求）
     * @param array<string, mixed> $options  默认选项
     */
    public function __construct(
        private readonly AiCaller $caller,
        private readonly array $options = []
    ) {}

    /**
     * 分析用户输入，拆解为结构化需求
     *
     * @return AnalysisResult { intent: string, entities: array, constraints: array, refinedPrompt: string }
     */
    public function analyze(string $userInput): AnalysisResult;
}
```

### 1.2 行为约定

1. `analyze()` 接收原始用户输入，返回结构化的 `AnalysisResult`
2. `AnalysisResult` 包含：
   - `intent` — 用户意图分类（如「代码生成」「知识问答」「翻译」等）
   - `entities` — 提取的关键实体列表 `[{name, type, value}]`
   - `constraints` — 约束条件列表 `[{key, value}]`
   - `refinedPrompt` — 经语义拆解后优化过的提示词，可直接发送给 AI
3. 当 `options['meta_analyze'] = true`（默认），内部用 AI 做元分析拆解
4. 当 `options['meta_analyze'] = false`，使用基于规则的关键词匹配拆解
5. 规则拆解作为 AI 元分析的 fallback：AI 调用失败时自动降级

### 1.3 AnalysisResult DTO

```php
namespace Devzzk\PhpAiCaller\Ai;

readonly class AnalysisResult
{
    public function __construct(
        public string $intent,
        public array  $entities,
        public array  $constraints,
        public string $refinedPrompt,
    ) {}
}
```

---

## 2. 验收标准

- [ ] AC-1: `SemanticAnalyzer::analyze()` 返回 `AnalysisResult` 对象
- [ ] AC-2: `intent` 字段正确识别用户意图
- [ ] AC-3: `entities` 从非结构化文本中提取关键实体
- [ ] AC-4: `refinedPrompt` 是拆解优化后的提示词，可直接用于 AI 调用
- [ ] AC-5: AI 元分析失败时自动降级到规则拆解，不抛异常
- [ ] AC-6: `Generator` 可通过 `setAnalyzer()` 集成语义分析能力

---

## 3. 实现锚点

| 锚点 | 文件 | 说明 |
|------|------|------|
| 锚点1 | `src/Ai/SemanticAnalyzer.php` | 语义分析器核心类 |
| 锚点2 | `src/Ai/AnalysisResult.php` | 分析结果 DTO |
| 锚点3 | `src/Ai/Generator.php` | 集成 `setAnalyzer()` 方法 |

---

## 4. 兼容影响

| 影响维度 | 评估 |
|----------|------|
| 公共 API | **新增** `SemanticAnalyzer`、`AnalysisResult` 两个类 |
| 内部实现 | Generator 新增可选依赖，非破坏性变更 |
| 依赖关系 | 无新增外部依赖 |
| 数据/配置 | 无影响 |
