<?php

namespace NBrowserKit;

use Nette\Application\Application;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Symfony\Component\BrowserKit;



class Client extends BrowserKit\Client
{

	/**
	 * @var Container
	 */
	private $container;



	/**
	 * @param Container $container
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @return Container
	 */
	protected function getContainer()
	{
		if ($this->container === NULL) {
			throw new MissingContainerException('Container is missing, use setContainer() method to set it.');
		}

		return $this->container;
	}



	/**
	 * @return BrowserKit\Response
	 */
	public function getResponse()
	{
		return parent::getResponse();
	}



	/**
	 * @return IRequest|NULL
	 */
	public function getRequest()
	{
		return parent::getRequest();
	}



	/**
	 * Makes a request.
	 *
	 * @param IRequest $request
	 * @return BrowserKit\Response
	 * @throws MissingContainerException
	 */
	protected function doRequest($request)
	{
		$container = $this->getContainer();

		$response = new Response;

		$container->removeService('httpRequest');
		$container->addService('httpRequest', $request);
		$container->removeService('httpResponse');
		$container->addService('httpResponse', $response);

		/** @var IPresenterFactory $presenterFactory */
		$presenterFactory = $container->getByType(IPresenterFactory::class);
		/** @var IRouter $router */
		$router = $container->getByType(IRouter::class);
		$application = $this->createApplication($request, $presenterFactory, $router, $response);
		$container->removeService('application');
		$container->addService('application', $application);

		ob_start();
		$application->run();
		$content = ob_get_clean();

		return new BrowserKit\Response($content, $response->getCode(), $response->getHeaders());
	}



	/**
	 * Filters the BrowserKit request to the `Nette\Http` one.
	 *
	 * @param BrowserKit\Request $request
	 * @return IRequest
	 */
	protected function filterRequest(BrowserKit\Request $request)
	{
		return RequestConverter::convertRequest($request);
	}

	protected function createApplication(IRequest $request, IPresenterFactory $presenterFactory, IRouter $router, IResponse $response)
	{
		$application = new Application(
			$presenterFactory,
			$router,
			$request,
			$response
		);

		return $application;
	}

}
