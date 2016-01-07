<?php
if (PHP_SAPI !== 'cli') {
    die('This script can only be executed from command line!');
}
$arr = array_keys(require __DIR__ . '/../../../vendor/composer/autoload_classmap.php');
$final = array();
foreach ($arr as $className) {
    if (strpos($className, 'TYPO3\\Surf\\Task\\') === 0) {
        $final[getTaskIdentifier($className)] = $className;
    }
}

file_put_contents(
    __DIR__ . '/../../../Migrations/Code/LegacyClassMap.php',
        '<?php'
        . chr(10)
        . 'return '
        . var_export($final, true)
        . ';'
);

function getTaskIdentifier($className) {
    return
        substr(
            str_replace(
                array(
                    'typo3\\surf',
                    '\\task',
                    '\\',
                ),
                array(
                    'typo3.surf',
                    '',
                    ':',
                ),
                strtolower($className)
            ),
            0,
            -4
        );

}