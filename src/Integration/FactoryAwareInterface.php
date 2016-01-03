<?php
namespace TYPO3\Surf\Integration;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

/**
 * interface FactoryAwareInterface
 */
interface FactoryAwareInterface
{
    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory);
}
