<?php

declare(strict_types=1);

namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoConfigurationException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Exception\InvalidConfigurationException;

abstract class Task
{
    /**
     * @return mixed|void
     */
    abstract public function execute(Node $node, Application $application, Deployment $deployment, array $options = []);

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->configureOptions($options);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->configureOptions($options);
    }

    protected function configureOptions(array $options = []): array
    {
        try {
            $resolver = new OptionsResolver();
            // We set all global options as defined options, otherwise we would get a lot of exceptions
            $resolver->setDefined(array_keys($options));
            $this->resolveOptions($resolver);
            return $resolver->resolve($options);
        } catch (MissingOptionsException|InvalidOptionsException|NoConfigurationException|NoSuchOptionException|OptionDefinitionException|UndefinedOptionsException|ExceptionInterface $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        }
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        // Configure your options here, required, normalization etc.
    }
}
