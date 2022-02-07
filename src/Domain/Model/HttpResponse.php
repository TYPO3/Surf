<?php

namespace TYPO3\Surf\Domain\Model;

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

final class HttpResponse
{
    private string $body;

    private array $headers;

    private int $statusCode;

    public function __construct(string $body, array $headers, int $statusCode)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
