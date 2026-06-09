# SPEC: DI 容器与服务提供者

| 字段 | 值 |
|------|-----|
| **状态** | planned |
| **版本** | 1.0.0 |
| **更新日期** | 2026-06-09 |
| **编号** | FEAT-002 |

---

## 1. 契约 (Contract)

### 1.1 容器接口 (PSR-11 兼容)

```php
namespace Devzzk\PhpAiCaller\Container;

class Container
{
    public function bind(string $abstract, string|callable|null $concrete = null, bool $shared = false): void;
    public function singleton(string $abstract, string|callable|null $concrete = null): void;
    public function instance(string $abstract, object $instance): void;
    public function make(string $abstract): object;
    public function get(string $id): object;   // PSR-11
    public function has(string $id): bool;      // PSR-11
    public function call(callable|array $callback): mixed;
}
```

### 1.2 Facade 静态代理

```php
namespace Devzzk\PhpAiCaller\Container;

abstract class Facade
{
    protected static ?Container $container = null;
    public static function setContainer(Container $container): void;
    public static function __callStatic(string $method, array $args): mixed;
}
```

### 1.3 服务提供者接口

```php
namespace Devzzk\PhpAiCaller\Container;

interface ServiceProvider
{
    public function register(Container $container): void;
}
```

### 1.4 行为约定

1. `Container::make()` 通过反射自动解析构造参数，支持嵌套依赖
2. `singleton()` 绑定的服务全局共享同一实例
3. `bind()` 不传 `$concrete` 时，`$abstract` 类名即为 concrete
4. `AiServiceProvider` 负责注册全部 AI 模块：Client、AiCaller、Conversation、Generator、SemanticAnalyzer
5. `Facade` 子类通过 `getFacadeAccessor()` 返回容器中的绑定名

---

## 2. 验收标准

- [ ] AC-1: `Container::make()` 可通过反射自动解析无绑定依赖
- [ ] AC-2: `Container::singleton()` 保证多次解析返回同一实例
- [ ] AC-3: `Container::instance()` 可注入外部实例
- [ ] AC-4: `AiServiceProvider::register()` 一键注册全部 AI 模块
- [ ] AC-5: `Facade` 静态调用等价于从容器中解析后调用

---

## 3. 实现锚点

| 锚点 | 文件 | 说明 |
|------|------|------|
| 锚点1 | `src/Container/Container.php` | DI 容器核心 |
| 锚点2 | `src/Container/Facade.php` | 静态代理基类 |
| 锚点3 | `src/Container/ServiceProvider.php` | 服务提供者接口 |
| 锚点4 | `src/Container/AiServiceProvider.php` | AI 模块注册 |

---

## 4. 兼容影响

| 影响维度 | 评估 |
|----------|------|
| 公共 API | **新增** Container、Facade、ServiceProvider，现有类签名不变 |
| 内部实现 | 无破坏性变更，容器为可选替代 new 的方式 |
| 依赖关系 | 无外部依赖 |
