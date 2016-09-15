<?php
namespace TYPO3\Surf\Tests\Unit\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the SymlinkConfigurationTask
 */
class CopyConfigurationTaskTest extends BaseTaskTest
{
    /**
     * Set up test dependencies
     */
    protected function setUp()
    {
        parent::setUp();

        $this->application = new \TYPO3\Surf\Application\TYPO3\Flow('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeOnLocalhostFindsConfigurationRecursively()
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('localhost');

        $this->task->execute($this->node, $this->application, $this->deployment, array());

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->application);

        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/'");
        $this->assertCommandExecuted("cp '{$configPath}/Settings.yaml' '{$releasesPath}/Configuration/'");
        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/Production/'");
        $this->assertCommandExecuted("cp '{$configPath}/Production/Settings.yaml' '{$releasesPath}/Configuration/Production/'");
    }

    /**
     * @test
     */
    public function executeFindsFilesWithExtensionSpecified()
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('localhost');
        $options = array(
            'configurationFileExtension' => 'php',
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->application);

        $this->assertCommandExecuted("mkdir -p '{$releasesPath}/Configuration/Production/'");
        $this->assertCommandExecuted("cp '{$configPath}/Production/Settings.php' '{$releasesPath}/Configuration/Production/'");
    }

    /**
     * @test
     */
    public function executeOnRemoteHostFindsConfigurationRecursively()
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('remote');

        $this->task->execute($this->node, $this->application, $this->deployment, array());

        $configPath = $this->deployment->getDeploymentConfigurationPath();
        $releasesPath = $this->deployment->getApplicationReleasePath($this->application);

        $this->assertCommandExecuted("ssh remote \"mkdir -p '{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("scp '{$configPath}/Settings.yaml' remote:\"'{$releasesPath}/Configuration/'\"");
        $this->assertCommandExecuted("ssh remote \"mkdir -p '{$releasesPath}/Configuration/Production/'\"");
        $this->assertCommandExecuted("scp '{$configPath}/Production/Settings.yaml' remote:\"'{$releasesPath}/Configuration/Production/'\"");
    }

    /**
     * @test
     */
    public function executeOnRemoteHostCorrectlyAppliesSshOptions()
    {
        $deployBasePath = __DIR__ . '/Fixtures/DeploymentConfigurations';
        $this->deployment->setDeploymentBasePath($deployBasePath);
        $this->deployment->setName('test1');
        $this->node->setHostname('remote');
        $options = array(
            'port' => '22',
            'username' => 'foo',
        );

        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted("ssh -p '22' foo@remote \"");
        $this->assertCommandExecuted("scp -P '22'");
        $this->assertCommandExecuted("' foo@remote:\"");
    }

    /**
     * @return \TYPO3\Surf\Domain\Model\Task
     */
    protected function createTask()
    {
        return new \TYPO3\Surf\Task\TYPO3\Flow\CopyConfigurationTask();
    }
}
