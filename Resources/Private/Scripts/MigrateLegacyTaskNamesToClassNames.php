<?php
if (PHP_SAPI !== 'cli') {
    die('This script can only be executed from command line!');
}
require __DIR__ . '/../../../vendor/autoload.php';
$final = require __DIR__ . '/../../../Migrations/Code/LegacyClassMap.php';

$finder = new \Symfony\Component\Finder\Finder();

$files = $finder->files()
    ->ignoreDotFiles(true)
    ->in(__DIR__ . '/../../../src/');

$files = iterator_to_array($files->getIterator());
$files[] = __DIR__ . '/../../../README.md';
foreach ($files as $file) {
    $fileContent = file_get_contents($file);
    foreach ($final as $identifier => $className) {
        $fileContent = str_ireplace($identifier, str_replace('\\', '\\\\', $className), $fileContent);
    }
    file_put_contents($file, $fileContent);
}
