<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Task;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\Surf\Task\VarnishPurgeTask;

class VarnishPurgeTaskTest extends BaseTaskTest
{
    /**
     * @var VarnishPurgeTask
     */
    protected $task;

    /**
     * @test
     */
    public function executeWithDefaultOptions(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 url.purge ./");
    }

    /**
     * @test
     */
    public function executeOverridingDefaultOptions(): void
    {
        $options = [
            'varnishadm' => 'varnishadm',
            'secretFile' => 'secretFile',
            'purgeUrl' => 'banUrl',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/varnishadm -S secretFile -T 127.0.0.1:6082 url.purge banUrl/');
    }

    /**
     * @test
     */
    public function simulateWithDefaultOptions(): void
    {
        $this->task->simulate($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 status/");
    }

    /**
     * @test
     */
    public function simulateOverridingDefaultOptions(): void
    {
        $options = [
            'varnishadm' => 'varnishadm',
            'secretFile' => 'secretFile',
            'banUrl' => 'banUrl',
        ];
        $this->task->simulate($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('/varnishadm -S secretFile -T 127.0.0.1:6082 status/');
    }

    /**
     * @return VarnishPurgeTask
     */
    protected function createTask(): VarnishPurgeTask
    {
        return new VarnishPurgeTask();
    }
}
