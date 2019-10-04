<?php declare(strict_types=1);

namespace NBrowserKit;

use Nette\Http\FileUpload;
use Nette\Http\IRequest;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Symfony\Component\BrowserKit;



class RequestConverter
{

	const FILE_KEYS = ['error', 'name', 'size', 'tmp_name', 'type'];



	/**
	 * @param BrowserKit\Request $request
	 * @return IRequest
	 */
	public static function convertRequest(BrowserKit\Request $request)
	{
		return new Request(
			new UrlScript($request->getUri()),
			$request->getParameters(),
			self::convertFiles($request->getFiles()),
			$request->getCookies(),
			self::getHeadersFromServerVariables($request->getServer()),
			$request->getMethod(),
			NULL,
			NULL,
			[$request, 'getContent']
		);
	}



	/**
	 * @param array[] $files
	 * @return FileUpload[]
	 */
	private static function convertFiles(array $files)
	{
		$netteFiles = [];
		foreach ($files as $key => $file) {
			if (array_diff(array_keys($file), self::FILE_KEYS) === []) {
				$netteFiles[$key] = new FileUpload($file);
			} else {
				$netteFiles[$key] = self::convertFiles($file);
			}

		}

		return $netteFiles;
	}



	/**
	 * @param array $serverVariables
	 * @return array
	 */
	private static function getHeadersFromServerVariables(array $serverVariables) {
		$headers = [];
		foreach ($serverVariables as $key => $value) {
			if (substr($key, 0, 8) !== 'CONTENT_') {
				if (substr($key, 0, 5) === 'HTTP_') {
					$key = substr($key, 5);
				}
				$key = strtr($key, '_', '-');
				$headers[$key] = $value;
			}
		}
		return $headers;
	}

}