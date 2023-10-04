<?php

/**
 * @author    andrey-tech
 * @copyright 2023 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\Pdepend\Summary\Formatter;

use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

use function copy;
use function getcwd;
use function is_file;
use function is_writable;
use function sprintf;

final class Config
{
    private const CONFIG_FILE = 'pdepend-summary-formatter.yaml';
    private const CONFIG_FILE_DIST = 'pdepend-summary-formatter.yaml.dist';
    private const CONFIG_DIR = __DIR__ . '/../../../../../config';

    private ConsoleOutput $consoleOutput;
    private ArgvInput $argvInput;
    private array $configuration = [];

    public function __construct(ConsoleOutput $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
        $this->argvInput = new ArgvInput();
    }

    public function buildArgvInput(): void
    {
        $definition = new InputDefinition();

        $argument = new InputArgument('summary-file', InputArgument::OPTIONAL);
        $definition->addArgument($argument);

        $option = new InputOption('output-file', null, InputOption::VALUE_REQUIRED);
        $definition->addOption($option);

        $option = new InputOption('config-file', null, InputOption::VALUE_REQUIRED);
        $definition->addOption($option);

        $option = new InputOption('no-color', null, InputOption::VALUE_NONE);
        $definition->addOption($option);

        $option = new InputOption('init', null, InputOption::VALUE_NONE);
        $definition->addOption($option);

        $this->argvInput = new ArgvInput(null, $definition);
    }

    public function handleOptionNoColor(): void
    {
        $noColor = (bool) $this->argvInput->getOption('no-color');
        $this->consoleOutput->getFormatter()->setDecorated(!$noColor);
    }

    public function handleOptionInit(): bool
    {
        $isInit = (bool) $this->argvInput->getOption('init');

        if ($isInit) {
            $this->createDefaultConfigFile();

            return true;
        }

        return false;
    }

    public function handleArgumentSummaryFile(): string
    {
        $summaryFile = (string) $this->argvInput->getArgument('summary-file');

        if (empty($summaryFile)) {
            throw new RuntimeException('Missing required argument <path to pdepend file summary.xml>".');
        }

        if (!is_file($summaryFile)) {
            throw new RuntimeException(
                sprintf('Can not find summary file "%s".', $summaryFile)
            );
        }

        if (!is_readable($summaryFile)) {
            throw new RuntimeException(
                sprintf('Can not read summary file "%s".', $summaryFile)
            );
        }

        return $summaryFile;
    }

    public function handleOptionConfigFile(): string
    {
        /** @var string|null $configFile */
        $configFile = $this->argvInput->getOption('config-file');

        if (null === $configFile) {
            return $this->searchConfigFile();
        }

        if (empty($configFile)) {
            throw new RuntimeException('The "--config-file" option requires a value.');
        }

        return $configFile;
    }

    public function handleOptionOutputFile(): ?string
    {
        /** @var string|null $outputFile */
        $outputFile = $this->argvInput->getOption('output-file');

        if (null === $outputFile) {
            return null;
        }

        if (empty($outputFile)) {
            throw new RuntimeException('The "--output-file" option requires a value.');
        }

        return $outputFile;
    }

    public function parseConfigFile(string $configFile): void
    {
        $this->configuration = (array) Yaml::parseFile($configFile);
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    private function createDefaultConfigFile(): void
    {
        $cwd = getcwd();

        $this->message(
            sprintf('Creating default config file "%s" in current working directory...', self::CONFIG_FILE_DIST)
        );

        if (!is_writable($cwd)) {
            throw new RuntimeException(
                sprintf('Can not write to current working directory "%s".', $cwd)
            );
        }

        $defaultConfigFile = $this->buildFullFileName(self::CONFIG_DIR, self::CONFIG_FILE_DIST);
        $targetConfigFile = $this->buildFullFileName($cwd, self::CONFIG_FILE_DIST);

        if (!@copy($defaultConfigFile, $targetConfigFile)) {
            throw new RuntimeException(
                sprintf('Can not create default config file "%s".', $targetConfigFile)
            );
        }

        $this->message('Default config file created successfully.');
    }

    private function searchConfigFile(): string
    {
        $cwd = getcwd();

        foreach ([ self::CONFIG_FILE, self::CONFIG_FILE_DIST ] as $configFileName) {
            $configFile = $this->buildFullFileName($cwd, $configFileName);

            if (!is_file($configFile)) {
                continue;
            }

            if (!is_readable($configFile)) {
                throw new RuntimeException(
                    sprintf('Can not read config file "%s".', $configFile)
                );
            }

            return $configFile;
        }

        throw new RuntimeException(
            'Can not find config file in current working directory. ' .
            'Please, use option --init or --config-file=<path to config file>.'
        );
    }

    private function message(string $message): void
    {
         $this->consoleOutput->writeln($message);
    }

    private function buildFullFileName(string $directory, string $fileName): string
    {
        return sprintf('%s/%s', $directory, $fileName);
    }
}
