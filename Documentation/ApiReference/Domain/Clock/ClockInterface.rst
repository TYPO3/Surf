------------------------------------------
TYPO3\\Surf\\Domain\\Clock\\ClockInterface
------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Clock

.. php:interface:: ClockInterface

    .. php:method:: currentTime()

        :returns: int

    .. php:method:: stringToTime($string, $time = null)

        :type $string: string
        :param $string:
        :type $time: int
        :param $time:
        :returns: int

    .. php:method:: createTimestampFromFormat($format, $time)

        :type $format: string
        :param $format:
        :type $time: string
        :param $time:
        :returns: int
