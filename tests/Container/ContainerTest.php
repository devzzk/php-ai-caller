<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Container;

use Devzzk\PhpAiCaller\Container\Container;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Container\Container
 */
class ContainerTest extends TestCase
{
    // ─── bind / make ────────────────────────────────────────

    public function testMakeResolvesClassWithoutConstructor(): void
    {
        $c = new Container();
        $obj = $c->make(PlainObject::class);
        $this->assertInstanceOf(PlainObject::class, $obj);
    }

    public function testMakeResolvesWithAutowiring(): void
    {
        $c = new Container();
        $obj = $c->make(ClassA::class);
        $this->assertInstanceOf(ClassA::class, $obj);
    }

    public function testMakeResolvesNestedAutowiring(): void
    {
        $c = new Container();
        $obj = $c->make(ClassB::class);
        $this->assertInstanceOf(ClassB::class, $obj);
        $this->assertInstanceOf(ClassA::class, $obj->a);
    }

    // ─── singleton ──────────────────────────────────────────

    public function testSingletonReturnsSameInstance(): void
    {
        $c = new Container();
        $c->singleton(Ping::class);

        $a = $c->make(Ping::class);
        $b = $c->make(Ping::class);

        $this->assertSame($a, $b);
    }

    public function testBindWithoutSharedReturnsDifferentInstances(): void
    {
        $c = new Container();
        $c->bind(Ping::class);

        $a = $c->make(Ping::class);
        $b = $c->make(Ping::class);

        $this->assertNotSame($a, $b);
    }

    // ─── instance ───────────────────────────────────────────

    public function testInstanceInjectsExistingObject(): void
    {
        $c = new Container();
        $ping = new Ping();
        $c->instance(Ping::class, $ping);

        $this->assertSame($ping, $c->make(Ping::class));
    }

    // ─── bind with closure ─────────────────────────────────

    public function testBindWithClosure(): void
    {
        $c = new Container();
        $c->bind(Ping::class, fn () => new Ping());

        $obj = $c->make(Ping::class);
        $this->assertInstanceOf(Ping::class, $obj);
    }

    public function testBindClosureReceivesContainer(): void
    {
        $c = new Container();
        $c->bind(PrefixedPing::class, function (Container $container): PrefixedPing {
            $ping = $container->make(Ping::class);
            return new PrefixedPing($ping, 'pre_');
        });

        $obj = $c->make(PrefixedPing::class);
        $this->assertSame('pre_', $obj->prefix);
        $this->assertInstanceOf(Ping::class, $obj->inner);
    }

    // ─── PSR-11: get / has ─────────────────────────────────

    public function testHasReturnsTrueForBoundClass(): void
    {
        $c = new Container();
        $c->bind(Ping::class);
        $this->assertTrue($c->has(Ping::class));
    }

    public function testHasReturnsTrueForClassWithoutBinding(): void
    {
        $c = new Container();
        $this->assertTrue($c->has(PlainObject::class));
    }

    public function testHasReturnsFalseForUnknown(): void
    {
        $c = new Container();
        $this->assertFalse($c->has('NonExistentClass'));
    }

    public function testGetIsAliasForMake(): void
    {
        $c = new Container();
        $this->assertInstanceOf(Ping::class, $c->get(Ping::class));
    }

    // ─── registerProvider ────────────────────────────────

    public function testRegisterProviderAddsBindings(): void
    {
        $c = new Container();
        $provider = new PingProvider();
        $c->registerProvider($provider);

        $this->assertTrue($c->has(Ping::class));
        $this->assertInstanceOf(Ping::class, $c->make(Ping::class));
    }
}

// ─── 测试用类 ────────────────────────────────────────────────

class PlainObject
{
}

class Ping
{
}

class ClassA
{
}

class ClassB
{
    public function __construct(
        public readonly ClassA $a,
    ) {
    }
}

class PrefixedPing
{
    public function __construct(
        public readonly Ping $inner,
        public readonly string $prefix,
    ) {
    }
}

class PingProvider implements \Devzzk\PhpAiCaller\Container\ServiceProvider
{
    public function register(\Devzzk\PhpAiCaller\Container\Container $container): void
    {
        $container->bind(Ping::class);
    }
}
