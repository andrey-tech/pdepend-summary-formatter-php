<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Extractor\Parser;

final class ProjectMetrics
{
    public string $generated;

    /**
     * @var PackageMetrics[]
     */
    public array $packages = [];

    public ProjectMiMetrics $projectMiMetrics;

    public int $noc;
    public int $nom;
    public int $noi;
    public int $nof;
    public int $nop;
    public int $loc;
    public int $lloc;
    public int $ncloc;

    public function __construct(
        string $generated,
        ProjectMiMetrics $projectMiMetrics,
        int $noc,
        int $nom,
        int $noi,
        int $nof,
        int $nop,
        int $loc,
        int $lloc,
        int $ncloc
    ) {
        $this->generated = $generated;
        $this->projectMiMetrics = $projectMiMetrics;
        $this->noc = $noc;
        $this->nom = $nom;
        $this->noi = $noi;
        $this->nof = $nof;
        $this->nop = $nop;
        $this->loc = $loc;
        $this->lloc = $lloc;
        $this->ncloc = $ncloc;
    }

    public function addPackageMetrics(PackageMetrics $packageMetrics): void
    {
        $this->packages[] = $packageMetrics;
    }
}
