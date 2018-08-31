<?php

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

/**
 * A task
 */
abstract class Task
{

    /**
     * Executes this action
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    abstract public function execute(Node $node, Application $application, Deployment $deployment, array $options = []);

    /**
     * Rollback this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->configureOptions($options);
    }

    /**
     * Simulate this task (e.g. by logging commands it would execute)
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->configureOptions($options);
    }

    /**
     * @param array $options
     *
     * @return array
     * @throws \Exception
     */
    protected function configureOptions(array $options = [])
    {
        try {
            $resolver = new OptionsResolver();
            // We set all global options as defined options, otherwise we would get a lot of exceptions
            $resolver->setDefined(array_keys($options));
            $this->resolveOptions($resolver);
            return $resolver->resolve($options);
        } catch (MissingOptionsException $e) {
            throw new InvalidOptionsException($e->getMessage(), $e->getCode());
        } catch (InvalidOptionsException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        } catch (NoConfigurationException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        } catch (NoSuchOptionException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        } catch (OptionDefinitionException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        } catch (UndefinedOptionsException $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        } catch (ExceptionInterface $e) {
            throw new InvalidConfigurationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Exception
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        // Configure your options here, required, normalization etc.
    }
}
