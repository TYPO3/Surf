.. -*- coding: utf-8 -*- with BOM.
.. include:: ../Includes.txt

=============
Smoke Testing
=============

As you do automated deployments, you should check if the website is up and running
before switching it to the live site. This is called a *Smoke Test*. We will give
an example for using the built-in HTTP smoke test.

First, you need to create a virtual host with document root in "<deploymentDirectory>/releases/next/Web".
While a deployment is running, the new website will be available under this URL and can
be used for testing.

Then, add a test as follows to the deployment configuration::

	$workflow = new \TYPO3\Surf\Domain\Model\SimpleWorkflow();

	$smokeTestOptions = array(
		'url' => 'http://your/website/which/you/want/to/test',
		'remote' => TRUE,
		'expectedStatus' => 200,
		'expectedRegexp' => '/somethingYouExpectOnThePage/'
	);
	$workflow->defineTask('mycompany.mypackage:smoketest', \TYPO3\Surf\Task\Test\HttpTestTask::class, $smokeTestOptions);

	$workflow->addTask('mycompany.mypackage:smoketest', 'test', $application);

The HTTP test has the following options:

Most important options:

* url (required): URL which should be loaded
* remote: if TRUE, the smoke test is triggered through the SSH channel on the remote host
  via command-line CURL. If false, it is triggered from the deploying host.
* expectedStatus: expected HTTP status code
* expectedHeaders: HTTP Header Strings which are expected (can be a multiline string,
  each header being on a separate line)
* expectedRegexp: Regular Expression to test the contents of the HTTP response against

Further options:

* timeout (only if remote=FALSE): HTTP timeout to use
* port (only if remote=FALSE): HTTP Port to use
* method (only if remote=FALSE): HTTP method to use (default GET)
* username (only if remote=FALSE): HTTP Authentication username
* password (only if remote=FALSE): HTTP Authentication Password
* data (only if remote=FALSE): HTTP payload
* proxy (only if remote=FALSE): HTTP Proxy to use
* proxyPort  (only if remote=FALSE): HTTP Proxy port to use
* additionalCurlParameters (only if remote=TRUE): list of parameters which
  is directly passed to CURL. Especially useful to e.g. disable SSL certificate
  check (with --insecure)
