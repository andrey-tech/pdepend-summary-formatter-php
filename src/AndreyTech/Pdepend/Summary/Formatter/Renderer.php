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
use AndreyTech\Pdepend\Summary\Formatter\Parser\ProjectMetrics;
use AndreyTech\Pdepend\Summary\Formatter\Parser\TraitMetrics;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;

final class Renderer
{
    private Colorizer $colorizer;

    public function __construct(Colorizer $colorizer)
    {
        $this->colorizer = $colorizer;
    }

    public function render(ProjectMetrics $projectMetrics, OutputInterface $output): void
    {
        foreach ($projectMetrics->packages as $packageMetrics) {
            foreach ($packageMetrics->traits as $traitMetrics) {
                $this->renderTraitMetrics($traitMetrics, $output);
            }

            foreach ($packageMetrics->classes as $classMetrics) {
                $this->renderClassMetrics($classMetrics, $output);
            }

        }

        $this->renderProjectMetrics($projectMetrics, $output);
    }

    private function renderClassMetrics(ClassMetrics $classMetrics, OutputInterface $output): void
    {
        $this->buildTitle($output, $classMetrics->file);

        $table = $this->buildClassTable($output);
        $this->addClassTableRow($table, $classMetrics);

        $table->render();

        $table = $this->buildMethodTable($output);

        foreach ($classMetrics->methods as $methodMetrics) {
            $this->addMethodTableRow($table, $methodMetrics);
        }

        $table->render();

        $output->writeln('');
    }

    private function renderTraitMetrics(TraitMetrics $traitMetrics, OutputInterface $output): void
    {
        $this->buildTitle($output, $traitMetrics->file);

        $table = $this->buildTraitTable($output);
        $this->addTraitTableRow($table, $traitMetrics);

        $table->render();

        $table = $this->buildMethodTable($output);

        foreach ($traitMetrics->methods as $methodMetrics) {
            $this->addMethodTableRow($table, $methodMetrics);
        }

        $table->render();

        $output->writeln('');
    }

    private function renderProjectMetrics(ProjectMetrics $projectMetrics, OutputInterface $output): void
    {
        $table = $this->buildProjectTable($output);
        $this->addProjectTableRow($table, $projectMetrics);

        $table->render();

        $output->writeln('');
    }

    private function addClassTableRow(Table $table, ClassMetrics $classMetrics): void
    {
        $table->addRow([
            $classMetrics->name,
            $this->colorizer->colorizeClassMetric('wmc', $classMetrics->wmc),
            $this->colorizer->colorizeClassMetric('cbo', $classMetrics->cbo),
            $this->colorizer->colorizeClassMetric('loc', $classMetrics->loc),
            $this->colorizer->colorizeClassMetric('cis', $classMetrics->cis),
            $this->colorizer->colorizeClassMetric('nom', $classMetrics->nom),
            $this->colorizer->colorizeClassMetric('npm', $classMetrics->npm),
            $this->colorizer->colorizeClassMetric('vars', $classMetrics->vars),
            $this->colorizer->colorizeClassMetric('dit', $classMetrics->dit),
            $this->colorizer->colorizeClassMetric('nocc', $classMetrics->nocc),
        ]);
    }

    private function addTraitTableRow(Table $table, TraitMetrics $traitMetrics): void
    {
        $table->addRow([
            $traitMetrics->name,
            $this->colorizer->colorizeClassMetric('wmc', $traitMetrics->wmc),
            $this->colorizer->colorizeClassMetric('cbo', $traitMetrics->cbo),
            $this->colorizer->colorizeClassMetric('cis', $traitMetrics->cis),
            $this->colorizer->colorizeClassMetric('npm', $traitMetrics->npm),
            $this->colorizer->colorizeClassMetric('vars', $traitMetrics->vars),
        ]);
    }

