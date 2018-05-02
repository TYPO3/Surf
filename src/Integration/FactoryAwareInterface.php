<?php
namespace TYPO3\Surf\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

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
