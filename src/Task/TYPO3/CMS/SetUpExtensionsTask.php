<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * This task sets up extensions using typo3_console.
 * Set up means: database migration, files, database data.
 *
 * @param array $extensionKeys=array() Extension keys for extensions that should be set up. If empty, all active non core extensions will be set up.
 */
class SetUpExtensionsTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, CMS::class);

        try {
            $scriptFileName = $this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options);
        } catch (InvalidConfigurationException $e) {
            $deployment->getLogger()->warning('TYPO3 Console script (' . $options['scriptFileName'] . ') was not found! Make sure it is available in your project, you set the "scriptFileName" option correctly or remove this task (' . self::class . ') in your deployment configuration!');
            return;
        }

        $options = $this->configureOptions($options);

        $typo3ConsoleVersion = $this->getTypo3ConsoleVersion($node, $application, $deployment, $options);

        $commandArguments = [$scriptFileName];

        if ($typo3ConsoleVersion->getMajor()->getValue() >= 7) {
            $commandArguments[] = 'extension:setup';
            if (!empty($options['extensionKeys'])) {
                foreach ($options['extensionKeys'] as $extensionKey) {
                    $commandArguments[] = '-e';
                    $commandArguments[] = $extensionKey;
                }
            }
        } elseif (empty($options['extensionKeys'])) {
            $commandArguments[] = 'extension:setupactive';
        } else {
            $commandArguments[] = 'extension:setup';
            $commandArguments[] = implode(',', $options['extensionKeys']);
        }

        $this->executeCliCommand(
            $commandArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('extensionKeys', []);
        $resolver->setAllowedTypes('extensionKeys', 'array');
    }
}
