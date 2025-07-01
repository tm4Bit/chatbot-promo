<?php

declare(strict_types=1);

function base_path(string $path): string
{
    return BASE_PATH.$path;
}

function dd($value)
{
    $styles = 'background-color: black; color: white; font-size: 14px; font-weight: bold; padding: 8px;';
    echo '<pre style="'.$styles.'">';
    var_dump($value);
    echo '</pre>';
    exit();
}

function config(string $configFile, string|array $key): mixed
{
    $configPath = BASE_PATH.'config/'.$configFile.'.php';

    if (! file_exists($configPath)) {
        throw new Exception("Configuration file {$configFile} not found.");
    }

    $config = require $configPath;

    if (is_string($key)) {
        if (! array_key_exists($key, $config)) {
            throw new Exception("Key '{$key}' not found in config file '{$configFile}'.");
        }

        return $config[$key];
    }

    if (is_array($key)) {
        $value = $config;
        foreach ($key as $k) {
            if (! is_array($value) || ! array_key_exists($k, $value)) {
                throw new Exception("Key path '".implode(' -> ', $key)."' not found in config file '{$configFile}'.");
            }
            $value = $value[$k];
        }

        return $value;
    }

    throw new Exception('Invalid key type provided.');
}

function render(string $template, array $attributes = []): string
{
    extract($attributes); // Extrai as variáveis do array para o escopo local
    ob_start();
    require $template;
    $html = ob_get_clean();

    return $html;
}

function renderPartial(string $template, array $attributes = [])
{
    extract($attributes);

    return require base_path('templates/partials/'.$template.'.php');
}

function get_translation(string $key, array $translations)
{
    return $translations[$key];
}

function normalize_string(string $value): string
{
    if (function_exists('iconv')) {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    } else {
        $value = Normalizer::normalize($value, Normalizer::FORM_D);
        $value = preg_replace('/[\pM]/u', '', $value);
    }
    $value = preg_replace('/[^\p{L}\p{N}\s_]/u', '', $value);
    $value = strtolower($value);
    $value = preg_replace('/\s+/', '_', $value);
    $value = preg_replace('/_+/', '_', $value);
    $value = trim($value, '_');

    return $value;
}
