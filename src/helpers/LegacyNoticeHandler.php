<?php

namespace crocodicstudio\crudbooster\helpers;

/**
 * Restores PHP 7 behaviour for warnings that were notices before PHP 8, so they
 * are not escalated to exceptions by Laravel inside CRUDBooster-managed code.
 * Any other error is passed on to the previously registered handler.
 */
class LegacyNoticeHandler
{
    const FORMER_NOTICES = [
        'Undefined array key',
        'Undefined variable',
        'Undefined property',
        'Undefined offset',
        'Undefined index',
        'Trying to access array offset on',
        'Attempt to read property',
        'Uninitialized string offset',
        'compact(): Undefined variable',
    ];

    private $paths;

    private $previous;

    public function __construct(array $paths, ?callable $previous)
    {
        $this->paths = array_values(array_filter(array_map('realpath', $paths)));
        $this->previous = $previous;
    }

    public static function register(array $paths)
    {
        $handler = new self($paths, null);
        $handler->previous = set_error_handler($handler);

        return $handler;
    }

    public function __invoke($level, $message, $file = '', $line = 0)
    {
        if ($level === E_WARNING && $this->isFormerNotice($message) && $this->isCovered($file)) {
            return true;
        }

        if ($this->previous) {
            return call_user_func($this->previous, $level, $message, $file, $line);
        }

        return false;
    }

    private function isFormerNotice($message)
    {
        foreach (self::FORMER_NOTICES as $prefix) {
            if (strncmp($message, $prefix, strlen($prefix)) === 0) {
                return true;
            }
        }

        return false;
    }

    private function isCovered($file)
    {
        $file = realpath($file) ?: $file;
        foreach ($this->paths as $path) {
            if (strncmp($file, $path.DIRECTORY_SEPARATOR, strlen($path) + 1) === 0) {
                return true;
            }
        }

        return false;
    }
}
