-----------------------------------
TYPO3\\Surf\\Task\\Package\\GitTask
-----------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Package

.. php:class:: GitTask

    A Git package task.

    Package an application by doing a local git update / clone before using the configured "transferMethod" to transfer assets to the application node(s).

    It takes the following options:

    * repositoryUrl - The git remote to use.
    * fetchAllTags (optional) - If true, make a fetch on tags.
    * gitPostCheckoutCommands (optional) - An array with commands to execute after checkout.
    * hardClean (optional) - If true, execute a hard clean.
    * recursiveSubmodules (optional) - If true, handle submodules recursive.
    * verbose (optional) - If true, output verbose information from git.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Package\GitTask', [
                     'repositoryUrl' => 'git@github.com:TYPO3/Surf.git',
                     'verbose' => true,
                     'recursiveSubmodules' => true,
                     'fetchAllTags' => true,
                     'hardClean' => true,
                     'gitPostCheckoutCommands' => [
                         '/var/www/outerspace' => 'composer install'
                     ]
                 ]
             ]
         );

    .. php:attr:: shell

        protected ShellCommandService

    .. php:method:: execute(Node $node, Application $application, Deployment $deployment, $options = [])

        Execute this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: simulate(Node $node, Application $application, Deployment $deployment, $options = [])

        Simulate this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: resolveSha1(Node $node, Deployment $deployment, $options)

        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulateGitCloneOrUpdate($checkoutPath, Node $node, Deployment $deployment, $options)

        :type $checkoutPath: string
        :param $checkoutPath:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: executeOrSimulatePostGitCheckoutCommands($gitPath, $sha1, Node $node, Deployment $deployment, $options)

        :type $gitPath: string
        :param $gitPath:
        :type $sha1: string
        :param $sha1:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: setShellCommandService(ShellCommandService $shellCommandService)

        :type $shellCommandService: ShellCommandService
        :param $shellCommandService:

    .. php:method:: rollback(Node $node, Application $application, Deployment $deployment, $options = [])

        Rollback this task

        :type $node: Node
        :param $node:
        :type $application: Application
        :param $application:
        :type $deployment: Deployment
        :param $deployment:
        :type $options: array
        :param $options:

    .. php:method:: configureOptions($options = [])

        :type $options: array
        :param $options:
        :returns: array

    .. php:method:: resolveOptions(OptionsResolver $resolver)

        :type $resolver: OptionsResolver
        :param $resolver:
