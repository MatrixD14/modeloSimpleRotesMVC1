<?php
//autoload.json default : [{ "app\\" : "app"}, "file" :{ "routers" : "path/file"}]
class LightAutoloader
{
    // Caminhos relativos à raiz do projeto (onde está o autoload.json)
    private static string $baseDir = __DIR__ . '/../';   // ajuste se necessário
    private static string $configFile = __DIR__ . '/../autoload.json';
    public static string $classmapFile = __DIR__ . '/classmap.php';
    private static ?string $projectRoot = null;

    // Obtém a raiz do projeto a partir do config

    private static function getProjectRoot(): string
    {
        if (self::$projectRoot !== null) {
            return self::$projectRoot;
        }
        $config = json_decode(file_get_contents(self::$configFile), true);
        $rootRelative = $config[1]['root'] ?? '../';  // fallback
        self::$projectRoot = realpath(self::$baseDir . $rootRelative);
        if (self::$projectRoot === false) {
            throw new \Exception("Root do projeto não encontrado: " . self::$baseDir . $rootRelative);
        }
        return self::$projectRoot;
    }

    // Modo CLI: gera o classmap.php

    public static function dump(): void
    {
        $config = json_decode(file_get_contents(self::$configFile), true);
        $psr4 = $config[0] ?? [];
        $files = $config[1]['file'] ?? [];
        $projectRoot = self::getProjectRoot();

        $classmap = [];

        // 1) Mapeamentos diretos (files)
        foreach ($files as $class => $relativePath) {
            $fullPath = $projectRoot . '/' . ltrim($relativePath, '/');
            if (file_exists($fullPath)) {
                // Salva o caminho relativo à raiz do projeto
                $classmap[$class] = $relativePath;
            } else {
                echo "⚠️ Arquivo não encontrado: {$fullPath}\n";
            }
        }

        // 2) Escaneia diretórios PSR-4
        foreach ($psr4 as $prefix => $dir) {
            $fullDir = $projectRoot . '/' . rtrim($dir, '/');
            if (!is_dir($fullDir)) {
                echo "⚠️ Diretório não encontrado: {$fullDir}\n";
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullDir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                    $namespace = trim($matches[1]);
                    $className = $file->getBasename('.php');
                    $fullClass = $namespace . '\\' . $className;
                    if (strpos($fullClass, $prefix) === 0) {
                        // Caminho relativo à raiz do projeto
                        $relativePath = substr($file->getRealPath(), strlen($projectRoot) + 1);
                        $classmap[$fullClass] = $relativePath;
                        // $classmap[$fullClass] = '__DIR__ ."/'  . ltrim($relativePath, '/')  . '"';
                    }
                }
            }
        }

        $content = '<?php return ' . var_export($classmap, true) . ';';
        file_put_contents(self::$classmapFile, $content);
        echo "✅ classmap.php gerado com " . count($classmap) . " classes.\n";
        echo "📁 Local: " . self::$classmapFile . "\n";
    }

    // Autoloader otimizado (usa classmap.php gerado)

    public static function register(): void
    {
        if (!file_exists(self::$classmapFile)) {
            throw new \Exception("classmap.php não encontrado. Execute 'php core/autoload.php dump' para gerar.");
        }
        $classmap = require self::$classmapFile;
        $projectRoot = self::getProjectRoot();

        spl_autoload_register(function ($class) use ($classmap, $projectRoot) {
            if (isset($classmap[$class])) {
                $fullPath = $projectRoot . '/' . $classmap[$class];
                require $fullPath;
            }
        });
    }

    // Fallback dinâmico (útil em desenvolvimento sem gerar o classmap)
    public static function registerDynamic(): void
    {
        $config = json_decode(file_get_contents(self::$configFile), true);
        $psr4 = $config[0] ?? [];
        $files = $config[1]['file'] ?? [];
        $projectRoot = self::getProjectRoot();

        spl_autoload_register(function ($class) use ($psr4, $files, $projectRoot) {
            if (isset($files[$class])) {
                $fullPath = $projectRoot . '/' . ltrim($files[$class], '/');
                if (file_exists($fullPath)) {
                    require $fullPath;
                    return;
                }
            }
            foreach ($psr4 as $prefix => $dir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) === 0) {
                    $relative = substr($class, $len);
                    $fullPath = $projectRoot . '/' . $dir . '/' . str_replace('\\', '/', $relative) . '.php';
                    if (file_exists($fullPath)) {
                        require $fullPath;
                        return;
                    }
                }
            }
        });
    }
}

// Execução: modo CLI (dump) ou modo web (autoload)

if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === 'dump') {
    LightAutoloader::dump();
    exit;
}

// Modo normal: tenta usar classmap.php, se não existir usa fallback dinâmico
if (file_exists(LightAutoloader::$classmapFile)) {
    LightAutoloader::register();
} else {
    // Em desenvolvimento, você pode querer o dinâmico sem precisar rodar dump
    LightAutoloader::registerDynamic();
}
