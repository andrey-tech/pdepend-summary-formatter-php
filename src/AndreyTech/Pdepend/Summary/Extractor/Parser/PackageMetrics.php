<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Extractor\Parser;

final class PackageMetrics
{
    public string $name;

    /**
     * @var TraitMetrics[]
     */
    public array $traits = [];

    /**
     * @var ClassMetrics[]
     */
    public array $classes = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addTraitMetrics(TraitMetrics $traitMetrics): void
    {
        $this->traits[] = $traitMetrics;
    }

    public function addClassMetrics(ClassMetrics $classMetrics): void
    {
        $this->classes[] = $classMetrics;
    }
}
