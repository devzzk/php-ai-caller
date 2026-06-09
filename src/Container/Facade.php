<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Container;

use BadMethodCallException;

/**
 * 静态代理基类 — 子类通过 getFacadeAccessor() 返回容器绑定名
 *
 * 用法：
 *   class Ai extends Facade { protected static function getFacadeAccessor(): string { return 'ai-caller'; } }
 *   Ai::call($payload);
 */
abstract class Facade
{
    private static ?Container $container = null;

    /**
     * 设置全局容器
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    /**
     * 获取当前容器
     */
    protected static function getContainer(): Container
    {
        if (self::$container === null) {
            throw new BadMethodCallException('Facade container not set. Call Facade::setContainer() first.');
        }
        return self::$container;
    }

    /**
     * 子类必须返回容器中的绑定标识
     */
    abstract protected static function getFacadeAccessor(): string;

    /**
     * 将静态调用代理到容器中的服务实例
     *
     * @param array<int, mixed> $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getContainer()->make(static::getFacadeAccessor());
        return $instance->{$method}(...$args);
    }
}
