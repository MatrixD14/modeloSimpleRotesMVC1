<?php

class Env
{
    private static array $data = [];
    private static bool $loaded = false;

    public static function load(string $file): void
    {
        if (self::$loaded) return;
        if (!is_file($file))
            throw new Exception("file config no exist: {$file}");
        $parsed = parse_ini_file($file, true, INI_SCANNER_TYPED);

        if ($parsed === false)
            throw new Exception("Erro the process file config.");
        self::$data = self::resolveInheritance($parsed);
        self::$loaded = true;
    }

    private static function resolveInheritance(array $config): array
    {
        $result = [];
        foreach ($config as $section => $values) {
            $parts = explode(':', $section, 2);
            $name = trim($parts[0]);
            $parent = isset($parts[1])
                ? trim($parts[1])
                : null;
            if ($parent && isset($config[$parent])) {
                $result[$name] = array_merge(
                    $config[$parent],
                    $values
                );
            } else {
                $result[$name] = $values;
            }
        }
        return $result;
    }

    public static function get(string $section, ?string $key = null, $default = null)
    {
        if (!isset(self::$data[$section])) return $default;
        if ($key === null) return self::$data[$section];
        return self::$data[$section][$key] ?? $default;
    }
    public static function has(string $section, ?string $key = null): bool
    {
        if (!isset(self::$data[$section])) return false;

        if ($key === null) return true;

        return array_key_exists(
            $key,
            self::$data[$section]
        );
    }

    public static function all(): array
    {
        return self::$data;
    }
}
