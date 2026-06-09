# php-ai-caller

通用 PHP AI 调用扩展包，提供 AI 接口调用、AI 对话、AI 内容生成，以及 HTTP 客户端、常用工具类和设计模式组件。

## 安装

```bash
composer require devzzk/php-ai-caller
```

要求 PHP >= 8.1。

## 快速开始

### 容器与依赖注入（推荐）

```php
use Devzzk\PhpAiCaller\Container\Container;
use Devzzk\PhpAiCaller\Container\AiServiceProvider;
use Devzzk\PhpAiCaller\Ai\Generator;
use Devzzk\PhpAiCaller\Ai\SemanticAnalyzer;
use Devzzk\PhpAiCaller\Ai\Conversation;
use Devzzk\PhpAiCaller\HttpClient\Client;

// 1) 创建容器并注册 AI 模块
$container = new Container();

$container->registerProvider(new AiServiceProvider([
    'api_url' => 'https://api.openai.com/v1/chat/completions',
    'api_key' => 'your-api-key',
]));

// 2) 从容器解析服务
$generator = $container->make(Generator::class);

// 3) 使用 — 与手动 new 完全一致
$result = $generator->generate('用一句话介绍 PHP', ['model' => 'gpt-4']);
echo $result['choices'][0]['message']['content'];

// 同一容器内可解析任意已注册服务
$conversation = $container->make(Conversation::class);
$analyzer     = $container->make(SemanticAnalyzer::class);
```

### AI 对话与生成（手动构建）

```php
use Devzzk\PhpAiCaller\HttpClient\Client;
use Devzzk\PhpAiCaller\Ai\AiCaller;
use Devzzk\PhpAiCaller\Ai\Conversation;
use Devzzk\PhpAiCaller\Ai\Generator;

// 构建 HTTP 客户端（基于 cURL）
$client = new Client(['timeout' => 60]);

// 创建 AI 调用器
$caller = new AiCaller(
    client: $client,
    apiUrl: 'https://api.openai.com/v1/chat/completions',
    apiKey: 'your-api-key',
);

// 创建对话管理器
$conversation = new Conversation();
$conversation->addSystemMessage('你是一个有用的助手。');

// 创建生成器
$generator = new Generator($caller, $conversation);

// 生成内容
$result = $generator->generate('用一句话介绍 PHP', [
    'model' => 'gpt-4',
]);

echo $result['choices'][0]['message']['content'];
```

### 语义分析与需求拆解

在调用 AI 之前，先对用户输入进行语义分析，拆解为结构化需求，再用优化后的提示词调用 AI。

```php
use Devzzk\PhpAiCaller\Ai\SemanticAnalyzer;
use Devzzk\PhpAiCaller\Ai\AnalysisResult;

// 创建语义分析器（复用同一个 AiCaller）
$analyzer = new SemanticAnalyzer($caller, [
    'meta_analyze' => true,   // 用 AI 做元分析（默认）
    'meta_model'   => 'gpt-3.5-turbo',
]);

// 直接分析
$analysis = $analyzer->analyze('用 PHP 写一个用户注册接口，支持手机号验证，代码不超过 100 行');

echo $analysis->intent;          // "代码生成"
print_r($analysis->entities);    // [['name' => 'PHP', 'type' => 'language', 'value' => 'PHP'], ...]
print_r($analysis->constraints); // [['key' => 'max_chars', 'value' => '100'], ...]
echo $analysis->refinedPrompt;
// 【任务意图】代码生成
// 【关键信息】PHP
// 【约束条件】max_chars：100
// 【原始需求】用 PHP 写一个用户注册接口...

// 与 Generator 集成：一步完成 分析 + 生成
$generator->setAnalyzer($analyzer);

$response = $generator->generateAnalyzed('用 PHP 写一个用户注册接口，支持手机号验证，代码不超过 100 行');

echo $response['result']['choices'][0]['message']['content'];
print_r($response['analysis']); // AnalysisResult 对象
```

> AI 元分析失败时自动降级到规则拆解，不会抛异常。

### HTTP 客户端

```php
use Devzzk\PhpAiCaller\HttpClient\Client;

$client = new Client();

// GET 请求
$response = $client->get('https://api.example.com/data', [
    'Accept' => 'application/json',
]);

// POST 请求
$response = $client->post('https://api.example.com/submit', [
    'key' => 'value',
], [
    'Authorization' => 'Bearer token',
]);

echo $response['status'];   // int
echo $response['body'];     // string
echo $response['headers'];  // array
```

### 对话管理

```php
use Devzzk\PhpAiCaller\Ai\Conversation;

$conv = new Conversation();

$conv->addSystemMessage('你是一个翻译助手')
     ->addUserMessage('你好，请翻译：Hello World')
     ->addAssistantMessage('你好，世界');

$messages = $conv->getMessages();
// [
//     ['role' => 'system',    'content' => '你是一个翻译助手'],
//     ['role' => 'user',      'content' => '你好，请翻译：Hello World'],
//     ['role' => 'assistant', 'content' => '你好，世界'],
// ]

// 清空对话
$conv->clear();
```

### 工具类

#### Arr — 点号路径访问

```php
use Devzzk\PhpAiCaller\Utility\Arr;

$data = [
    'user' => [
        'name' => 'Alice',
        'role' => ['id' => 1, 'label' => 'Admin'],
    ],
];

Arr::get($data, 'user.name');          // 'Alice'
Arr::get($data, 'user.role.id');       // 1
Arr::get($data, 'user.age', 0);        // 0（默认值）

Arr::set($data, 'user.profile.bio', 'Hello');
// $data['user']['profile']['bio'] = 'Hello'
```

#### Str — 字符串操作

```php
use Devzzk\PhpAiCaller\Utility\Str;

Str::startsWith('Hello World', 'Hello');  // true
Str::endsWith('Hello World', 'World');    // true
Str::truncate('很长的一段文字...', 5);      // '很长的一段...'
```

### 设计模式

#### Singleton trait

```php
use Devzzk\PhpAiCaller\DesignPatterns\Singleton;

class AppConfig
{
    use Singleton;

    private array $config = [];

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}

$config = AppConfig::getInstance();
$config->set('debug', true);

// 任意位置获取同一实例
$same = AppConfig::getInstance();
echo $same->get('debug'); // true
```

### 异常处理

```php
use Devzzk\PhpAiCaller\Exception\HttpClientException;

try {
    $client->get('https://invalid-url.example');
} catch (HttpClientException $e) {
    echo 'HTTP 请求失败: ' . $e->getMessage();
}
```

## 目录结构

```
src/
├── Ai/
│   ├── AiCaller.php            # AI 接口调用器
│   ├── AnalysisResult.php      # 语义分析结果 DTO
│   ├── Conversation.php        # 对话管理器
│   ├── Generator.php           # 内容生成器
│   └── SemanticAnalyzer.php    # 语义分析器
├── HttpClient/
│   └── Client.php              # cURL HTTP 客户端
├── Container/
│   ├── Container.php           # DI 容器（PSR-11）
│   ├── Facade.php              # 静态代理基类
│   ├── ServiceProvider.php     # 服务提供者接口
│   └── AiServiceProvider.php   # AI 模块注册
├── Utility/
│   ├── Arr.php             # 数组工具（点号路径）
│   └── Str.php             # 字符串工具
├── DesignPatterns/
│   └── Singleton.php       # 单例模式 trait
└── Exception/
    └── HttpClientException.php
```

## 许可

MIT License
