<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Unit test for Deployment
 */
class DeploymentTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function initializeUsesSimpleWorkflowAsDefault() {
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$deployment->initialize();

		$this->assertInstanceOf('TYPO3\Surf\Domain\Model\SimpleWorkflow', $deployment->getWorkflow());
	}

	/**
	 * @test
	 */
	public function getNodesReturnsNodesFromApplicationsAsSet() {
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$application1 = new \TYPO3\Surf\Domain\Model\Application('Test application 1');
		$application2 = new \TYPO3\Surf\Domain\Model\Application('Test application 2');

		$application1
			->addNode(new \TYPO3\Surf\Domain\Model\Node('test1.example.com'));
		$application2
			->addNode(new \TYPO3\Surf\Domain\Model\Node('test1.example.com'))
			->addNode(new \TYPO3\Surf\Domain\Model\Node('test2.example.com'));

		$deployment
			->addApplication($application1)
			->addApplication($application2);

		$nodes = $deployment->getNodes();
		$nodeNames = array_map(function($node) { return $node->getName(); }, $nodes);
		sort($nodeNames);

		$this->assertEquals(array('test1.example.com', 'test2.example.com'), $nodeNames);
	}

	/**
	 * @test
	 */
	public function initializeCreatesReleaseIdentifier() {
		$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$deployment->setWorkflow($workflow);
		$deployment->initialize();

		$releaseIdentifier = $deployment->getReleaseIdentifier();
		$this->assertNotEmpty($releaseIdentifier);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\Exception
	 */
	public function initializeIsAllowedOnlyOnce() {
		$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
		$deployment = new \TYPO3\Surf\Domain\Model\Deployment('Test deployment');
		$deployment->setWorkflow($workflow);
		$deployment->initialize();

		$deployment->initialize();
	}

}
?>