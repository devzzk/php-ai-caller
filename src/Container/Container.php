<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Container;

use RuntimeException;

/**
 * 轻量级 DI 容器，PSR-11 兼容
 *
 * 支持：
 *   - 自动依赖解析（反射构造器）
 *   - 单例/多例绑定
 *   - 实例注入
 *   - 函数/方法调用自动注入
 */
class Container
{
    /**
     * 绑定记录
     * @var array<string, array{concrete: string|callable, shared: bool}>
     */
    private array $bindings = [];

    /**
     * 已解析的共享实例
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * 将抽象名绑定到具体实现（多例）
     *
     * @param string|callable|null $concrete 传入 null 则以 $abstract 自身为 concrete
     */
    public function bind(string $abstract, string|callable|null $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared'   => $shared,
        ];
    }

    /**
     * 将抽象名绑定为单例
     */
    public function singleton(string $abstract, string|callable|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 注入一个已存在的实例
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * 注册服务提供者
     */
    public function registerProvider(ServiceProvider $provider): void
    {
        $provider->register($this);
    }

    /**
     * 从容器解析服务（使用自动装配）
     *
     * @template T of object
     * @param class-string<T> $abstract
     * @return T
     */
    public function make(string $abstract): object
    {
        // 已有共享实例直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;
        $shared   = $this->bindings[$abstract]['shared'] ?? false;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } elseif ($concrete !== $abstract && class_exists($concrete)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->build($abstract);
        }

        if ($shared) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * PSR-11: 从容器获取服务
     *
     * @throws RuntimeException
     */
    public function get(string $id): object
    {
        return $this->make($id);
    }

    /**
     * PSR-11: 检查是否可解析
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || class_exists($id);
    }

    /**
     * 调用方法/函数，自动注入容器中的依赖
     *
     * @param callable|array{object|class-string, string} $callback
     * @return mixed
     */
    public function call(callable|array $callback): mixed
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;
            $ref = new \ReflectionMethod($class, $method);
            $object = is_object($class) ? $class : $this->make($class);
        } else {
            $ref = new \ReflectionFunction($callback(...));
            $object = null;
        }

        $args = $this->resolveParameters($ref);

        return $ref->isStatic()
            ? $ref->invokeArgs(null, $args)
            : $ref->invokeArgs($object, $args);
    }

    // ─── internal ──────────────────────────────────────────

    /**
     * 反射构建对象
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function build(string $class): object
    {
        $ref = new \ReflectionClass($class);

        $constructor = $ref->getConstructor();
        if ($constructor === null) {
            return $ref->newInstance();
        }

        $args = $this->resolveParameters($constructor);

        return $ref->newInstanceArgs($args);
    }

    /**
     * 解析方法的参数依赖
     *
     * @return array<int, mixed>
     */
    private function resolveParameters(\ReflectionFunctionAbstract $ref): array
    {
        $args = [];

        foreach ($ref->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } elseif ($param->isVariadic()) {
                break;
            } else {
                throw new RuntimeException(
                    "Cannot resolve parameter \${$param->getName()} for {$ref->getName()}"
                );
            }
        }

        return $args;
    }
}
