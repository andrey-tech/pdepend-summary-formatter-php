<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter;

use function explode;
use function implode;
use function sprintf;

use const PHP_INT_MAX;

final class Colorizer
{
    /**
     * @var array<string, array<string, list<int|float|null>>>
     */
    private array $classMetrics;

    /**
     * @var array<string, array<string, list<int|float|null>>>
     */
    private array $methodMetrics;

    /**
     * @var array<string, int>
     */
    private array $fgTagStats = [];

    public function __construct(array $config = [])
    {
        $this->parseConfig($config);
    }

    /**
     * @return array<string, int>
     */
    public function getFgTagStats(): array
    {
        return $this->fgTagStats;
    }

    /**
     * @param int|float $value
     */
    public function colorizeClassMetric(string $name, $value): string
    {
        return $this->colorize($name, $value, $this->classMetrics);
    }

    /**
     * @param int|float $value
     */
    public function colorizeMethodMetric(string $name, $value): string
    {
        return $this->colorize($name, $value, $this->methodMetrics);
    }

    /**
     * @param int|float $value
     * @param array<string, array<string, list<int|float|null>>> $metrics
     */
    private function colorize(string $name, $value, array $metrics): string
    {
        $ranges = $metrics[$name] ?? null;

        if (null === $ranges) {
            return (string) $value;
        }

        foreach ($ranges as $tagValues => $range) {
            $min = $range[0] ?? - PHP_INT_MAX;
            $max = $range[1] ?? PHP_INT_MAX;

            if ($value >= $min && $value <= $max) {
                return $this->renderTemplate($value, $tagValues);
            }
        }

        return (string) $value;
    }

    /**
     * @param int|float $value
     */
    private function renderTemplate($value, string $tagValues): string
    {
        $values = explode('+', $tagValues);

        $tags = [];

        if (isset($values[0])) {
            $tags[] = sprintf('fg=%s', $values[0]);
            $this->updateFgTagStats($values[0]);
        }

        if (isset($values[1])) {
            $tags[] = sprintf('options=%s', $values[1]);
        }

        return sprintf('<%s>%s</>', implode(';', $tags), $value);
    }

    private function updateFgTagStats(string $tag): void
    {
        if (!isset($this->fgTagStats[$tag])) {
            $this->fgTagStats[$tag] = 0;
        }

        $this->fgTagStats[$tag]++;
    }

    private function parseConfig(array $config): void
    {
        /**
         * @var array{
         *     colorizer: array{
         *         metrics: array{
         *             class: array<string, array<string, list<int|float|null>>>,
         *             method: array<string, array<string, list<int|float|null>>>
         *         }
         *     }
         * } $config
         */

        $this->classMetrics = $config['colorizer']['metrics']['class'] ?? [];
        $this->methodMetrics = $config['colorizer']['metrics']['method'] ?? [];
    }
}
