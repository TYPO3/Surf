-------------------------------
TYPO3\\Surf\\Task\\Git\\TagTask
-------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Git

.. php:class:: TagTask

    A task which can be used to tag a git repository and its submodules

    It takes the following options:

    * tagName - The tag name to use
    * description - The description for the tag
    * recurseIntoSubmodules - If true, tag submodules as well (optional)
    * submoduleTagNamePrefix - Prefix for the submodule tags (optional)

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Git\TagTask', [
                     'tagName' => 'earth2',
                     'description' => 'Another release to save the planet',
                     'recurseIntoSubmodules' => true,
                     'submoduleTagNamePrefix' => 'sub-'
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

    .. php:method:: validateOptions($options)

        :type $options: array
        :param $options:

    .. php:method:: processOptions($options, Deployment $deployment)

        Replace placeholders in option values and set default values

        :type $options: array
        :param $options:
        :type $deployment: Deployment
        :param $deployment:
        :returns: array

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
