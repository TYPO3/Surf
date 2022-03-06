<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task\Php;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * A task to reset the PHP opcache by executing a prepared script with an HTTP request.
 *
 * It takes the following options:
 *
 * * baseUrl - The path where the script is located.
 * * scriptIdentifier - The name of the script. Default is a random string. See `WebOpcacheResetCreateScriptTask`
 *   for more information.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask', [
 *              'baseUrl' => 'https://my.node.com/',
 *              'scriptIdentifier' => 'eraseAllHumans',
 *              'stream_context' => [
 *                     'http' => [
 *                          'header' => 'Authorization: Basic '.base64_encode("username:password"),
 *                     ],
 *              ],
 *          ]
 *      );
 */
class WebOpcacheResetExecuteTask extends Task
{
    private FilesystemInterface $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        $scriptUrl = sprintf('%s/surf-opcache-reset-%s.php', $options['baseUrl'], $options['scriptIdentifier']);

        $result = $this->filesystem->get($scriptUrl, false, $options['stream_context']);

        if ($result !== 'success') {
            if ($options['throwErrorOnWebOpCacheResetExecuteTask']) {
                throw TaskExecutionException::webOpcacheResetExecuteTaskDidNotReturnExpectedResult($scriptUrl);
            }

            $deployment->getLogger()->warning(sprintf('Executing PHP opcache reset script at "%s" did not return expected result', $scriptUrl));
        }
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['baseUrl', 'scriptIdentifier']);
        $resolver->setDefault('throwErrorOnWebOpCacheResetExecuteTask', false);
        $resolver->setDefault('stream_context', null);

        $resolver->setNormalizer('stream_context', fn(Options $options, $value) => is_array($value) ? stream_context_create($value) : null);

        $resolver->setNormalizer('baseUrl', fn(Options $options, $value): string => rtrim($value, '/'));
    }
}
