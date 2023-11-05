<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter;

use AndreyTech\Pdepend\Summary\Extractor\Parser;
use AndreyTech\Pdepend\Summary\Extractor\Parser\ProjectMetrics;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Throwable;

use function fclose;
use function fopen;
use function sprintf;

final class Application
{
    private const EXIT_CODE_OK = 0;
    private const EXIT_CODE_ERROR = 1;
    private const EXIT_CODE_RED_METRICS = 2;
    private const EXIT_CODE_YELLOW_METRICS = 3;

    private ConsoleOutput $consoleOutput;
    private Config $config;

    /**
     * @var resource|false
     */
    private $handle = false;

    public function __construct()
    {
        $this->consoleOutput = new ConsoleOutput();
        $this->config = new Config($this->consoleOutput);
    }

    public function run(): int
    {
        $startTime = microtime(true);

        try {
            $exitCode = $this->doRun();
        } catch (Throwable $exception) {
            $exitCode = self::EXIT_CODE_ERROR;
            $this->error(
                sprintf('ERROR: %s', $exception->getMessage())
            );
        }

        $this->showStats($startTime, $exitCode);

        return $exitCode;
    }

    /**
     * @throws Exception
     */
    public function doRun(): int
    {
        $this->message('Starting pdepend-summary-formatter...');

        $this->config->buildArgvInput();
        $this->config->handleOptionNoColor();

        if ($this->config->handleOptionInit()) {
            return 0;
        }

        $configFile = $this->config->handleOptionConfigFile();
        $this->config->parseConfigFile($configFile);

        $outputFile = $this->config->handleOptionOutputFile();

        $summaryFile = $this->config->handleArgumentSummaryFile();
        $metrics = $this->parseSummaryFile($summaryFile);

        $exitCode = $this->renderToConsole($metrics);
        $this->renderToOutputFile($metrics, $outputFile);

        return $exitCode;
    }

    /**
     * @throws Exception
     */
    private function parseSummaryFile(string $summaryFile): ProjectMetrics
    {
        $this->message(
            sprintf('Parsing summary file "%s"...', $summaryFile)
        );

        return (new Parser())->parse($summaryFile);
    }

    private function renderToConsole(ProjectMetrics $metrics): int
    {
        $colorizer = new Colorizer($this->config->getConfiguration());
        $renderer = new Renderer($colorizer);

        $this->message('Rendering results to console...' . PHP_EOL);

        $renderer->render($metrics, $this->consoleOutput);

        return $this->calculateExitCode($colorizer);
    }

    private function renderToOutputFile(ProjectMetrics $metrics, ?string $outputFile): void
    {
        if (null === $outputFile) {
            return;
        }

        $this->message(
            sprintf('Rendering results to output file "%s"...', $outputFile)
        );

        $colorizer = new Colorizer();
        $renderer = new Renderer($colorizer);

        $streamOutput = $this->buildStreamOutput($outputFile);
        $renderer->render($metrics, $streamOutput);
    }

    private function showStats(float $startTime, int $exitCode): void
    {
        $deltaTime = (int) round(1000 * (microtime(true) - $startTime));
        $memoryUsage = (int) round(memory_get_peak_usage(true) / 1024 / 1024);

        $this->message(
            sprintf('Exit code: %u, Time: %u ms, Memory: %u MiB', $exitCode, $deltaTime, $memoryUsage)
        );
    }

    private function calculateExitCode(Colorizer $colorizer): int
    {
        $stats = $colorizer->getFgTagStats();

        $statsRed = $stats['red'] ?? 0;

        if ($statsRed > 0) {
            return self::EXIT_CODE_RED_METRICS;
        }

        $statsYellow = $stats['yellow'] ?? 0;

        if ($statsYellow > 0) {
            return self::EXIT_CODE_YELLOW_METRICS;
        }

        return self::EXIT_CODE_OK;
    }

    private function buildStreamOutput(string $file): StreamOutput
    {
        $this->handle = fopen($file, 'w+b');

        if (false === $this->handle) {
            throw new RuntimeException(
                sprintf('Can not open output file "%s" to write.', $file)
            );
        }

        return new StreamOutput($this->handle);
    }

    private function message(string $message): void
    {
         $this->consoleOutput->writeln($message);
    }

    private function error(string $message): void
    {
         $this->consoleOutput->writeln(
             sprintf('<fg=red;options=bold>%s</>', $message)
         );
    }

    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
        }
    }
}
