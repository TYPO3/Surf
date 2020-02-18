<?php
namespace TYPO3\Surf\Tests\Unit\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Composer\InstallTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the TagTask
 */
class InstallTaskTest extends BaseTaskTest
{
    /**
     * Set up test dependencies
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function noComposerCommandPathGivenThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);
        $options = [
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
    }

    /**
     * @test
     */
    public function executeUserConfiguredComposerCommand()
    {
        $options = [
            'composerCommandPath' => '/my/path/to/composer.phar',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/^\/my\/path\/to\/composer.phar install --no-ansi --no-interaction --no-dev --no-progress --classmap-authoritative 2>&1$/');
    }

    /**
     * @test
     */
    public function executeUserConfiguredComposerCommandWithAdditionalArguments()
    {
        $options = [
            'composerCommandPath' => 'composer',
            'additionalArguments' => ['--ignore-platform-reqs', '--no-scripts'],
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/^composer install --no-ansi --no-interaction --no-dev --no-progress --classmap-authoritative \'--ignore-platform-reqs\' \'--no-scripts\' 2>&1$/');
    }

    /**
     * @test
     */
    public function executeUserConfiguredComposerCommandWithAdditionalArgumentsAsString()
    {
        $options = [
            'composerCommandPath' => 'composer',
            'additionalArguments' => '--ignore-platform-reqs',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/^composer install --no-ansi --no-interaction --no-dev --no-progress --classmap-authoritative \'--ignore-platform-reqs\' 2>&1$/');
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new InstallTask();
    }
}
