<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter\Parser;

final class MethodMetrics
{
    public string $name;

    public int $ccn;
    public int $ccn2;
    public int $npath;
    public float $mi;
    public int $loc;
    public float $hb;
    public float $hd;
    public float $hv;
    public float $he;
    public float $ht;
    public float $hi;
    public float $hl;
    public int $hnd;
    public int $hnt;
    public int $crap0;

    public function __construct(
        string $name,
        int $ccn,
        int $ccn2,
        int $npath,
        float $mi,
        int $loc,
        float $hb,
        float $hd,
        float $hv,
        float $he,
        float $ht,
        float $hi,
        float $hl,
        int $hnd,
        int $hnt,
        int $crap0
    ) {
        $this->name = $name;
        $this->ccn = $ccn;
        $this->ccn2 = $ccn2;
        $this->npath = $npath;
        $this->mi = $mi;
        $this->loc = $loc;
        $this->hb = $hb;
        $this->hd = $hd;
        $this->hv = $hv;
        $this->he = $he;
        $this->ht = $ht;
        $this->hi = $hi;
        $this->hl = $hl;
        $this->hnd = $hnd;
        $this->hnt = $hnt;
        $this->crap0 = $crap0;
    }
}
