# php-ai-caller

通用 PHP AI 调用扩展包，提供 AI 接口调用、AI 对话、AI 内容生成，以及 HTTP 客户端、常用工具类和设计模式组件。

## 安装

```bash
composer require devzzk/php-ai-caller
```

要求 PHP >= 8.1。

## 快速开始

### AI 对话与生成

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
│   ├── AiCaller.php        # AI 接口调用器
│   ├── Conversation.php    # 对话管理器
│   └── Generator.php       # 内容生成器
├── HttpClient/
│   └── Client.php          # cURL HTTP 客户端
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
