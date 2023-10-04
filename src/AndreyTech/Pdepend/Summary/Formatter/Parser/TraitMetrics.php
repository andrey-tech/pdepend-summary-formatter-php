<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter\Parser;

final class TraitMetrics
{
    public string $file;
    public string $name;

    /**
     * @var MethodMetrics[]
     */
    public array $methods = [];

    public int $cbo;
    public int $cis;
    public int $npm;
    public int $vars;
    public int $wmc;

    public function __construct(
        string $file,
        string $name,
        int $cbo,
        int $cis,
        int $npm,
        int $vars,
        int $wmc
    ) {
        $this->file = $file;
        $this->name = $name;
        $this->cbo = $cbo;
        $this->cis = $cis;
        $this->npm = $npm;
        $this->vars = $vars;
        $this->wmc = $wmc;
    }

    public function addMethodMetrics(MethodMetrics $methodMetrics): void
    {
        $this->methods[] = $methodMetrics;
    }
}
