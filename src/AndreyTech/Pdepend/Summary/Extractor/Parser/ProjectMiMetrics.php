<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Extractor\Parser;

final class ProjectMiMetrics
{
    public float $max = 0.0;
    public float $min = 0.0;
    public float $avg = 0.0;
    public float $std = 0.0;
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
        if (empty($this->miList)) {
            return;
        }

        $this->min = min($this->miList);
        $this->max = max($this->miList);

        $this->total = count($this->miList);

        $this->avg = array_sum($this->miList) / $this->total;

        $variance = array_reduce(
            $this->miList,
            fn (float $std, float $mi): float => $std + ($mi - $this->avg) ** 2,
            0.0
        );

        $this->std = sqrt($variance / $this->total);

        $this->miList = [];
    }
}
