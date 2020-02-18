<?php

namespace TYPO3\Surf\Integration;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Humbug\SelfUpdate\Strategy\GithubStrategy;

final class CustomGithubStrategy extends GithubStrategy
{
    const API_URL = 'https://packagist.org/packages/%s.json';

    protected function getApiUrl()
    {
        return sprintf(self::API_URL, $this->getPackageName());
    }
}
