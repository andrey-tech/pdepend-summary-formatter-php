<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter;

use AndreyTech\Pdepend\Summary\Formatter\Parser\ClassMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\MethodMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\PackageMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\ProjectMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\ProjectMiMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\TraitMetrics;
use Exception;
use SimpleXMLElement;

final class Parser
{
    /**
     * @throws Exception
     */
    public function parse(string $file): ProjectMetrics
    {
        $xml = new SimpleXMLElement($file, 0, true);

        $projectMetrics = $this->parseProject($xml);
        $this->parsePackages($xml, $projectMetrics);

        $this->calculateProjectMi($projectMetrics);

        return $projectMetrics;
    }

    private function parseProject(SimpleXMLElement $xml): ProjectMetrics
    {
        return new ProjectMetrics(
            $this->getStringAttr($xml, 'generated'),
            new ProjectMiMetrics(),
            $this->getIntAttr($xml, 'noc'),
            $this->getIntAttr($xml, 'nom'),
            $this->getIntAttr($xml, 'noi'),
            $this->getIntAttr($xml, 'nof'),
            $this->getIntAttr($xml, 'nop'),
            $this->getIntAttr($xml, 'loc'),
            $this->getIntAttr($xml, 'lloc'),
            $this->getIntAttr($xml, 'ncloc')
        );
    }

    private function parsePackages(SimpleXMLElement $xml, ProjectMetrics $projectMetrics): void
    {
        $packages = $xml->package;
        if (null === $packages) {
            return;
        }

        foreach ($packages as $package) {
            $packageMetrics = $this->buildPackageMetrics($package);
            $projectMetrics->addPackageMetrics($packageMetrics);

            $this->parseClasses($package, $packageMetrics);
            $this->parseTraits($package, $packageMetrics);
        }
    }

    private function parseClasses(SimpleXMLElement $package, PackageMetrics $packageMetrics): void
    {
        $classes = $package->class;
        if (null === $classes) {
            return;
        }

        foreach ($classes as $class) {
            $classMetrics = $this->buildClassMetrics($class);
            $packageMetrics->addClassMetrics($classMetrics);

            $methods = $class->method;
            if (null === $methods) {
                continue;
            }

            foreach ($methods as $method) {
                $classMetrics->addMethodMetrics($this->buildMethodMetrics($method));
            }
        }
    }

    private function parseTraits(SimpleXMLElement $package, PackageMetrics $packageMetrics): void
    {
        $traits = $package->trait;
        if (null === $traits) {
            return;
        }

        foreach ($traits as $trait) {
            $traitMetrics = $this->buildTraitMetrics($trait);
            $packageMetrics->addTraitMetrics($traitMetrics);

            $methods = $trait->method;
            if (null === $methods) {
                continue;
            }

            foreach ($methods as $method) {
                $traitMetrics->addMethodMetrics($this->buildMethodMetrics($method));
            }
        }
    }

    private function buildPackageMetrics(SimpleXMLElement $package): PackageMetrics
    {
        return new PackageMetrics(
            $this->getStringAttr($package, 'name')
        );
    }

    private function buildClassMetrics(SimpleXMLElement $class): ClassMetrics
    {
        /** @var SimpleXMLElement $fileName */
        $fileName = $class->file;

        return new ClassMetrics(
            $this->getStringAttr($fileName, 'name'),
            $this->getStringAttr($class, 'name'),
            $this->getIntAttr($class, 'cbo'),
            $this->getIntAttr($class, 'loc'),
            $this->getIntAttr($class, 'cis'),
            $this->getIntAttr($class, 'nom'),
            $this->getIntAttr($class, 'npm'),
            $this->getIntAttr($class, 'vars'),
            $this->getIntAttr($class, 'wmc'),
            $this->getIntAttr($class, 'dit'),
            $this->getIntAttr($class, 'nocc')
        );
    }

    private function buildTraitMetrics(SimpleXMLElement $trait): TraitMetrics
    {
        /** @var SimpleXMLElement $fileName */
        $fileName = $trait->file;

        return new TraitMetrics(
            $this->getStringAttr($fileName, 'name'),
            $this->getStringAttr($trait, 'name'),
            $this->getIntAttr($trait, 'cbo'),
            $this->getIntAttr($trait, 'cis'),
            $this->getIntAttr($trait, 'npm'),
            $this->getIntAttr($trait, 'vars'),
            $this->getIntAttr($trait, 'wmc')
        );
    }

    private function buildMethodMetrics(SimpleXMLElement $method): MethodMetrics
    {
        $ccn2 = $this->getIntAttr($method, 'ccn2');

        return new MethodMetrics(
            $this->getStringAttr($method, 'name'),
            $this->getIntAttr($method, 'ccn'),
            $ccn2,
            $this->getIntAttr($method, 'npath'),
            $this->getFloatAttr($method, 'mi'),
            $this->getIntAttr($method, 'loc'),
            $this->getFloatAttr($method, 'hb'),
            $this->getFloatAttr($method, 'hd'),
            $this->getFloatAttr($method, 'hv'),
            $this->getFloatAttr($method, 'he'),
            $this->getFloatAttr($method, 'ht'),
            $this->getFloatAttr($method, 'hi'),
            $this->getFloatAttr($method, 'hl'),
            $this->getIntAttr($method, 'hnd'),
            $this->getIntAttr($method, 'hnt'),
            $this->calculateCRAP($ccn2)
        );
    }

    private function calculateProjectMi(ProjectMetrics $projectMetrics): void
    {
        foreach ($projectMetrics->packages as $package) {
            foreach ($package->traits as $trait) {
                foreach ($trait->methods as $method) {
                    $projectMetrics->projectMiMetrics->addMi($method->mi);
                }
            }

            foreach ($package->classes as $class) {
                foreach ($class->methods as $method) {
                    $projectMetrics->projectMiMetrics->addMi($method->mi);
                }
            }
        }

        $projectMetrics->projectMiMetrics->calculate();
    }

    /**
     * @see https://www.artima.com/weblogs/viewpost.jsp?thread=210575
     */
    private function calculateCRAP(int $complexity, int $coverage = 0): int
    {
        $crap = ($complexity ** 2) * ((1 - $coverage / 100) ** 3) + $complexity;

        return (int) $crap;
    }

    private function getStringAttr(SimpleXMLElement $element, string $attribute): string
    {
        return (string) $element[$attribute];
    }

    private function getIntAttr(SimpleXMLElement $element, string $attribute): int
    {
        return (int) $element[$attribute];
    }

    private function getFloatAttr(SimpleXMLElement $element, string $attribute): float
    {
        return (float) $element[$attribute];
    }
}
