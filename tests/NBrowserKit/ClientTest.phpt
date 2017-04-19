<?php
/**
 * @testcase
 */

namespace Test\NBrowserKit;

require_once __DIR__ . '/../bootstrap.php';

use NBrowserKit\Client;
use Nette\Application\Application;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

class ClientTest extends TestCase
{

	public function testRequest()
	{
		$application = \Mockery::mock('overload:' . Application::class);
		$application
			->shouldReceive('run')
			->once()
			->andReturnUsing(function () {
				echo 'It works!';
			});
		$client = new Client;
		$client->setContainer($this->prepareContainer());

		$client->request('POST', '/foo', ['foo' => 'bar']);

		Assert::same(200, $client->getResponse()->getStatus());
		Assert::same('It works!', $client->getResponse()->getContent());
	}



	/**
	 * @throws \NBrowserKit\MissingContainerException
	 */
	public function testThrowsUpIfContainerIsNotSet()
	{
		$client = new Client;
		$client->request('POST', '/foo', ['foo' => 'bar']);
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

	private function prepareContainer(): Container
	{
		$container = new class extends Container
		{
			protected $meta = [
				self::TYPES => [
					'Nette\Application\IPresenterFactory' => [
						true => [
							'presenterFactory',
						],
					],
					'Nette\Application\IRouter' => [
						true => [
							'router',
						],
					],
				],
			];
		};
		$container->addService('presenterFactory', \Mockery::mock(IPresenterFactory::class));
		$container->addService('router', \Mockery::mock(IRouter::class));

		return $container;
	}

}



(new ClientTest)->run();
