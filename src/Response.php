<?php

namespace NBrowserKit;


use Nette\Http\Response as NetteResponse;



class Response extends NetteResponse
{

	/**
	 * @var array
	 */
	private $headers = [];



	public function __construct()
	{
		// Intentionally not calling the parent constructor (parent constructor causes side effects).
	}



	/**
	 * @param string $name
	 * @param string $value
	 * @return static
	 */
	public function addHeader($name, $value)
	{
		$this->headers[$name] = $value;

		return $this;
	}



	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * @param string $header
	 * @param string $default
	 * @return string
	 */
	public function getHeader($header, $default = NULL)
	{
		return array_key_exists($header, $this->headers) ? $this->headers[$header] : $default;
	}



	/**
	 * Sends a HTTP header and replaces a previous one.
	 *
	 * @param  string $name
	 * @param  string $value
	 * @return static
	 */
	public function setHeader($name, $value)
	{
		if ($value === NULL) {
			unset($this->headers[$name]);
		} else {
			$this->headers[$name] = $value;
		}

		return $this;
	}

}
