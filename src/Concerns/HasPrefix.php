<?php

namespace Aslnbxrz\SimplePrefix\Concerns;

use Closure;
use Illuminate\Support\Facades\Config;

trait HasPrefix
{
    protected static string $sp_prefix = '';
    protected static array $sp_prefixFrom = [];
    protected static string $sp_separator = '-';

    protected static ?Closure $sp_prefixResolver = null;

    protected static bool $sp_runtimeConfigured = false;

    protected ?string $__sp_cached_prefix = null;

    protected function definePrefixVia(): ?string
    {
        return null;
    }

    protected static function bootHasPrefix(): void
    {
        // 1) Config defaults
        $cfg = Config::get('simple-prefix.defaults', []);
        $defPrefix = (string)($cfg['prefix'] ?? '');
        $defSeparator = (string)($cfg['separator'] ?? '-');
        $defFrom = is_array($cfg['from'] ?? null) ? $cfg['from'] : ['id'];

        if (!static::$sp_runtimeConfigured) {
            static::$sp_prefix = $defPrefix;
            static::$sp_separator = $defSeparator;
            static::$sp_prefixFrom = self::sanitizeFromArray($defFrom);
        }

        // 2) Model constants
        if (Config::get('simple-prefix.use_model_constants', true) && !static::$sp_runtimeConfigured) {
            $cls = static::class;

            if (defined($cls . '::PREFIX')) {
                static::$sp_prefix = (string)constant($cls . '::PREFIX');
            }
            if (defined($cls . '::PREFIX_SEPARATOR')) {
                $s = (string)constant($cls . '::PREFIX_SEPARATOR');
                if ($s !== '') static::$sp_separator = $s;
            }
            if (defined($cls . '::PREFIX_FROM')) {
                $f = constant($cls . '::PREFIX_FROM');
                if (is_array($f)) {
                    static::$sp_prefixFrom = self::sanitizeFromArray($f);
                }
            }
        }

        if (empty(static::$sp_prefixFrom)) {
            static::$sp_prefixFrom = ['id'];
        }
    }

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if (property_exists($model, '__sp_cached_prefix')) {
                $model->__sp_cached_prefix = null;
            }
        });
    }

    /* ===== Runtime setters ===== */
    public static function setPrefix(string $prefix): void
    {
        static::$sp_prefix = $prefix;
        static::$sp_runtimeConfigured = true;
    }

    public static function setPrefixFrom(array $attributes): void
    {
        static::$sp_prefixFrom = self::sanitizeFromArray($attributes) ?: ['id'];
        static::$sp_runtimeConfigured = true;
    }

    public static function setPrefixSeparator(string $separator): void
    {
        if ($separator !== '') {
            static::$sp_separator = $separator;
            static::$sp_runtimeConfigured = true;
        }
    }

    public static function resolvePrefixUsing(Closure $resolver): void
    {
        static::$sp_prefixResolver = $resolver;
        static::$sp_runtimeConfigured = true;
    }

    public function getPrefixAttribute(): string
    {
        if ($this->__sp_cached_prefix !== null) {
            return $this->__sp_cached_prefix;
        }

        $separator = static::$sp_separator;
        $prefix = $this->resolvePrefixForModel();

        $parts = [];
        if ($prefix !== '') {
            $parts[] = $prefix;
        }

        $from = static::$sp_prefixFrom ?: ['id'];

        foreach ($from as $attr) {
            $v = $this->getAttribute($attr);

            if ($v === null) {
                if ($attr === 'id') {
                    $parts[] = '';
                }
                continue;
            }

            if (is_string($v)) {
                $trimmed = trim($v);
                if ($trimmed === '') {
                    continue;
                }
                $parts[] = $trimmed;
                continue;
            }

            $parts[] = (string)$v;
        }

        return $this->__sp_cached_prefix = implode($separator, $parts);
    }

    protected function resolvePrefixForModel(): string
    {
        if (static::$sp_prefixResolver instanceof Closure) {
            $resolved = (static::$sp_prefixResolver)($this);
            if ($resolved !== null && $resolved !== '') {
                return (string)$resolved;
            }
        }

        $via = $this->definePrefixVia();
        if ($via !== null && $via !== '') {
            return (string)$via;
        }

        return static::$sp_prefix ?? '';
    }

    protected static function sanitizeFromArray(array $attributes): array
    {
        $clean = [];
        foreach ($attributes as $a) {
            $a = (string)$a;
            if ($a !== '') {
                $clean[] = $a;
            }
        }
        return $clean;
    }
}