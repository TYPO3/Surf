<?php

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

use TYPO3\Surf\Task\VarnishBanTask;

class VarnishBanTaskTest extends BaseTaskTest
{

    /**
     * @var VarnishBanTask
     */
    protected $task;

    /**
     * @test
     */
    public function executeWithDefaultOptions()
    {
        $this->task->execute($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 ban.url '.*'/");
    }

    /**
     * @test
     */
    public function executeOverridingDefaultOptions()
    {
        $options = [
            'varnishadm' => 'varnishadm',
            'secretFile' => 'secretFile',
            'banUrl' => 'banUrl',
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted("/varnishadm -S secretFile -T 127.0.0.1:6082 ban.url 'banUrl'/");
    }

    /**
     * @test
     */
    public function simulateWithDefaultOptions()
    {
        $this->task->simulate($this->node, $this->application, $this->deployment, []);
        $this->assertCommandExecuted("/\/usr\/bin\/varnishadm -S \/etc\/varnish\/secret -T 127.0.0.1:6082 status/");
    }

    /**
     * @test
     */
    public function simulateOverridingDefaultOptions()
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
     * @return VarnishBanTask
     */
    protected function createTask()
    {
        return new VarnishBanTask();
    }
}
