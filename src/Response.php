<?php

namespace NBrowserKit;


use Nette\Http\IResponse;
use Nette\Http\Response as NetteResponse;



class Response implements IResponse
{

	/** @var array */
	private $headers = [];

	/** @var int */
	private $code = 200;


	/**
	 * @param string $name
	 * @param string $value
	 * @return static
	 */
	public function addHeader(string $name, string $value)
	{
		$this->headers[$name] = $value;

		return $this;
	}



	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}



	/**
	 * @param string $header
	 * @return string|null
	 */
	public function getHeader(string $header): ?string
	{
		return array_key_exists($header, $this->headers) ? $this->headers[$header] : null;
	}



	/**
	 * Sets a HTTP header and replaces a previous one.
	 *
	 * @param  string $name
	 * @param  string $value
	 * @return static
	 */
	public function setHeader(string $name, ?string $value)
	{
		if ($value === NULL) {
			unset($this->headers[$name]);
		} else {
			$this->headers[$name] = $value;
		}

		return $this;
	}

	/**
	 * Sets HTTP response code.
	 *
	 * @return static
	 */
	public function setCode(int $code)
	{
		$this->code = $code;

		return $this;
	}

	/**
	 * Returns HTTP response code.
	 */
	public function getCode(): int
	{
		return $this->code;
	}

	/**
	 * Sets a Content-type HTTP header.
	 *
	 * @return static
	 */
	public function setContentType(string $type, string $charset = null)
	{
		$this->setHeader('Content-Type', $type . ($charset !== null ? '; charset=' . $charset : ''));
		return $this;
	}

	/**
	 * Redirects to a new URL.
	 */
	public function redirect(string $url, int $code = self::S302_FOUND): void
	{
		$this->setCode($code);
		$this->setHeader('Location', $url);
	}

	/**
	 * Sets the number of seconds before a page cached on a browser expires.
	 *
	 * @param  string|NULL like '20 minutes', NULL means "must-revalidate"
	 *
	 * @return static
	 */
	public function setExpiration($seconds)
	{
		// TODO: Implement setExpiration() method.
	}

	/**
	 * Checks if headers have been sent.
	 */
	public function isSent(): bool
	{
		return false;
	}

	/**
	 * Sends a cookie.
	 *
	 * @param  string|int|\DateTimeInterface $expire time, value 0 means "until the browser is closed"
	 *
	 * @return static
	 */
	public function setCookie(string $name, string $value, $expire, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null)
	{
		$parts = [
			rawurlencode($name) . '=' . rawurlencode($value)
		];
		if ($expire !== 0 && $expire !== '0') {
			if ($expire instanceof \DateTimeInterface) {
				$parts[] = 'Expires=' . $expire->format('r');
			} else {
				$parts[] = 'Expires=' . rawurlencode($expire);
			}
		}
		if ($path !== null) {
			$parts[] = 'Path=' . rawurlencode($path);
		}
		if ($domain !== null) {
			$parts[] = 'Domain=' . rawurlencode($domain);
		}
		if ($secure === true) {
			$parts[] = 'Secure';
		}
		if ($httpOnly) {
			$parts[] = 'HttpOnly';
		}

		$this->addHeader('Set-Cookie', join('; ', $parts));
	}

	/**
	 * Deletes a cookie.
	 */
	public function deleteCookie(string $name, string $path = null, string $domain = null, bool $secure = null)
	{
		$this->setCookie($name, '', 0, $path, $domain, $secure);
	}

}
