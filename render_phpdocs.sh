#!/usr/bin/env bash
# This script is for rendering the Surf PHP Api Docs

API_REFERENCE_PATH=Documentation/APIReference
EXCLUDED_NAMESPACES="TYPO3\\Surf\\Cli;TYPO3\\Surf\\Integration;TYPO3\\Surf\\Command"

if [ ! -f sphpdox.phar ]
then
    echo "* Downloading sphpdox api doc renderer"
    wget https://github.com/Sebobo/sphpdox/releases/download/0.0.1-alpha/sphpdox.phar
    chmod a+x sphpdox.phar
fi

echo "* Deleting old api docs"
rm -rf $API_REFERENCE_PATH/*

echo "* Rendering new api docs"
./sphpdox.phar process 'TYPO3\Surf' src --output $API_REFERENCE_PATH --exclude $EXCLUDED_NAMESPACES --title "ApiReference"

echo "* Cleaning up generated doc folders"
mv $API_REFERENCE_PATH/TYPO3/Surf/* $API_REFERENCE_PATH
rm -rf $API_REFERENCE_PATH/TYPO3

echo "* Done"
