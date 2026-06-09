<?php

declare(strict_types=1);

namespace Devzzk\PhpAiCaller\Utility;

/**
 * 数组工具类
 */
final class Arr
{
    /**
     * 使用点号路径获取嵌套数组的值
     *
     * @param array<string, mixed> $array
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        foreach ($keys as $segment) {
            if (!isset($array[$segment])) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * 使用点号路径设置嵌套数组的值
     *
     * @param array<string, mixed> $array
     */
    public static function set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $ref = &$array;
        foreach ($keys as $segment) {
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref = $value;
    }
}
