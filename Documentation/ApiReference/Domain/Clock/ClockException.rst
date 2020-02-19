------------------------------------------
TYPO3\\Surf\\Domain\\Clock\\ClockException
------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Clock

.. php:class:: ClockException

    .. php:attr:: message

        protected

    .. php:attr:: code

        protected

    .. php:attr:: file

        protected

    .. php:attr:: line

        protected

    .. php:method:: stringCouldNotBeConvertedToTimestamp($string)

        :type $string: string
        :param $string:
        :returns: ClockException

    .. php:method:: formatCouldNotBeConvertedToTimestamp($format, $time)

        :type $format: string
        :param $format:
        :type $time: int
        :param $time:
        :returns: ClockException

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
