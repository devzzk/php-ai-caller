<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Utility;

/**
 * 字符串工具类
 */
final class Str
{
    /**
     * 判断字符串是否以指定内容开头
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * 判断字符串是否以指定内容结尾
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * 截断字符串并追加后缀
     */
    public static function truncate(string $string, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        return mb_substr($string, 0, $length) . $suffix;
    }
}
