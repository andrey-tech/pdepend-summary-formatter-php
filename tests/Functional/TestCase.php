<?php

/**
 * @author    andrey-tech
 * @copyright 2025-2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace Test\Functional;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RuntimeException;

use function file_get_contents;
use function is_file;
use function is_readable;
use function sprintf;
use function strrchr;
use function substr;

abstract class TestCase extends PHPUnitTestCase
{
    private const DATA_DIR = 'data';

    protected function getDataFileContents(string $file): string
    {
        $file = $this->getDataFileAbsolutePath($file);

        $contents = file_get_contents($file);
        if (false === $contents) {
            throw new RuntimeException(
                sprintf('Cannot load data file "%s".', $file)
            );
        }

        return $contents;
    }

    protected function getDataFileAbsolutePath(string $fileName): string
    {
        $file = sprintf(
            '%s/%s/%s/%s',
            __DIR__,
            self::DATA_DIR,
            substr((string) strrchr(static::class, '\\'), 1),
            $fileName
        );

        if (!is_file($file)) {
            throw new RuntimeException(
                sprintf('Cannot find data file "%s".', $file)
            );
        }

        if (!is_readable($file)) {
            throw new RuntimeException(
                sprintf('Cannot read data file "%s".', $file)
            );
        }

        return $file;
    }
}