    private function addMethodTableRow(Table $table, MethodMetrics $methodMetrics): void
    {
        $table->addRow([
            $methodMetrics->name,
            $this->colorizer->colorizeMethodMetric('mi', round($methodMetrics->mi)),
            $this->colorizer->colorizeMethodMetric('ccn', $methodMetrics->ccn),
            $this->colorizer->colorizeMethodMetric('ccn2', $methodMetrics->ccn2),
            $this->colorizer->colorizeMethodMetric('crap0', $methodMetrics->crap0),
            $this->colorizer->colorizeMethodMetric('npath', $methodMetrics->npath),
            $this->colorizer->colorizeMethodMetric('loc', $methodMetrics->loc),
            $this->colorizer->colorizeMethodMetric('hb', round($methodMetrics->hb, 2)),
            $this->colorizer->colorizeMethodMetric('hd', round($methodMetrics->hd)),
            $this->colorizer->colorizeMethodMetric('hv', round($methodMetrics->hv)),
            $this->colorizer->colorizeMethodMetric('he', round($methodMetrics->he)),
            $this->colorizer->colorizeMethodMetric('', round($methodMetrics->ht)),
            $this->colorizer->colorizeMethodMetric('', round($methodMetrics->hi)),
            $this->colorizer->colorizeMethodMetric('', round($methodMetrics->hl, 1)),
            $this->colorizer->colorizeMethodMetric('', $methodMetrics->hnd),
            $this->colorizer->colorizeMethodMetric('', $methodMetrics->hnt),
        ]);
    }

    private function addProjectTableRow(Table $table, ProjectMetrics $projectMetrics): void
    {
        $projectMiMetrics = $this->buildProjectMiMetrics($projectMetrics);

        $table->addRow([
            $projectMetrics->generated,
            ...$projectMiMetrics,
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->noc),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->nom),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->noi),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->nof),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->nop),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->loc),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->lloc),
            $this->colorizer->colorizeMethodMetric('', $projectMetrics->ncloc),
        ]);
    }

    private function buildProjectMiMetrics(ProjectMetrics $projectMetrics): array
    {
        $mi = $projectMetrics->projectMiMetrics;

        if (0 === $mi->total) {
            $miMin = $miAvg = $miMax = $miStd = '-';
        } else {
            $miMin = $this->colorizer->colorizeMethodMetric('mi', round($mi->min));
            $miAvg = $this->colorizer->colorizeMethodMetric('mi', round($mi->avg));
            $miMax = $this->colorizer->colorizeMethodMetric('mi', round($mi->max));
            $miStd = $this->colorizer->colorizeMethodMetric('', round($mi->std));
        }

        return [ $miMin, $miAvg, $miMax, $miStd ];
    }

    private function buildTitle(OutputInterface $output, string $file): void
    {
        $output->writeln(
            sprintf('<fg=yellow>FILE: %s</>', $file)
        );
    }

    private function buildClassTable(OutputInterface $output): Table
    {
        $table = $this->buildTable($output);
        $table->setHeaders(['CLASS', 'wmc', 'cbo', 'loc', 'cis', 'nom', 'npm', 'vars', 'dit', 'nocc']);
        $table->setColumnWidths([25]);
        $table->setColumnMaxWidth(0, 50);

        return $table;
    }

    private function buildTraitTable(OutputInterface $output): Table
    {
        $table = $this->buildTable($output);
        $table->setHeaders(['TRAIT', 'wmc', 'cbo', 'cis', 'npm', 'vars']);
        $table->setColumnWidths([25]);
        $table->setColumnMaxWidth(0, 50);

        return $table;
    }

    private function buildMethodTable(OutputInterface $output): Table
    {
        $table = $this->buildTable($output);
        $table->setHeaders(
            [
                'METHOD',
                'mi',
                'ccn',
                'ccn2',
                'crap0',
                'npath',
                'loc',
                'hb',
                'hd',
                'hv',
                'he',
                'ht',
                'hi',
                'hl',
                'hnd',
                'hnt',
            ]
        );
        $table->setColumnWidths([25]);
        $table->setColumnMaxWidth(0, 50);

        return $table;
    }

    private function buildProjectTable(OutputInterface $output): Table
    {
        $table = $this->buildTable($output);
        $table->setHeaders(
            [
                'PROJECT',
                'min mi',
                'avg mi',
                'max mi',
                'std mi',
                'noc',
                'nom',
                'noi',
                'nof',
                'nop',
                'loc',
                'lloc',
                'ncloc',
            ]
        );

        return $table;
    }

    private function buildTable(OutputInterface $output): Table
    {
        $style = new TableStyle();
        $style->setCellHeaderFormat('<fg=white;bg=default;options=bold>%s</>');

        $table = new Table($output);
        $table->setStyle($style);

        return $table;
    }
}
