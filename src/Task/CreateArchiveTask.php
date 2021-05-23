<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Generator\IdGeneratorInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A task for creating an zip / tar.gz / tar.bz2 archive.
 *
 * Needs the following options:
 *
 * * sourceDirectory - The directory which should be compressed.
 * * targetFile - The target file. The file ending defines the format. Supported are .zip, .tar.gz, .tar.bz2.
 * * baseDirectory - The base directory in the compressed archive in which all files should reside in.
 * * exclude - An array of exclude patterns, as being understood by tar (optional)
 *
 * This task needs the following unix command line tools:
 *
 * * tar / gnutar
 * * zip
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\CreateArchiveTask', [
 *              'sourceDirectory' => '/var/www/outerspace',
 *              'targetFile' => '/var/www/outerspace.zip',
 *              'baseDirectory' => 'compressedSpace',
 *              'exclude' => [
 *                  '*.bak'
 *              ]
 *          ]
 *      );
 */
class CreateArchiveTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var IdGeneratorInterface
     */
    private $idGenerator;

    public function __construct(FilesystemInterface $filesystem, IdGeneratorInterface $idGenerator)
    {
        $this->filesystem = $filesystem;
        $this->idGenerator = $idGenerator;
    }

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $this->shell->execute('rm -f ' . $options['targetFile'] . '; mkdir -p ' . dirname($options['targetFile']), $node, $deployment);
        $sourcePath = $deployment->getApplicationReleasePath($node);

        $tarOptions = sprintf(' --transform="s,^%s,%s," ', ltrim($sourcePath, '/'), $options['baseDirectory']);
        foreach ($options['exclude'] as $excludePattern) {
            $tarOptions .= sprintf(' --exclude="%s" ', $excludePattern);
        }

        if (substr($options['targetFile'], -7) === '.tar.gz') {
            $tarOptions .= sprintf(' -czf %s %s', $options['targetFile'], $sourcePath);
            $this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);
        } elseif (substr($options['targetFile'], -8) === '.tar.bz2') {
            $tarOptions .= sprintf(' -cjf %s %s', $options['targetFile'], $sourcePath);
            $this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);
        } elseif (substr($options['targetFile'], -4) === '.zip') {
            $temporaryDirectory = $this->filesystem->getTemporaryDirectory() . '/' . $this->idGenerator->generate('f3_deploy');
            $this->shell->execute(sprintf('mkdir -p %s', $temporaryDirectory), $node, $deployment);
            $tarOptions .= sprintf(' -cf %s/out.tar %s', $temporaryDirectory, $sourcePath);
            $this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);
            $this->shell->execute(sprintf('cd %s; tar -xf out.tar; rm out.tar; zip --quiet -9 -r out %s', $temporaryDirectory, $options['baseDirectory']), $node, $deployment);
            $this->shell->execute(sprintf('mv %s/out.zip %s; rm -Rf %s', $temporaryDirectory, $options['targetFile'], $temporaryDirectory), $node, $deployment);
        }
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sourceDirectory', 'targetFile', 'baseDirectory']);
        $resolver->setDefault('exclude', []);
        $resolver->setAllowedTypes('exclude', 'array');

        $resolver->setAllowedValues('sourceDirectory', function ($directory) {
            return $this->filesystem->isDirectory($directory);
        });
        $resolver->setAllowedValues('targetFile', static function ($targetFile) {
            return preg_match('/\.(tar\.gz|tar\.bz2|zip)$/', $targetFile);
        });
    }
}
