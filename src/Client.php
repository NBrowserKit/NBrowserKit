<?php

namespace NBrowserKit;

use Nette\Application\Application;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\DI\Container;
use Nette\Http\IRequest;
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
		if ($this->container === NULL) {
			throw new MissingContainerException('Container is missing, use setContainer() method to set it.');
		}

		$response = new Response;

		$this->container->removeService('httpRequest');
		$this->container->addService('httpRequest', $request);
		$this->container->removeService('httpResponse');
		$this->container->addService('httpResponse', $response);

		/** @var IPresenterFactory $presenterFactory */
		$presenterFactory = $this->container->getByType(IPresenterFactory::class);
		/** @var IRouter $router */
		$router = $this->container->getByType(IRouter::class);
		$application = new Application(
			$presenterFactory,
			$router,
			$request,
			$response
		);

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

}
