# NBrowserKit

This package implements [Symfony's BrowserKit](https://github.com/symfony/BrowserKit) Client for use with a [Nette](http://nette.org/) application.

## Usage

```php

	$client = new Client;
	$client->setContainer($container);

	$client->request('GET', '/');

	Assert::same(200, $client->getResponse()->getStatus());
	Assert::contains('Hello World', $client->getResponse()->getContent());

```

You can find more [examples in The Symfony Book](http://symfony.com/doc/current/book/testing.html#functional-tests).

## Running Tests

Tests for this package are written using [Nette Tester](http://tester.nette.org/) library. You can run them easily from the command line:

```bash

	composer install --dev
	vendor/bin/tester tests

```