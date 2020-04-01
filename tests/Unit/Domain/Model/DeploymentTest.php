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
use TYPO3\Surf\Domain\Service\TaskManager;
use TYPO3\Surf\Exception;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

/**
 * Unit test for Deployment
 */
class DeploymentTest extends TestCase
{
    use KernelAwareTrait;

    /**
     * @test
     */
    public function initializeUsesSimpleWorkflowAsDefault()
    {
        $deployment = new Deployment('Test deployment');
        $deployment->setContainer(static::getKernel()->getContainer());
        $deployment->initialize();

        $this->assertInstanceOf(SimpleWorkflow::class, $deployment->getWorkflow());
    }

    /**
     * @test
     */
    public function getNodesReturnsNodesFromApplicationsAsSet()
    {
        $deployment = new Deployment('Test deployment');
        $deployment->setContainer(static::getKernel()->getContainer());
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
        $nodeNames = array_map(function ($node) {
            return $node->getName();
        }, $nodes);
        sort($nodeNames);

        $this->assertEquals(['test1.example.com', 'test2.example.com'], $nodeNames);
    }

    /**
     * @test
     */
    public function constructorCreatesReleaseIdentifier()
    {
        $deployment = new Deployment('Test deployment');
        $deployment->setContainer(static::getKernel()->getContainer());
        $releaseIdentifier = $deployment->getReleaseIdentifier();
        $this->assertNotEmpty($releaseIdentifier);
    }

    /**
     * @test
     */
    public function initializeIsAllowedOnlyOnce(): void
    {
        $this->expectException(Exception::class);
        $workflow = new SimpleWorkflow($this->prophesize(TaskManager::class)->reveal());
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
    public function deploymentHasDefaultLockIdentifierIfNoIdentifierIsGiven($deploymentLockIdentifier): void
    {
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);
        $deployment->setContainer(static::getKernel()->getContainer());
        $this->assertEquals($deployment->getReleaseIdentifier(), $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasDefinedLockIdentifier(): void
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);

        $this->assertEquals($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasLockIdentifierDefinedByEnvironmentVariable(): void
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        putenv(sprintf('SURF_DEPLOYMENT_LOCK_IDENTIFIER=%s', $deploymentLockIdentifier));
        $deployment = new Deployment('Some name');
        $this->assertEquals($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @return array
     */
    public function wrongDeploymentLockIdentifiersProvided(): array
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
