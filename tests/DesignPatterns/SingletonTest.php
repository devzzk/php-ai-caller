<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\DesignPatterns;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\DesignPatterns\Singleton
 */
class SingletonTest extends TestCase
{
    public function testGetInstanceReturnsSameObject(): void
    {
        $a = SingletonTestConcrete::getInstance();
        $b = SingletonTestConcrete::getInstance();

        $this->assertSame($a, $b);
    }

    public function testGetInstanceStoresIndependentPerClass(): void
    {
        $a = SingletonTestConcrete::getInstance();
        $b = SingletonTestConcreteB::getInstance();

        $this->assertNotSame($a, $b);
    }

    public function testInstanceCanHoldState(): void
    {
        $instance = SingletonTestConcrete::getInstance();
        $instance->value = 'set';

        $same = SingletonTestConcrete::getInstance();
        $this->assertSame('set', $same->value);
    }
}

// ─── 测试用具体类 ──────────────────────────────────────────────

class SingletonTestConcrete
{
    use \Devzzk\PhpAiCaller\DesignPatterns\Singleton;

    public mixed $value = null;
}

class SingletonTestConcreteB
{
    use \Devzzk\PhpAiCaller\DesignPatterns\Singleton;
}
