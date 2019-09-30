<?php
/**
 * @testcase
 */

namespace Test\NBrowserKit;

require_once __DIR__ . '/../bootstrap.php';

use NBrowserKit\RequestConverter;
use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use Symfony\Component\BrowserKit;
use Tester\Assert;
use Tester\TestCase;



class RequestConverterTest extends TestCase
{

	public function testReturnsNetteHttpIRequest(): void
	{
		$input = new BrowserKit\Request('http://example.com/', 'GET');
		$output = RequestConverter::convertRequest($input);

		Assert::type(IRequest::class, $output);
	}



	public function testConvertsUrl(): void
	{
		$input = new BrowserKit\Request('http://example.com/tadaa', 'POST', ['foo' => 'bar']);
		$output = RequestConverter::convertRequest($input);

		$url = $output->getUrl();
		Assert::type(UrlScript::class, $url);
		Assert::same('http://example.com/tadaa', (string) $url);
	}



	public function testConvertsQueryString(): void
	{
		$input = new BrowserKit\Request('http://example.com/?e=mc^2', 'GET');
		$output = RequestConverter::convertRequest($input);

		Assert::same(['e' => 'mc^2'], $output->getQuery());
	}



	public function testConvertsMethod(): void
	{
		$input = new BrowserKit\Request('http://example.com/', 'POST', ['foo' => 'bar']);
		$output = RequestConverter::convertRequest($input);

		Assert::same('POST', $output->getMethod());
	}



	public function testConvertsBodyParameters(): void
	{
		$input = new BrowserKit\Request('http://example.com/', 'POST', ['foo' => 'bar']);
		$output = RequestConverter::convertRequest($input);

		Assert::same(['foo' => 'bar'], $output->getPost());
	}



	public function testConvertsFiles(): void
	{
		$files = [
			'first' => [
				'name' => 'filename.txt',
				'type' => 'application/octet-stream',
				'size' => 123,
				'tmp_name' => '/tmp/filename.txt',
				'error' => UPLOAD_ERR_OK,
			],
			'second' => [
				'name' => 'goatse.jpg',
				'type' => 'image/jpeg',
				'size' => 21324,
				'tmp_name' => '/tmp/goatse.jpg',
				'error' => UPLOAD_ERR_INI_SIZE,
			],
		];
		$input = new BrowserKit\Request('http://example.com/', 'POST', [], $files);
		$output = RequestConverter::convertRequest($input);

		Assert::count(2, $output->getFiles());

		$firstFile = $output->getFile('first');
		Assert::same('filename.txt', $firstFile->getName());
		Assert::same(123, $firstFile->getSize());
		Assert::same('/tmp/filename.txt', $firstFile->getTemporaryFile());
		Assert::same(UPLOAD_ERR_OK, $firstFile->getError());
		Assert::true($firstFile->isOk());

		$secondFile = $output->getFile('second');
		Assert::same('goatse.jpg', $secondFile->getName());
		Assert::same(21324, $secondFile->getSize());
		Assert::same('/tmp/goatse.jpg', $secondFile->getTemporaryFile());
		Assert::same(UPLOAD_ERR_INI_SIZE, $secondFile->getError());
		Assert::false($secondFile->isOk());
	}



	public function testConvertsFilesMultiple(): void
	{
		$files = [
			'foo' => [
				[
					'name' => 'filename.txt',
					'type' => 'application/octet-stream',
					'size' => 123,
					'tmp_name' => '/tmp/filename.txt',
					'error' => UPLOAD_ERR_OK,
				],
				[
					'name' => 'goatse.jpg',
					'type' => 'image/jpeg',
					'size' => 21324,
					'tmp_name' => '/tmp/goatse.jpg',
					'error' => UPLOAD_ERR_INI_SIZE,
				],
			],
		];
		$input = new BrowserKit\Request('http://example.com/', 'POST', [], $files);
		$output = RequestConverter::convertRequest($input);

		Assert::count(1, $output->getFiles());
		$fooFiles = $output->getFile('foo');
		Assert::type('array', $fooFiles);
		Assert::count(2, $fooFiles);
		Assert::type(FileUpload::class, $fooFiles[0]);
		Assert::type(FileUpload::class, $fooFiles[1]);
	}



	public function testConvertsCookies(): void
	{
		$input = new BrowserKit\Request('http://example.com/', 'GET', [], [], ['PHPSESSID' => 'bflmpsvz']);
		$output = RequestConverter::convertRequest($input);

		Assert::same(['PHPSESSID' => 'bflmpsvz'], $output->getCookies());
	}



	public function testConvertsHeaders(): void
	{
		$serverVariables = [
			'HTTP_HOST' => 'www.damejidlo.cz',
			'HTTP_CACHE_CONTROL' => 'max-age=0',
			'FOO_BAR' => 'trolololololololo lalalalala',
			'CONTENT_FOO' => 'Ignored for some reason',
		];
		$input = new BrowserKit\Request('http://example.com/', 'GET', [], [], [], $serverVariables);
		$output = RequestConverter::convertRequest($input);

		$expectedHeaders = [
			'host' => 'www.damejidlo.cz',
			'cache-control' => 'max-age=0',
			'foo-bar' => 'trolololololololo lalalalala',
		];
		Assert::same($expectedHeaders, $output->getHeaders());
	}



	public function testConvertsContent(): void
	{
		$input = new BrowserKit\Request('http://example.com/', 'POST', [], [], [], [], 'Guten Tag');
		$output = RequestConverter::convertRequest($input);

		Assert::same('Guten Tag', $output->getRawBody());
	}

}



(new RequestConverterTest)->run();
