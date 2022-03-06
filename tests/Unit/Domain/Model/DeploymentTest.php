<?php

declare(strict_types=1);

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
     * Reset global state
     */
    protected function tearDown(): void
    {
        putenv('SURF_DEPLOYMENT_LOCK_IDENTIFIER');
    }

    /**
     * @test
     */
    public function initializeUsesSimpleWorkflowAsDefault(): void
    {
        $deployment = new Deployment('Test deployment');
        $deployment->setContainer(static::getKernel()->getContainer());
        $deployment->initialize();

        self::assertInstanceOf(SimpleWorkflow::class, $deployment->getWorkflow());
    }

    /**
     * @test
     */
    public function getNodesReturnsNodesFromApplicationsAsSet(): void
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
        $nodeNames = array_map(static fn(Node $node): string => $node->getName(), $nodes);
        sort($nodeNames);

        self::assertEquals(['test1.example.com', 'test2.example.com'], $nodeNames);
    }

    /**
     * @test
     */
    public function constructorCreatesReleaseIdentifier(): void
    {
        $deployment = new Deployment('Test deployment');
        $deployment->setContainer(static::getKernel()->getContainer());

        $releaseIdentifier = $deployment->getReleaseIdentifier();

        self::assertNotEmpty($releaseIdentifier);
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
     * @param string $deploymentLockIdentifier
     */
    public function deploymentHasDefaultLockIdentifierIfNoIdentifierIsGiven($deploymentLockIdentifier): void
    {
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);
        $deployment->setContainer(static::getKernel()->getContainer());

        self::assertSame($deployment->getReleaseIdentifier(), $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasDefinedLockIdentifier(): void
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        $deployment = new Deployment('Some name', $deploymentLockIdentifier);

        self::assertSame($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
    }

    /**
     * @test
     */
    public function deploymentHasLockIdentifierDefinedByEnvironmentVariable(): void
    {
        $deploymentLockIdentifier = 'Deployment lock identifier';
        putenv(sprintf('SURF_DEPLOYMENT_LOCK_IDENTIFIER=%s', $deploymentLockIdentifier));

        $deployment = new Deployment('Some name');

        self::assertSame($deploymentLockIdentifier, $deployment->getDeploymentLockIdentifier());
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

        self::assertSame(
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

        self::assertSame(
            '/deployment/path/releases/' . $releaseIdentifier . '/htdocs',
            $deployment->getApplicationReleasePath($application)
        );
    }

    /**
     * @return array
     */
    public function wrongDeploymentLockIdentifiersProvided(): array
    {
        return [
            [''],
        ];
    }
}
