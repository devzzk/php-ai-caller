<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Container;

/**
 * 服务提供者接口 — 向容器注册绑定
 */
interface ServiceProvider
{
    /**
     * 在容器中注册服务
     */
    public function register(Container $container): void;
}
