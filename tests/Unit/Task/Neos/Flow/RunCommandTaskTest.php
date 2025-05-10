<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use InvalidArgumentException;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Neos\Flow\RunCommandTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class RunCommandTaskTest extends BaseTaskTest
{
    protected function createTask(): RunCommandTask
    {
        return new RunCommandTask();
    }

    /**
     * @test
     */
    public function noFlowApplicationGivenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function requiredOptionCommandNotGivenThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     * @dataProvider executeWithDifferentOptions
     */
    public function executeSuccessfully(string $expectedCommand, array $options = []): void
    {
        $this->application = new Flow();
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(sprintf($expectedCommand, $this->deployment->getReleaseIdentifier()));
    }

    public function executeWithDifferentOptions(): \Iterator
    {
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production php ./flow vendor.package:example:command \'Some longer argument needing "escaping"\'',
            [
                'command' => 'vendor.package:example:command',
                'arguments' => 'Some longer argument needing "escaping"'
            ]
        ];
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:package:activate \'--package-key\' \'Vendor.Package\'',
            ['command' => 'package:activate', 'arguments' => ['--package-key', 'Vendor.Package']]
        ];
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production php ./flow vendor.package:command \'argument1\' \'argument2\'',
            ['command' => 'vendor.package:command', 'arguments' => ['argument1', 'argument2']]
        ];
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production php ./flow vendor.package:command \'argument1\'',
            ['command' => 'vendor.package:command', 'arguments' => 'argument1']
        ];
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production php ./flow neos.flow:cache:warmup',
            ['command' => 'cache:warmup']
        ];
        yield [
            'cd /releases/%s && FLOW_CONTEXT=Production /usr/local/bin/php ./flow neos.flow:cache:warmup',
            ['command' => 'cache:warmup', 'phpBinaryPathAndFilename' => '/usr/local/bin/php']
        ];
    }
}
