<?php
declare(strict_types=1);

namespace Framework\View;


class MVCTemplateViewer implements TemplateViewerInterface
{

    public function render(string $template, array $data=[]): string
    {
        $views_dir = dirname(__DIR__, 2). "/views/";

        $code = file_get_contents($views_dir . $template);
        if ($code === false) {
            throw new \RuntimeException("Template introuvable : " . $template);
        }

        $code = $this->replaceBlocks($code, $views_dir);
        $code = $this->loadIncludes($code, $views_dir);
        $code = $this->replaceVariables($code);
        $code = $this->replacePHP($code);

        extract($data, EXTR_SKIP);
        ob_start();
            eval("?>$code");
        return ob_get_clean();

    }

    
    private function replaceVariables(string $code): string
    {
        return preg_replace_callback(
            '#{{\s*(?<path>[A-Za-z_]\w*(?:\.[A-Za-z_]\w*)*)\s*}}#',
            function (array $match): string {
                $parts = explode('.', $match['path']);
                $root = '$' . array_shift($parts);
                $path = var_export($parts, true);
                return "<?= htmlspecialchars((string) (\$this->resolveVariable($root, $path) ?? ''), ENT_QUOTES, 'UTF-8') ?>";
            },
            $code
        );
    }

    private function resolveVariable(mixed $value, array $path): mixed
    {
        foreach ($path as $property) {
            if (is_array($value)) {
                $value = $value[$property] ?? null;
                continue;
            }

            if (!is_object($value)) {
                return $value;
            }

            if (property_exists($value, $property) && isset($value->{$property})) {
                $value = $value->{$property};
                continue;
            }

            $getter = 'get' . ucfirst($property);
            if (method_exists($value, $getter)) {
                $value = $value->{$getter}();
                continue;
            }

            if (method_exists($value, $property)) {
                $value = $value->{$property}();
                continue;
            }

            return null;
        }

        return $value;
    }

    private function replacePHP(string $code): string
    {
        return preg_replace_callback('#{%\s*(.*?)\s*%}#s', function (array $match): string {
            $tag = trim($match[1]);
            if (preg_match('/^if\s+(.+)$/s', $tag, $m)) {
                return '<?php if (' . $this->parseExpression($m[1]) . '): ?>';
            }
            if (preg_match('/^elseif\s+(.+)$/s', $tag, $m)) {
                return '<?php elseif (' . $this->parseExpression($m[1]) . '): ?>';
            }
            if ($tag === 'else') return '<?php else: ?>';
            if ($tag === 'endif') return '<?php endif ?>';
            if (preg_match('/^for\s+([A-Za-z_]\w*)\s+in\s+(.+)$/s', $tag, $m)) {
                return '<?php foreach ((array) (' . $this->parseExpression($m[2]) . ') as $' . $m[1] . '): ?>';
            }
            if ($tag === 'endfor') return '<?php endforeach ?>';
            if (preg_match('/^set\s+([A-Za-z_]\w*)\s*=\s*(.+)$/s', $tag, $m)) {
                return '<?php $' . $m[1] . ' = ' . $this->parseExpression($m[2]) . '; ?>';
            }
            return $match[0];
        }, $code);
    }

    private function parseExpression(string $expression): string
    {
        $expression = trim($expression);
        if (preg_match('/^([A-Za-z_]\w*(?:\.[A-Za-z_]\w*)*)$/', $expression, $m)) {
            $parts = explode('.', $m[1]);
            $root = '$' . array_shift($parts);
            return '$this->resolveVariable(' . $root . ', ' . var_export($parts, true) . ')';
        }
        return $expression;
    }

    private function replaceBlocks(string $code, string $dir): string
    {
         
        $extend_pattern = '#^{% extends "(?<template>.*)" %}#';

        if (preg_match($extend_pattern, $code, $matches) === 1){
            $base = file_get_contents($dir.$matches["template"]);
            $blocks = $this->getBlocks($code);
            $code = $this->replaceYields($base, $blocks);
        }

        return $code;
    }

    private function replaceYields(string $code, array $blocks): string
    {
        $pattern = "#{% yield (?<name>\w+) %}#";
        preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match){
            $name = $match['name'];
            $block = $blocks[$name];
            $code = preg_replace("#{% yield $name %}#", $block, $code);
        }

        return $code;
    }

    private function getBlocks(string $code): array
    {
        $pattern = "#{% block (?<name>\w+) %}(?<content>.*?){% endblock %}#s";
        preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

        $blocks = [];

        foreach ($matches as $match) {
            $blocks[$match['name']] = $match['content'];
        }

        return $blocks;
    }

    private function loadIncludes(string $code, string $dir): string
    {
        $pattern = '#{% include "(?<template>.*?)" %}#';
        preg_match_all($pattern, $code, $matches, PREG_SET_ORDER);

        foreach ($matches as $match){
            $template = $match["template"];
            $contents = file_get_contents($dir.$template);
            $code = preg_replace("#{% include \"$template\" %}#",
                                 $contents, $code);
        }

        return $code;
    }

}