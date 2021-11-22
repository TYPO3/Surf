<?php
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
 * This task sets up extensions using the TYPO3 Console (typo3_console).
 * Set up means: database migration, files, database data.
 *
 * @param array $extensionKeys=array() Extension keys for extensions that should be set up. If empty, all active non core extensions will be set up.
 */
class SetUpExtensionsTask extends AbstractCliTask
{
    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        Assert::isInstanceOf($application, CMS::class);

        try {
            $scriptFileName = $this->getConsoleScriptFileName($node, $application, $deployment, $options);
            $consoleVersion = $this->getConsoleVersion($scriptFileName, $node, $application, $deployment, $options);
        } catch (InvalidConfigurationException $e) {
            $deployment->getLogger()->warning('TYPO3 Console script (' . $options['scriptFileName'] .
                ') was not found! Make sure it is available in your project, you set the "scriptFileName" option correctly or remove this task (' .
                __CLASS__ . ') in your deployment configuration!'
            );
            return;
        }

        $options = $this->configureOptions($options);

        $commandArguments = [$scriptFileName];
        
        // TYPO3 Console behaviour changed since version 7:
        // Version <7: `typo3cms extension:setupactive` was called to install all extensions
        // And `typo3cms extension:setup extension_key_1,extension_key_2` was called to install some extensions by their key
        // Version >=7: `typo3cms extension:setup` is called to install all extensions
        // `typo3cms extension:setup -e extension_key_1 -e extension_key_2` is called to install these two extensions

        if ((int)$consoleVersion >= 7) {
            $commandArguments[] = 'extension:setup';
            if (!empty($options['extensionKeys'])) {
                foreach ($options['extensionKeys'] as $extensionKey) {
                    $commandArguments[] = '-e';
                    $commandArguments[] = $extensionKey;
                }
            }
        } else {
            if (empty($options['extensionKeys'])) {
                $commandArguments[] = 'extension:setupactive';
            } else {
                $commandArguments[] = 'extension:setup';
                $commandArguments[] = implode(',', $options['extensionKeys']);
            }
        }

        $this->executeCliCommand(
            $commandArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    /**
     * @parameter OptionsResolver $resolver
     * @return void
     */
    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('extensionKeys', []);
        $resolver->setAllowedTypes('extensionKeys', 'array');
    }

    /**
     * Get TYPO3 Console version string, e.g. "7.0.0"
     *
     * @param string $scriptFileName
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return string Returns empty string ("") if version could not be determined
     */
    protected function getConsoleVersion(
        string $scriptFileName,
        Node $node,
        Application $application,
        Deployment $deployment,
        array $options
    ): string {
        $consoleVersionCommandOutput = $this->executeCliCommand(
            [$scriptFileName, '--version'],
            $node,
            $application,
            $deployment,
            $options
        );
        $consoleVersionLine = explode(PHP_EOL, $consoleVersionCommandOutput)[0] ?? '';
        return str_replace('TYPO3 Console ', '', $consoleVersionLine);
    }
}
