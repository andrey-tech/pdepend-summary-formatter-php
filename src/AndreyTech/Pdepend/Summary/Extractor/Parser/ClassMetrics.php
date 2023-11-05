<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Extractor\Parser;

final class ClassMetrics
{
    public string $file;
    public string $name;

    /**
     * @var MethodMetrics[]
     */
    public array $methods = [];

    public int $cbo;
    public int $loc;
    public int $cis;
    public int $nom;
    public int $npm;
    public int $vars;
    public int $wmc;
    public int $dit;
    public int $nocc;

    public function __construct(
        string $file,
        string $name,
        int $cbo,
        int $loc,
        int $cis,
        int $nom,
        int $npm,
        int $vars,
        int $wmc,
        int $dit,
        int $nocc
    ) {
        $this->file = $file;
        $this->name = $name;
        $this->cbo = $cbo;
        $this->loc = $loc;
        $this->cis = $cis;
        $this->nom = $nom;
        $this->npm = $npm;
        $this->vars = $vars;
        $this->wmc = $wmc;
        $this->dit = $dit;
        $this->nocc = $nocc;
    }

    public function addMethodMetrics(MethodMetrics $methodMetrics): void
    {
        $this->methods[] = $methodMetrics;
    }
}
