<?php

/**
 * @author    andrey-tech
 * @copyright 2023-2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Extractor\Parser;

use function array_reduce;
use function array_sum;
use function count;
use function floor;
use function max;
use function min;
use function sort;
use function sqrt;

final class ProjectMiMetrics
{
    public float $max = 0.0;
    public float $min = 0.0;
    public float $avg = 0.0;
    public float $std = 0.0;
    public float $median = 0.0;
    public int $total = 0;

    /**
     * @var float[]
     */
    private array $miList = [];

    public function addMi(float $mi): void
    {
        $this->miList[] = $mi;
    }

    public function calculate(): void
    {
        $this->total = count($this->miList);

        if (0 === $this->total) {
            return;
        }

        $this->calculateAverage();
        $this->calculateMedian();

        $this->miList = [];
    }

    public function calculateAverage(): void
    {
        $this->min = min($this->miList);
        $this->max = max($this->miList);

        $this->avg = array_sum($this->miList) / $this->total;

        $variance = array_reduce(
            $this->miList,
            fn (float $std, float $mi): float => $std + ($mi - $this->avg) ** 2,
            0.0
        );

        $this->std = sqrt($variance / $this->total);
    }

    private function calculateMedian(): void
    {
        sort($this->miList, SORT_NUMERIC);

        $middle = floor($this->total / 2);

        $this->median = ($this->total % 2) ?
            $this->miList[$middle] :
            ($this->miList[$middle - 1] + $this->miList[$middle]) / 2;
    }
}
