<?php

namespace app\helpers;

class View
{
    private static array $layouts = [];
    private static array $sections = [];
    private static string $currentSection = '';
    private static array $viewCache = [];
    private static string $viewsBasePath = '';

    /**
     * Define o caminho base das views (opcional)
     * Se não for definido, tenta detectar automaticamente
     */
    public static function setViewsPath(string $path): void
    {
        self::$viewsBasePath = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }

    public static function layout(string $layoutName, array $data = [])
    {
        self::$layouts[0] = ['name' => $layoutName, 'data' => $data];
    }

    public static function start(string $sectionName)
    {
        self::$currentSection = $sectionName;
        ob_start();
    }

    public static function stop()
    {
        self::$sections[self::$currentSection] = ob_get_clean();
    }

    public static function section(string $sectionName, string $default = '')
    {
        echo self::$sections[$sectionName] ?? $default;
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function render(string $view, array $data = []): void
    {
        // Limpa buffers anteriores de forma segura
        while (ob_get_level() > 0) ob_end_clean();

        // Reseta estados
        self::$layouts = [];
        self::$sections = [];
        self::$currentSection = '';

        // Extrai dados para as views
        extract($data);

        // Inicia buffer para capturar a saída da view
        ob_start();
        $viewFile = self::resolvePath($view);
        require $viewFile;
        $viewContent = ob_get_clean();

        // Verifica se a view definiu um layout
        if (!empty(self::$layouts)) {
            // Se a view NÃO usou start/stop (nenhuma seção foi criada),
            // todo o conteúdo da view é considerado a seção 'content'
            if (empty(self::$sections)) {
                self::$sections['content'] = $viewContent;
            }

            $layout = self::$layouts[0];
            $layoutFile = self::resolvePath($layout['name']);
            extract($layout['data']);

            // Fornece uma função de atalho para escapar dentro do layout
            $e = function ($str) {
                return self::e($str);
            };

            // Inclui o layout (que chamará View::section('content') etc.)
            require $layoutFile;
        } else {
            // Sem layout, apenas imprime o conteúdo da view
            echo $viewContent;
        }
    }

    private static function resolvePath(string $name): string
    {
        // Cache
        if (isset(self::$viewCache[$name])) {
            return self::$viewCache[$name];
        }

        // Normaliza o nome (ex: 'home/index' ou 'master')
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $name) . '.php';

        // Caminhos possíveis
        $candidates = [];

        // 1. Caminho base definido manualmente
        if (!empty(self::$viewsBasePath)) {
            $candidates[] = self::$viewsBasePath . $relativePath;
        }

        // 2. Padrão: raiz do projeto + /views/
        $rootViews = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $candidates[] = $rootViews . $relativePath;

        // 3. Alternativa: app/views/ (caso o usuário tenha movido)
        $appViews = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        if ($appViews !== $rootViews) {
            $candidates[] = $appViews . $relativePath;
        }

        // Tenta cada caminho
        foreach ($candidates as $path) {
            if (file_exists($path)) {
                self::$viewCache[$name] = $path;
                return $path;
            }
        }

        // Se não encontrou, mostra diagnóstico
        throw new \Exception("View ou layout não encontrado: '{$name}'. Procurou em: " . implode(', ', $candidates));
    }
}
