<?php
namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Task\Neos\Flow\CopyConfigurationTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the SymlinkConfigurationTask
 */
class CopyConfigurationTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Flow('TestApplication');

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeOnLocalhostFindsConfigurationRecursively(): void
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->onLocalhost();

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->node);

        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/'");
        $this->assertCommandExecuted("cp '{$configPath}/Settings.yaml' '{$releasesPath}/Configuration/'");
        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/Production/'");
        $this->assertCommandExecuted("cp '{$configPath}/Production/Settings.yaml' '{$releasesPath}/Configuration/Production/'");
    }

    /**
     * @test
     */
    public function executeFindsFilesWithExtensionSpecified(): void
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->onLocalhost();
        $options = [
            'configurationFileExtension' => 'php',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->node);

        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/Production/'");
        $this->assertCommandExecuted("cp '{$configPath}/Production/Settings.php' '{$releasesPath}/Configuration/Production/'");
    }

    /**
     * @test
     */
    public function executeOnRemoteHostFindsConfigurationRecursively(): void
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('remote');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->node);

        $this->assertCommandExecuted("ssh remote \"mkdir -p '{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("scp '{$configPath}/Settings.yaml' remote:\"'{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("ssh remote \"mkdir -p '{$releasesPath}/Configuration/Production/'\"");
        $this->assertCommandExecuted("scp '{$configPath}/Production/Settings.yaml' remote:\"'{$releasesPath}/Configuration/Production/'\"");
    }

    /**
     * @test
     */
    public function executeOnRemoteHostFindsConfigurationRecursivelyWithSSHPassword(): void
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('remote');
        $this->node->setOption('password', 'password1');

        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->node);

        $this->assertCommandExecuted("ssh -o PubkeyAuthentication=no remote \"mkdir -p '{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("scp -o PubkeyAuthentication=no '{$configPath}/Settings.yaml' remote:\"'{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("ssh -o PubkeyAuthentication=no remote \"mkdir -p '{$releasesPath}/Configuration/Production/'\"");
        $this->assertCommandExecuted("scp -o PubkeyAuthentication=no '{$configPath}/Production/Settings.yaml' remote:\"'{$releasesPath}/Configuration/Production/'\"");
    }

    /**
     * @test
     */
    public function executeOnRemoteHostCorrectlyAppliesSshOptions(): void
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('remote');
        $options = [
            'port' => '22',
            'username' => 'foo',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("ssh -p '22' foo@remote \"");
        $this->assertCommandExecuted("scp -P '22'");
        $this->assertCommandExecuted("' foo@remote:\"");
    }

    /**
     * @return Task
     */
    protected function createTask()
    {
        return new CopyConfigurationTask();
    }
}
