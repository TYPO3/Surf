--------------------------------
TYPO3\\Surf\\Task\\Git\\PushTask
--------------------------------

.. php:namespace: TYPO3\\Surf\\Task\\Git

.. php:class:: PushTask

    A task which can push to a git remote

    It takes the following options:

    * remote - The git remote to use.
    * refspec - The refspec to push.
    * recurseIntoSubmodules (optional) - If true, push submodules as well.

    Example:
     $workflow
         ->setTaskOptions('TYPO3\Surf\Task\Git\PushTask', [
                 'remote' => 'git@github.com:TYPO3/Surf.git',
                 'refspec' => 'master',
                 'recurseIntoSubmodules' => true
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
