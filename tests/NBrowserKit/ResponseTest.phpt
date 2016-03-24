<?php
/**
 * @testcase
 */

namespace Test\NBrowserKit;

require_once __DIR__ . '/../bootstrap.php';

use NBrowserKit\Response;
use Nette\Http\Response as NetteResponse;
use Tester\Assert;
use Tester\TestCase;



class ResponseTest extends TestCase
{

	public function testExtendsNetteResponse()
	{
		$response = new Response;
		Assert::type(NetteResponse::class, $response);
	}



	public function testAddHeader()
	{
		$response = new Response;
		$response->addHeader('foo', 'bar');
		Assert::same(['foo' => 'bar'], $response->getHeaders());
		Assert::same('bar', $response->getHeader('foo'));
		Assert::same(NULL, $response->getHeader('horseshit'));
	}



	public function testRemoveHeader()
	{
		$response = new Response;
		$response->addHeader('foo', 'bar');
		$response->addHeader('hello', 'world');

		$response->setHeader('foo', NULL);
		Assert::same(['hello' => 'world'], $response->getHeaders());
	}



	public function testSetHeader()
	{
		$response = new Response;
		$response->addHeader('foo', 'bar');

		$response->setHeader('foo', 'something else');
		Assert::same(['foo' => 'something else'], $response->getHeaders());
	}



	public function testDefaultResponseCodeIs200()
	{
		http_response_code(418);
		$response = new Response;
		Assert::same(200, $response->getCode());

		http_response_code(200); // When running tests using vendor/bin/tester, it checks the HTTP code for some reason
	}

}



(new ResponseTest)->run();
