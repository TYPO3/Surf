<?php
/****************************************************************************
 * boostrap
 ****************************************************************************/

    $target_user = getenv('TARGET_USER');
    $target_host = getenv('TARGET_HOST');
    $target_path = getenv('TARGET_PATH');
    if(!$target_path) {
        throw new \TYPO3\Surf\Exception\InvalidConfigurationException('Missing TARGET_PATH');
    }
    $source_repo = getenv('GIT_REPO') ? getenv('GIT_REPO') : getenv('CI_REPOSITORY_URL');
    if(!$source_repo) {
        throw new \TYPO3\Surf\Exception\InvalidConfigurationException('Missing GIT_REPO');
    }
    $source_branch = getenv('GIT_BRANCH') ? getenv('GIT_BRANCH') : getenv('CI_COMMIT_REF_NAME');

    $GLOBALS['smoke_uri1'] = getenv('SMOKE1');
    $GLOBALS['smoke_uri2'] = getenv('SMOKE2');
    $GLOBALS['smoke_uri3'] = getenv('SMOKE3');

    $GLOBALS['rewrite_path'] = getenv('REWRITE_PATH');

    $GLOBALS['context'] = getenv('CONTEXT') ? getenv('CONTEXT') : 'Production';

/****************************************************************************
 * Initialisation
 ****************************************************************************/
    $workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();
    $workflow->setEnableRollback(FALSE);
    /** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */
    $deployment->setWorkflow($workflow);

/****************************************************************************
 * Node Setup
 ****************************************************************************/
    $node = new \TYPO3\Surf\Domain\Model\Node($target_host);
    $node->setHostname($target_host);
    $node->setOption('username', $target_user);
    // avoid cleaning of cache directory
    $node->setOption('hardClean', FALSE);

/****************************************************************************
 * Application
 ****************************************************************************/
    $application = new \TYPO3\Surf\Application\TYPO3\Flow();
    $application->setName('FLOW application ' . $source_repo);

    $application->setDeploymentPath($target_path);

    $application->setVersion('3');

    // git
    $application->setOption('packageMethod', 'git');
    $application->setOption('repositoryUrl', $source_repo);
    if($source_branch) {
        $application->setOption('branch', $source_branch);
    }

    // transfer
    $application->setOption('keepReleases', 2);
    $application->setOption('transferMethod', 'rsync');
    $application->setOption('updateMethod', NULL);
    $application->setOption('composerCommandPath', 'composer');

    $application->addNode($node);
    $deployment->addApplication($application);

/****************************************************************************
 * Tasks
 ****************************************************************************/
    $workflow->defineTask(
        'Smoketest1',
        'TYPO3\\Surf\\Task\\Test\\HttpTestTask',
        array(
            'url' => $GLOBALS['smoke_uri1'],
            'expectedStatus' => 200,
            'remote' => TRUE,
            'additionalCurlParameters' => ' -k ' // ignore SSL Zertificate Problems
        )
    );

    $workflow->defineTask(
        'Smoketest2',
        'TYPO3\\Surf\\Task\\Test\\HttpTestTask',
        array(
            'url' => $GLOBALS['smoke_uri2'],
            'expectedStatus' => 200,
            'remote' => TRUE,
            'timeout' => 60,
            'additionalCurlParameters' => ' -k ' // ignore SSL Zertificate Problems
        )
    );

    $workflow->defineTask(
        'Smoketest3',
        'TYPO3\\Surf\\Task\\Test\\HttpTestTask',
        array(
            'url' => $GLOBALS['smoke_uri3'],
            'expectedStatus' => 200,
            'remote' => TRUE,
            'timeout' => 60,
            'additionalCurlParameters' => ' -k ' // ignore SSL Zertificate Problems
        )
    );

    $workflow->defineTask(
        'FixPermissions',
        'TYPO3\\Surf\\Task\\ShellTask',
        array(
            'command' => 'chmod 777 -R {releasePath}/Configuration; chmod 777 -R {releasePath}/Data; chmod 777 -R {releasePath}/Web; ',
            'logOutput' => TRUE
        )
    );


    $workflow->defineTask(
        'FixHtaccess',
        'TYPO3\\Surf\\Task\\ShellTask',
        array(
            'command' => 'cd {releasePath} ; sed -i "s/RewriteBase \/.*/RewriteBase \/' . $GLOBALS['rewrite_path'] . '\/ \n SetEnv FLOW_CONTEXT ' . addcslashes($GLOBALS['context'], '/\\.') . ' /g" {releasePath}/Web/.htaccess'
        )
    );

    $workflow->defineTask(
        'FreezePackages',
        'TYPO3\\Surf\\Task\\ShellTask',
        array(
            'command' => 'cd {releasePath} ; ./flow package:freeze'
        )
    );

    $workflow->defineTask(
        'CopySession',
        'TYPO3\\Surf\\Task\\ShellTask',
        array(
            'command' => 'cp -R {currentPath}/Data/Temporary/Production/Cache/Data/Flow_Session_* {releasePath}/Data/Temporary/Production/Cache/Data/',
            'ignoreErrors' => true
        )
    );


/****************************************************************************
 * Execute
 ****************************************************************************/
    $deployment->onInitialize(function() use ($workflow, $application) {
        $workflow->removeTask('typo3.surf:flow3:setfilepermissions');
        $workflow->removeTask('typo3.surf:flow3:migrate');

        if($GLOBALS['rewrite_path']) {
            $workflow->beforeStage(
                'migrate',
                array(
                    'FixHtaccess'
                )
            );
        }

        $workflow->afterTask(
            'TYPO3\\Surf\\Task\\TYPO3\\Flow\\CopyConfigurationTask',
            array(
                'FreezePackages'
            )
        );

        $workflow->beforeStage(
            'test',
            array(
                'CopySession',
                'FixPermissions'
            )
        );

        if($GLOBALS['smoke_uri1']) {
            $workflow->addTask('Smoketest1', 'test', $application);
        }
        if($GLOBALS['smoke_uri2']) {
            $workflow->addTask('Smoketest2', 'test', $application);
        }
        if($GLOBALS['smoke_uri3']) {
            $workflow->addTask('Smoketest3', 'test', $application);
        }
    });
