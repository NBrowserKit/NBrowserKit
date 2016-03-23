<?php
/**
 * @testcase
 */

namespace Test\NBrowserKit;

require_once __DIR__ . '/../bootstrap.php';

use Mockery\MockInterface;
use NBrowserKit\Client;
use Nette\Application\Application;
use Nette\Application\IRouter;
use Nette\Application\IPresenterFactory;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
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
		$container = $this->prepareContainer();

		$client = new Client;
		$client->setContainer($container);

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



	/**
	 * @return Container|MockInterface
	 */
	private function prepareContainer()
	{
		$container = \Mockery::mock(Container::class);
		$container
			->shouldReceive('removeService')
			->with('httpRequest')
			->once();
		$container
			->shouldReceive('removeService')
			->with('httpResponse')
			->once();
		$container
			->shouldReceive('addService')
			->with('httpRequest', \Mockery::on(function ($request) {
				return ($request instanceof IRequest) && ((string) $request->getUrl() === 'http://localhost/foo');
			}))
			->once();
		$container
			->shouldReceive('addService')
			->with('httpResponse', \Mockery::on(function ($response) {
				return ($response instanceof IResponse);
			}))
			->once();
		$container
			->shouldReceive('getByType')
			->with(IPresenterFactory::class)
			->once()
			->andReturn(\Mockery::mock(IPresenterFactory::class));
		$container
			->shouldReceive('getByType')
			->with(IRouter::class)
			->once()
			->andReturn(\Mockery::mock(IRouter::class));
		$container
			->shouldReceive('removeService')
			->with('application')
			->once();
		$container
			->shouldReceive('addService')
			->with('application', \Mockery::type(Application::class))
			->once();

		return $container;
	}

}



(new ClientTest)->run();
