----------------------------------------------
TYPO3\\Surf\\Exception\\TaskExecutionException
----------------------------------------------

.. php:namespace: TYPO3\\Surf\\Exception

.. php:class:: TaskExecutionException

    An exception during task execution

    Something went wrong or an assertion during task execution was not successful.

    .. php:attr:: message

        protected

    .. php:attr:: code

        protected

    .. php:attr:: file

        protected

    .. php:attr:: line

        protected

    .. php:method:: webOpcacheResetExecuteTaskDidNotReturnExpectedResult($scriptUrl)

        :type $scriptUrl: string
        :param $scriptUrl:
        :returns: TaskExecutionException

    .. php:method:: webOpcacheResetCreateScriptTaskCouldNotWritFile($scriptFilename)

        :type $scriptFilename: string
        :param $scriptFilename:
        :returns: TaskExecutionException

    .. php:method:: __clone()

    .. php:method:: __construct($message, $code, $previous)

        :param $message:
        :param $code:
        :param $previous:

    .. php:method:: __wakeup()

    .. php:method:: getMessage()

    .. php:method:: getCode()

    .. php:method:: getFile()

    .. php:method:: getLine()

    .. php:method:: getTrace()

    .. php:method:: getPrevious()

    .. php:method:: getTraceAsString()

    .. php:method:: __toString()
