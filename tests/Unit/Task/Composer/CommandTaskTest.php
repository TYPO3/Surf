<?php
namespace TYPO3\Surf\Tests\Unit\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Composer\CommandTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the TagTask
 */
class CommandTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeUserConfiguredComposerCommand(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script'
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\' 2>&1$/'
        );
    }

    /**
     * @test
     */
    public function executeWithoutCommandThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'additionalArguments' => 'my-script'
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function executeWithSupportedSuffixAsArray(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => ['2>&1']
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\' 2>&1$/'
        );
    }

    /**
     * @test
     */
    public function executeWithSupportedSuffixAsString(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => '2>&1'
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\' 2>&1$/'
        );
    }

    /**
     * @test
     */
    public function executeWithSupportedEmptySuffixAsArray(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => [],
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\'$/'
        );
    }

    /**
     * @test
     */
    public function executeWithSupportedEmptySuffixAsString(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => '',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\'$/'
        );
    }

    /**
     * @test
     */
    public function executeWithSupportedEmptySuffixAsNull(): void
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => null
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^\/my\/path\/to\/composer.phar \'run-script\' \'--no-ansi\' \'--no-interaction\' \'my-script\'$/'
        );
    }

    /**
     * @test
     */
    public function executeWithUnsupportedSuffixThrowsException(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
            'command' => 'run-script',
            'additionalArguments' => 'my-script',
            'suffix' => ['&& echo \'Hello world!\'']
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function executeUserConfiguredComposerUpdateCommand(): void
    {
        $options = [
            'composerCommandPath' => 'composer',
            'command' => 'update',
            'arguments' => [
                '--no-ansi',
                '--no-interaction',
                '--no-dev',
                '--no-progress',
                '--classmap-authoritative'
            ]
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted(
            '/^composer \'update\' \'--no-ansi\' \'--no-interaction\' \'--no-dev\' \'--no-progress\' \'--classmap-authoritative\' 2>&1$/'
        );
    }

    /**
     * @return Task
     */
    protected function createTask()
    {
        return new CommandTask();
    }
}
