-------------------------------------------------
TYPO3\\Surf\\Domain\\Service\\ShellCommandService
-------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Service

.. php:class:: ShellCommandService

    A shell command service

    .. php:method:: execute($command, Node $node, Deployment $deployment, $ignoreErrors = false, $logOutput = true)

        Execute a shell command (locally or remote depending on the node hostname)

        :type $command: array|string
        :param $command: The shell command to execute, either string or array of commands
        :type $node: Node
        :param $node: Node to execute command against
        :type $deployment: Deployment
        :param $deployment:
        :type $ignoreErrors: bool
        :param $ignoreErrors: If this command should ignore exit codes unequal zero
        :type $logOutput: bool
        :param $logOutput: TRUE if the output of the command should be logged
        :returns: mixed The output of the shell command or false if the command returned a non-zero exit code and $ignoreErrors was enabled.

    .. php:method:: simulate($command, Node $node, Deployment $deployment)

        Simulate a command by just outputting what would be executed

        :type $command: array|string
        :param $command:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :returns: bool

    .. php:method:: executeOrSimulate($command, Node $node, Deployment $deployment, $ignoreErrors = false, $logOutput = true)

        Execute or simulate a command (if the deployment is in dry run mode)

        :type $command: array|string
        :param $command:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $ignoreErrors: bool
        :param $ignoreErrors:
        :type $logOutput: bool
        :param $logOutput: true if the output of the command should be logged
        :returns: mixed false if command failed or command output as string

    .. php:method:: executeLocalCommand($command, Deployment $deployment, $logOutput = true)

        Execute a shell command locally

        :type $command: array|string
        :param $command:
        :type $deployment: Deployment
        :param $deployment:
        :type $logOutput: bool
        :param $logOutput: TRUE if the output of the command should be logged
        :returns: array

    .. php:method:: executeRemoteCommand($command, Node $node, Deployment $deployment, $logOutput = true)

        Execute a shell command via SSH

        :type $command: array|string
        :param $command:
        :type $node: Node
        :param $node:
        :type $deployment: Deployment
        :param $deployment:
        :type $logOutput: bool
        :param $logOutput: TRUE if the output of the command should be logged
        :returns: array

    .. php:method:: executeProcess($deployment, $command, $logOutput, $logPrefix)

        Open a process with symfony/process and process each line by logging and
        collecting its output.

        :type $deployment: \TYPO3\Surf\Domain\Model\Deployment
        :param $deployment:
        :type $command: string
        :param $command:
        :type $logOutput: bool
        :param $logOutput:
        :type $logPrefix: string
        :param $logPrefix:
        :returns: array The exit code of the command and the returned output

    .. php:method:: prepareCommand($command)

        Prepare a command

        :type $command: array|string
        :param $command:
        :returns: string
