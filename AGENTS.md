# AGENTS.md — devzzk/php-ai-caller

## 1. Issue 分类

| 类别 | 代号 | 判据 | 示例 |
|------|------|------|------|
| 局部 Bug | `bug/local` | 仅影响单一方法/类，不影响公共 API | `Str::truncate` 多字节截断偏移错误 |
| 设计变更 | `design` | 类结构、调用链、依赖关系调整，不改公共接口签名 | HTTP Client 替换底层 curl 为 stream |
| 公共接口/兼容影响 | `api` | 修改了 `public` / `protected` 方法签名、参数类型、返回值类型 | `AiCaller::call()` 返回值结构变更 |
| 多 issue 同根因 | `root-cause` | 多个 issue 指向同一底层缺陷，需先修根因后复验 | 3 个报错都指向 `Client::request` cURL 句柄泄露 |

任何新增 issue 必须在标题前缀标注类别，例如：`[api] AiCaller::call 参数签名调整`。

## 2. 分支命名

```
feature/{issue-id}-{简短描述}
bugfix/{issue-id}-{简短描述}
hotfix/{issue-id}-{简短描述}
update/{issue-id}-{简短描述}
refactor/{issue-id}-{简短描述}
docs/{issue-id}-{简短描述}
```

保护分支：`main`、`master`、`develop`，禁止直接提交。

## 3. 测试要求

| 场景 | 最低要求 |
|------|----------|
| 新增 public 方法 | 单元测试覆盖正常路径 + 2 种异常路径 |
| 修复 bug | 先复现测试（RED），再修复（GREEN） |
| 公共接口变更 | 单元测试 + 上下游调用方回归 |
| 工具类变更 | 每个受影响方法至少 3 条 case |
| 设计变更 | 原行为用例全部保持通过 |

测试框架：PHPUnit ^10.0，测试目录 `tests/`，命名空间 `Devzzk\PhpAiCaller\Tests\`。

## 4. Spec 对账规则

每次 PR 合并前，必须完成以下对账：

- [ ] 本次变更涉及的 spec 条目是否已更新？
- [ ] 若有公共 API 变更，对应 spec 的「契约」段是否匹配代码签名？
- [ ] 若 spec 有「实现锚点」，对应文件/行是否存在？
- [ ] 新增能力是否有对应的 spec（新建于 `specs/planned/`）？
- [ ] 废弃能力对应的 spec 是否移至 `specs/archived/`？

spec 状态说明见 `specs/README.md`。

## 5. 完成定义 (Definition of Done)

一项任务视为"已完成"，必须同时满足：

1. 代码在对应命名分支上通过所有已有单元测试
2. 新增/修改的 public API 有对应测试覆盖
3. spec 文档已按状态归档（planned → implemented；废弃 → archived）
4. 无 PHPStan / Psalm 级别 ≥ 5 的静态分析新告警
5. PR 标题包含 issue 类别标签
6. 经由 review 确认 spec 对账清单通过
