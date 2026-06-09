# specs/ — 规格文档状态账本

## 目录结构

```
specs/
├── README.md                  ← 本文件
├── governance/                 ← 治理类 spec（全局规范、流程）
│   └── *.md
├── planned/                    ← 已规划、尚未实现的 spec
│   └── *.md
├── implemented/                ← 已落地实现的 spec
│   └── *.md
└── archived/                   ← 已废弃/被替代的 spec
    └── *.md
```

## 四种状态

| 状态 | 目录 | 含义 | 移入时机 |
|------|------|------|----------|
| **governance** | `governance/` | 项目级规范，不随功能变化，长期有效 | 首次制定时放入 |
| **planned** | `planned/` | 能力已设计，代码未实现 | 设计方案通过后 |
| **implemented** | `implemented/` | 代码已合入保护分支，可对账 | PR 合并时从 planned 移入 |
| **archived** | `archived/` | 旧接口/能力已移除或永久替代 | 废弃能力代码删除后移入 |

## 生命周期

```
[起草] → governance/   （治理类）
[起草] → planned/      （能力类）
         planned/ → implemented/   （实现完成）
         planned/ → archived/      （草稿作废）
         implemented/ → archived/  （接口废弃）
```

## 当前 spec 清单

| 文件 | 状态 | 标题 |
|------|------|------|
| `governance/contribution-workflow.md` | governance | 贡献工作流规范（分支、提交纪律） |
