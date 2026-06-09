# PR 合并前 Spec 对账清单

> 每创建一个 PR 时，复制本清单到 PR 描述中逐项打钩确认。

---

## Spec 状态对账

- [ ] 本次变更涉及的 spec 条目状态已更新（planned → implemented / archived）
- [ ] 如有公共 API 变更，对应 spec「契约」段的方法签名与代码一致
- [ ] 如有返回值变更，对应 spec「契约」段的行为约定已同步

## 实现锚点对账

- [ ] spec 中声明的实现锚点（文件/类/方法）在分支代码中真实存在
- [ ] 无多余的、spec 未声明的公共实现锚点（即无未经设计的公共 API 泄露）

## 新增 & 废弃对账

- [ ] 新增能力有对应的 spec 文件存放在 `specs/planned/` 或 `specs/implemented/`
- [ ] 废弃能力对应的 spec 已从 `specs/implemented/` 移至 `specs/archived/`，并标注废弃原因

## Issue 归类确认

- [ ] PR 标题标注了 issue 类别（`[api]` / `[design]` / `[bug/local]` / `[root-cause]`）
- [ ] PR 描述中关联了对应 issue 编号

## 测试覆盖确认

- [ ] 新增 public 方法 ≥ 正常 + 2 异常路径的测试
- [ ] 工具类变更每个受影响方法 ≥ 3 条 case
- [ ] CI 中所有已有测试保持通过

---

## 最终检查

- [ ] spec 对账清单全部通过
- [ ] 无未归档、未定稿的 spec 草稿遗留在 `planned/`（要么实现归档，要么明确作废）
