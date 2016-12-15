<?php


namespace TYPO3\Surf\Task\Php;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use RandomLib\Factory;
use RandomLib\Generator;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Create a script and execute it to reset the PHP opcache
 *
 * The opcache reset has to be done in the webserver process, so a simple CLI command would not help.
 */
class WebOpcacheResetTask extends Task implements ShellCommandServiceAwareInterface
{

    /**
     * @var int
     */
    const DEFAULT_SCRIPT_IDENTIFIER_LENGTH = 32;

    /**
     * @var string
     */
    const DEFAULT_SCRIPT_PREFIX = 'surf-opcache-reset';

    /**
     * @var string
     */
    const SCRIPT_CODE = '<?php if (function_exists("opcache_reset")) { opcache_reset(); } @unlink(__FILE__); echo "success";';

    use ShellCommandServiceAwareTrait;

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if ( ! $deployment->isDryRun()) {
            if ( ! isset($options['baseUrl'])) {
                throw new InvalidConfigurationException('No "baseUrl" option provided for WebOpcacheResetTask',
                    1421932609);
            }

            // Defaults to Web in the CMS Application
            $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
            $basePath = isset($options['scriptBasePath']) ? $options['scriptBasePath'] : Files::concatenatePaths(array(
                $deployment->getApplicationReleasePath($application),
                $webDirectory,
            ));

            $identifier = isset($options['scriptIdentifier']) ? $options['scriptIdentifier'] : $this->generateRandomIdentifier();
            $filename = sprintf('%s-%s.php', self::DEFAULT_SCRIPT_PREFIX, $identifier);
            $command = sprintf('echo %s > %s', escapeshellarg(self::SCRIPT_CODE), escapeshellarg($basePath.'/'.$filename));

            $this->shell->execute($command, $node, $deployment);

            $url = rtrim($options['baseUrl'], '/').'/'.$filename;
            if ($this->executeScript($url) !== 'success') {
                $deployment->getLogger()->warning('Executing PHP opcache reset script at "'.$url.'" did not return expected result');
            }
        }
    }

    /**
     * @return string
     */
    private function generateRandomIdentifier()
    {
        // Generate random identifier
        $factory   = new Factory();
        $generator = $factory->getMediumStrengthGenerator();

        return $generator->generateString(self::DEFAULT_SCRIPT_IDENTIFIER_LENGTH, Generator::CHAR_ALNUM);
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function executeScript($url)
    {
        return file_get_contents($url);
    }
}