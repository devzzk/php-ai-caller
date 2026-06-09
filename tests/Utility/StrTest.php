<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\Utility;

use Devzzk\PhpAiCaller\Utility\Str;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\Utility\Str
 */
class StrTest extends TestCase
{
    // ─── startsWith ──────────────────────────────────────────

    public function testStartsWithTrue(): void
    {
        $this->assertTrue(Str::startsWith('Hello World', 'Hello'));
    }

    public function testStartsWithFalse(): void
    {
        $this->assertFalse(Str::startsWith('Hello World', 'World'));
    }

    public function testStartsWithCaseSensitive(): void
    {
        $this->assertFalse(Str::startsWith('Hello World', 'hello'));
    }

    // ─── endsWith ────────────────────────────────────────────

    public function testEndsWithTrue(): void
    {
        $this->assertTrue(Str::endsWith('Hello World', 'World'));
    }

    public function testEndsWithFalse(): void
    {
        $this->assertFalse(Str::endsWith('Hello World', 'Hello'));
    }

    public function testEndsWithCaseSensitive(): void
    {
        $this->assertFalse(Str::endsWith('Hello World', 'world'));
    }

    // ─── truncate ────────────────────────────────────────────

    public function testTruncateShortString(): void
    {
        $this->assertSame('Hi', Str::truncate('Hi', 10));
    }

    public function testTruncateExactLength(): void
    {
        $this->assertSame('Hello', Str::truncate('Hello', 5));
    }

    public function testTruncateLongString(): void
    {
        $this->assertSame('Hello...', Str::truncate('Hello World', 5));
    }

    public function testTruncateCustomSuffix(): void
    {
        $this->assertSame('Hello…', Str::truncate('Hello World', 5, '…'));
    }

    public function testTruncateMultibyte(): void
    {
        $this->assertSame('你好世界...', Str::truncate('你好世界哈哈哈', 4));
    }

    public function testTruncateEmptyString(): void
    {
        $this->assertSame('', Str::truncate('', 3));
    }
}
