<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\HttpClient;

use Devzzk\PhpAiCaller\Exception\HttpClientException;

class Client
{
    private array $defaultOptions = [
        'timeout' => 30,
        'connect_timeout' => 10,
    ];

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private array $options = []
    ) {
    }

    /**
     * 发送 GET 请求
     *
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array}
     * @throws HttpClientException
     */
    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, null, $headers);
    }

    /**
     * 发送 POST 请求
     *
     * @param array<string, mixed>|null $data
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array}
     * @throws HttpClientException
     */
    public function post(string $url, ?array $data = null, array $headers = []): array
    {
        if ($data !== null && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        $body = $data !== null ? json_encode($data, JSON_THROW_ON_ERROR) : null;

        return $this->request('POST', $url, $body, $headers);
    }

    /**
     * 发送 HTTP 请求
     *
     * @param array<string, string> $headers
     * @return array{status: int, body: string, headers: array}
     * @throws HttpClientException
     */
    private function request(string $method, string $url, ?string $body, array $headers): array
    {
        $ch = curl_init();

        if ($ch === false) {
            throw new HttpClientException('Failed to initialize cURL');
        }

        $options = array_replace($this->defaultOptions, $this->options);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $options['timeout'],
            CURLOPT_CONNECTTIMEOUT => $options['connect_timeout'],
            CURLOPT_HEADER         => true,
        ]);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(
                static fn(string $k, string $v): string => "{$k}: {$v}",
                array_keys($headers),
                $headers
            ));
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new HttpClientException("cURL error: {$error}");
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $responseHeaders = substr((string) $response, 0, $headerSize);
        $responseBody = substr((string) $response, $headerSize);

        return [
            'status'  => $httpCode,
            'body'    => $responseBody,
            'headers' => $this->parseHeaders($responseHeaders),
        ];
    }

    /**
     * 解析响应头
     *
     * @return array<string, string>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (str_contains($line, ': ')) {
                [$key, $value] = explode(': ', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }
}
