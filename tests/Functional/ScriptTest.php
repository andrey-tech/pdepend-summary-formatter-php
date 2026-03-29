<?php

/**
 * @author    andrey-tech
 * @copyright 2025-2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace Test\Functional;

use Symfony\Component\Process\Process;

use function realpath;
use function sprintf;

final class ScriptTest extends TestCase
{
    private const SCRIPT_FILE = __DIR__ . '/../../bin/pdepend-summary-formatter';
    private const CONFIG_FILE = __DIR__ . '/../../config/pdepend-summary-formatter.yml.dist';

    public function testSuccessRedNoColors(): void
    {
        $process = $this->runProcess([
            '--no-color',
            sprintf('--config-file=%s', (string) realpath(self::CONFIG_FILE)),
            $this->getDataFileAbsolutePath('summary-red.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('success-red.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(2, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testSuccessYellowNoColors(): void
    {
        $process = $this->runProcess([
            '--no-color',
            sprintf('--config-file=%s', (string) realpath(self::CONFIG_FILE)),
            $this->getDataFileAbsolutePath('summary-yellow.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('success-yellow.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(3, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    public function testSuccessGreenNoColors(): void
    {
        $process = $this->runProcess([
            '--no-color',
            sprintf('--config-file=%s', (string) realpath(self::CONFIG_FILE)),
            $this->getDataFileAbsolutePath('summary-green.xml')
        ]);

        self::assertStringContainsString(
            $this->getDataFileContents('success-green.txt'),
            $process->getOutput()
        );

        self::assertStringContainsString('Exit code:', $process->getOutput());
        self::assertStringContainsString('Time:', $process->getOutput());
        self::assertStringContainsString('Memory:', $process->getOutput());

        self::assertSame(0, $process->getExitCode());
        self::assertEmpty($process->getErrorOutput());
    }

    /**
     * @param list<string> $options
     */
    private function runProcess(array $options = []): Process
    {
        $process = new Process([
            'php',
            self::SCRIPT_FILE,
            ...$options
        ]);

        $process->setTimeout(10);
        $process->run();

        return $process;
    }
}
