<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\TYPO3\CMS;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Version\VersionCheckerInterface;
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
    private VersionCheckerInterface $versionChecker;

    public function __construct(VersionCheckerInterface $versionChecker)
    {
        $this->versionChecker = $versionChecker;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        /** @var CMS $application */
        Assert::isInstanceOf($application, CMS::class);

        try {
            $scriptFileName = $this->getTypo3ConsoleScriptFileName($node, $application, $deployment, $options);
        } catch (InvalidConfigurationException $e) {
            $deployment->getLogger()->warning('TYPO3 Console script (' . $options['scriptFileName'] . ') was not found! Make sure it is available in your project, you set the "scriptFileName" option correctly or remove this task (' . self::class . ') in your deployment configuration!');
            return;
        }

        $options = $this->configureOptions($options);

        $commandArguments = [$scriptFileName];

        if ($this->versionChecker->isSatisified('helhum/typo3-console', '>= 7.0.0')) {
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
