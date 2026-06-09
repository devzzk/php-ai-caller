<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\DesignPatterns;

/**
 * 单例模式 trait
 */
trait Singleton
{
    private static ?self $instance = null;

    public static function getInstance(): static
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 禁止外部实例化
     */
    private function __construct()
    {
    }

    /**
     * 禁止克隆
     */
    private function __clone(): void
    {
    }
}
