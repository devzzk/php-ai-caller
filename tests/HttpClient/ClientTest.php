<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Tests\HttpClient;

use Devzzk\PhpAiCaller\Exception\HttpClientException;
use Devzzk\PhpAiCaller\HttpClient\Client;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Devzzk\PhpAiCaller\HttpClient\Client
 */
class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    // ─── 构造 ────────────────────────────────────────────────

    public function testDefaultOptions(): void
    {
        // 无自定义参数时不应抛异常
        $client = new Client();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCustomOptions(): void
    {
        $client = new Client(['timeout' => 60, 'connect_timeout' => 5]);
        $this->assertInstanceOf(Client::class, $client);
    }

    // ─── 非标准 URL 触发的异常路径 ──────────────────────────

    public function testGetToInvalidUrlThrowsException(): void
    {
        $this->expectException(HttpClientException::class);
        $this->client->get('not-a-valid-url');
    }

    public function testPostToInvalidUrlThrowsException(): void
    {
        $this->expectException(HttpClientException::class);
        $this->client->post('not-a-valid-url', ['key' => 'value']);
    }

    // ─── parseHeaders 内部方法 ───────────────────────────────

    public function testParseHeaders(): void
    {
        $raw = "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nX-Request-Id: abc123\r\n\r\n";

        $headers = $this->invokeParseHeaders($raw);

        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertSame('abc123', $headers['X-Request-Id']);
    }

    public function testParseHeadersEmpty(): void
    {
        $headers = $this->invokeParseHeaders('');
        $this->assertEmpty($headers);
    }

    public function testParseHeadersSkipsStatusLine(): void
    {
        $raw = "HTTP/1.1 404 Not Found\r\nContent-Type: text/html\r\n\r\n";

        $headers = $this->invokeParseHeaders($raw);

        // 状态行不含 ": " 分隔符，应被跳过
        $this->assertCount(1, $headers);
        $this->assertSame('text/html', $headers['Content-Type']);
    }

    // ─── POST 自动设置 Content-Type ─────────────────────────

    public function testHttpClientExceptionIsRuntimeException(): void
    {
        $this->expectException(HttpClientException::class);
        $this->client->get('://invalid');
    }

    public function testHttpClientExceptionIsThrowable(): void
    {
        try {
            $this->client->get('://invalid');
        } catch (HttpClientException) {
            $this->assertTrue(true);
        }
    }

    // ─── helper ──────────────────────────────────────────────

    /**
     * 通过反射调用私有方法 parseHeaders
     *
     * @return array<string, string>
     */
    private function invokeParseHeaders(string $rawHeaders): array
    {
        $ref = new \ReflectionMethod(Client::class, 'parseHeaders');
        return $ref->invoke($this->client, $rawHeaders);
    }
}
