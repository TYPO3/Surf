<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Prophecy\Promise\PromiseInterface;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;

final class FluidPromise implements PromiseInterface
{
    /**
     * @inheritDoc
     */
    public function execute(array $args, ObjectProphecy $object, MethodProphecy $method): object
    {
        return $object->reveal();
    }
}
