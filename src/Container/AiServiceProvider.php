<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Container;

use Devzzk\PhpAiCaller\Ai\AiCaller;
use Devzzk\PhpAiCaller\Ai\Conversation;
use Devzzk\PhpAiCaller\Ai\Generator;
use Devzzk\PhpAiCaller\Ai\SemanticAnalyzer;
use Devzzk\PhpAiCaller\HttpClient\Client;

/**
 * AI 模块服务提供者
 *
 * 向容器注册所有 AI 相关服务的默认绑定。
 * 使用者可通过容器先设置自定义配置（如 apiKey），再调用此 provider。
 */
class AiServiceProvider implements ServiceProvider
{
    /**
     * @param array<string, mixed> $config {
     *     api_url: string  AI API 地址
     *     api_key: string  AI API 密钥
     *     meta_analyze: bool  是否启用 AI 元分析
     *     meta_model: string  元分析模型
     *     http_options: array  HTTP 客户端选项
     * }
     */
    public function __construct(
        private readonly array $config = []
    ) {
    }

    public function register(Container $container): void
    {
        // HTTP 客户端 — 多例
        $container->bind(Client::class, null, false);

        // AI 调用器 — 多例（每次解析使用 config 中的凭据）
        $container->bind(AiCaller::class, function (Container $c) {
            $client = $c->make(Client::class);
            $url    = $this->config['api_url'] ?? throw new \RuntimeException('Missing config: api_url');
            $key    = $this->config['api_key'] ?? throw new \RuntimeException('Missing config: api_key');
            return new AiCaller($client, $url, $key);
        });

        // 对话管理器 — 多例
        $container->bind(Conversation::class);

        // 语义分析器 — 多例
        $container->bind(SemanticAnalyzer::class, function (Container $c) {
            $caller  = $c->make(AiCaller::class);
            $options = [];
            if (isset($this->config['meta_analyze'])) {
                $options['meta_analyze'] = $this->config['meta_analyze'];
            }
            if (isset($this->config['meta_model'])) {
                $options['meta_model'] = $this->config['meta_model'];
            }
            return new SemanticAnalyzer($caller, $options);
        });

        // 内容生成器 — 多例
        $container->bind(Generator::class, function (Container $c) {
            $generator = new Generator(
                $c->make(AiCaller::class),
                $c->make(Conversation::class),
            );
            // 自动注入语义分析器
            $analyzer = $c->make(SemanticAnalyzer::class);
            $generator->setAnalyzer($analyzer);
            return $generator;
        });
    }
}
