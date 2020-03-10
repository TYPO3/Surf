<?php
namespace TYPO3\Surf\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Domain\Model\Deployment;

interface FactoryInterface
{
    /**
     * @return Command[]
     */
    public function createCommands(): array;

    public function createOutput(): OutputInterface;

    public function getDeployment(string $deploymentName, string $configurationPath = null, bool $simulateDeployment = true, bool $initialize = true, bool $forceDeployment = false): Deployment;

    /**
     * Get available deployment names
     *
     * Will look up all .php files in the directory ./.surf/ or the given path if specified.
     */
    public function getDeploymentNames(string $path = null): array;

    /**
     * Get the root path of the surf deployment declarations
     *
     * This defaults to ./.surf if a NULL path is given.
     */
    public function getDeploymentsBasePath(string $path = null): string;

    /**
     * Get the base path to local workspaces
     */
    public function getWorkspacesBasePath(string $path = null): string;
}
