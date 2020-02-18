---------------------------------------
TYPO3\\Surf\\Domain\\Clock\\SystemClock
---------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Clock

.. php:class:: SystemClock

    .. php:method:: currentTime()

        :returns: int

    .. php:method:: stringToTime($string, $time = null)

        :type $string: string
        :param $string:
        :type $time: int
        :param $time:
        :returns: false|int

    .. php:method:: createTimestampFromFormat($format, $time)

        :type $format: string
        :param $format:
        :type $time: string
        :param $time:
        :returns: int
