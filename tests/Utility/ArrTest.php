<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Utility;

use Devzzk\PhpAiCaller\Utility\Arr;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Utility\Arr
 */
class ArrTest extends TestCase
{
    private array $data;

    protected function setUp(): void
    {
        $this->data = [
            'user' => [
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'role' => [
                    'id' => 1,
                    'label' => 'Admin',
                ],
            ],
            'settings' => [
                'theme' => 'dark',
                'lang' => 'zh',
            ],
        ];
    }

    // ─── Arr::get ────────────────────────────────────────────

    public function testGetTopLevelKey(): void
    {
        $this->assertSame(
            ['theme' => 'dark', 'lang' => 'zh'],
            Arr::get($this->data, 'settings')
        );
    }

    public function testGetNestedKey(): void
    {
        $this->assertSame('Alice', Arr::get($this->data, 'user.name'));
    }

    public function testGetDeeperNestedKey(): void
    {
        $this->assertSame('Admin', Arr::get($this->data, 'user.role.label'));
    }

    public function testGetIntValue(): void
    {
        $this->assertSame(1, Arr::get($this->data, 'user.role.id'));
    }

    public function testGetMissingKeyReturnsDefault(): void
    {
        $this->assertNull(Arr::get($this->data, 'user.age'));
    }

    public function testGetMissingKeyReturnsCustomDefault(): void
    {
        $this->assertSame('N/A', Arr::get($this->data, 'user.age', 'N/A'));
    }

    public function testGetMissingPathReturnsDefault(): void
    {
        $this->assertSame(0, Arr::get($this->data, 'user.role.permissions.admin', 0));
    }

    // ─── Arr::set ────────────────────────────────────────────

    public function testSetTopLevelKey(): void
    {
        $arr = $this->data;
        Arr::set($arr, 'version', '1.0');

        $this->assertSame('1.0', $arr['version']);
    }

    public function testSetNestedKey(): void
    {
        $arr = $this->data;
        Arr::set($arr, 'user.age', 25);

        $this->assertSame(25, $arr['user']['age']);
    }

    public function testSetCreatesIntermediateArrays(): void
    {
        $arr = [];
        Arr::set($arr, 'a.b.c', 'value');

        $this->assertSame('value', $arr['a']['b']['c']);
    }

    public function testSetOverwriteExisting(): void
    {
        $arr = $this->data;
        Arr::set($arr, 'user.name', 'Bob');

        $this->assertSame('Bob', $arr['user']['name']);
    }
}
