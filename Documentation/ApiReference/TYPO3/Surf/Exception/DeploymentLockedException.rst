-------------------------------------------------
TYPO3\\Surf\\Exception\\DeploymentLockedException
-------------------------------------------------

.. php:namespace: TYPO3\\Surf\\Exception

.. php:class:: DeploymentLockedException

    .. php:attr:: message

        protected

    .. php:attr:: code

        protected

    .. php:attr:: file

        protected

    .. php:attr:: line

        protected

    .. php:method:: deploymentLockedBy(Deployment $deployment, $currentDeploymentLockIdentifier)

        :type $deployment: Deployment
        :param $deployment:
        :param $currentDeploymentLockIdentifier:
        :returns: DeploymentLockedException

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
