-------------------------------------------
TYPO3\\Surf\\Domain\\Filesystem\\Filesystem
-------------------------------------------

.. php:namespace: TYPO3\\Surf\\Domain\\Filesystem

.. php:class:: Filesystem

    .. php:method:: put($filename, $content)

        :type $filename: string
        :param $filename:
        :type $content: string
        :param $content:
        :returns: bool|int

    .. php:method:: get($filename, $includePath = false, $streamContext = null)

        :type $filename: string
        :param $filename:
        :type $includePath: bool
        :param $includePath:
        :type $streamContext: resource|null
        :param $streamContext:
        :returns: false|string
