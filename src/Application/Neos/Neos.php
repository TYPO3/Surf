<?php
namespace TYPO3\Surf\Application\Neos;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * A Neos application template
 */
class Neos extends Flow
{
    /**
     * @var array
     */
    private $neosCommands = [
        'domain:add',
        'domain:list',
        'domain:delete',
        'domain:activate',
        'domain:deactivate',

        'site:import',
        'site:export',
        'site:prune',
        'site:list',

        'user:list',
        'user:show',
        'user:create',
        'user:delete',
        'user:activate',
        'user:deactivate',
        'user:setpassword',
        'user:addrole',
        'user:removerole',

        'workspace:publish',
        'workspace:discard',
        'workspace:create',
        'workspace:delete',
        'workspace:rebase',
        'workspace:publishall',
        'workspace:discardall',
        'workspace:list'
    ];

    /**
     * @param string $name
     */
    public function __construct($name = 'Neos')
    {
        parent::__construct($name);

        $this->options = array_merge($this->options, [
            'webDirectory' => self::DEFAULT_WEB_DIRECTORY,
        ]);
    }

    protected function isNeosCommand($command): bool
    {
        return in_array($command, $this->neosCommands, false);
    }

    public function getCommandPackageKey(string $command = ''): string
    {
        if ($this->getVersion() < '4.0') {
            return $this->isNeosCommand($command) ? 'typo3.neos' : 'typo3.flow';
        }
        return $this->isNeosCommand($command) ? 'neos.neos' : 'neos.flow';
    }
}
