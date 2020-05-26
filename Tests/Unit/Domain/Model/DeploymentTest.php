<?php

namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;

/**
 * Unit test for Deployment
 */
class DeploymentTest extends TestCase
{
    /**
     * @test
     */
    public function initializeUsesSimpleWorkflowAsDefault(): void
    {
        $deployment = new Deployment('Test deployment');
        $deployment->initialize();

        $this->assertInstanceOf(SimpleWorkflow::class, $deployment->getWorkflow());
    }

    /**
     * @test
     */
    public function getNodesReturnsNodesFromApplicationsAsSet(): void
    {
        $deployment = new Deployment('Test deployment');
        $application1 = new Application('Test application 1');
        $application2 = new Application('Test application 2');

        $application1
            ->addNode(new Node('test1.example.com'));
        $application2
            ->addNode(new Node('test1.example.com'))
            ->addNode(new Node('test2.example.com'));

        $deployment
            ->addApplication($application1)
            ->addApplication($application2);

        $nodes = $deployment->getNodes();
        $nodeNames = array_map(static function (Node $node) {
            return $node->getName();
        }, $nodes);
        sort($nodeNames);

        $this->assertEquals(['test1.example.com', 'test2.example.com'], $nodeNames);
    }

    /**
     * @test
     */
    public function constructorCreatesReleaseIdentifier(): void
    {
        $deployment = new Deployment('Test deployment');

        $releaseIdentifier = $deployment->getReleaseIdentifier();
        $this->assertNotEmpty($releaseIdentifier);
    }

    /**
     * @test
     * @expectedException \TYPO3\Surf\Exception
     */
    public function initializeIsAllowedOnlyOnce()
    {
        $workflow = new SimpleWorkflow();
        $deployment = new Deployment('Test deployment');
        $deployment->setWorkflow($workflow);
        $deployment->initialize();

        $deployment->initialize();
    }

    /**
     * @test
     * @dataProvider wrongDeploymentLockIdentifiersProvided
     *
     * @param mixed $deploymentLockIdentifier
     */
    public function deploymentHasDefaultLockIdentifierIfNoIdentifierIsGiven($deploymentLockIdentifier)
    {
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);

        $this->assertEquals($deployment->getReleaseIdentifier(), $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasDefinedLockIdentifier()
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);

        $this->assertEquals($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasLockIdentifierDefinedByEnvironmentVariable()
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        putenv(sprintf('SURF_DEPLOYMENT_LOCK_IDENTIFIER=%s', $deploymentLockIdentifier));
        $deployment = new Deployment('Some name');
        $this->assertEquals($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentContainsRelativeProjectRootPathForApplicationReleasePath(): void
    {
        $deployment = new Deployment('Some name');

        $application = new Application('Test application 1');
        $application->setDeploymentPath('/deployment/path');

        $releaseIdentifier = $deployment->getReleaseIdentifier();

        $this->assertEquals(
            '/deployment/path/releases/' . $releaseIdentifier,
            $deployment->getApplicationReleasePath($application)
        );
    }

    /**
     * @test
     */
    public function deploymentContainsChangedRelativeProjectRootPathForApplicationReleasePath(): void
    {
        $deployment = new Deployment('Some name');
        $deployment->setRelativeProjectRootPath('htdocs');

        $application = new Application('Test application 1');
        $application->setDeploymentPath('/deployment/path');

        $releaseIdentifier = $deployment->getReleaseIdentifier();

        $this->assertEquals(
            '/deployment/path/releases/' . $releaseIdentifier . '/htdocs',
            $deployment->getApplicationReleasePath($application)
        );
    }

    /**
     * @return array
     */
    public function wrongDeploymentLockIdentifiersProvided()
    {
        return [
            [null],
            [
                ['some array'],
            ],
            [''],
            [new \stdClass()],
        ];
    }

    /**
     * Reset global state
     */
    protected function tearDown()
    {
        putenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER');
    }
}
