<?php
namespace TYPO3\Surf\Integration;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Console\Command\Command;
use TYPO3\Surf\Integration\FactoryInterface;

/**
 * Trait FactoryAwareTrait
 */
trait FactoryAwareTrait
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

}