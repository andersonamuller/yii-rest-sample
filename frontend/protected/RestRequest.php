<?php
class RestRequest
{
	protected $url;
	protected $verb;
	protected $requestBody;
	protected $requestLength;
	protected $username;
	protected $password;
	protected $acceptType;
	protected $responseBody;
	protected $responseInfo;

	public function __construct($url = null, $verb = 'GET', $requestBody = null)
	{
		$this->url = $url;
		$this->verb = $verb;
		$this->requestBody = $requestBody;
		$this->requestLength = 0;
		$this->username = null;
		$this->password = null;
		$this->acceptType = 'application/json';
		$this->responseBody = null;
		$this->responseInfo = null;

		if ($this->requestBody !== null) {
			$this->buildPostBody();
		}
	}

	public function flush()
	{
		$this->requestBody = null;
		$this->requestLength = 0;
		$this->verb = 'GET';
		$this->responseBody = null;
		$this->responseInfo = null;
	}

	public function execute()
	{
		$ch = curl_init();
		$this->setAuth($ch);

		try {
			switch (strtoupper($this->verb)) {
				case 'HEAD':
					$this->executeHead($ch);
					break;
				case 'GET':
					$this->executeGet($ch);
					break;
				case 'POST':
					$this->executePost($ch);
					break;
				case 'PUT':
					$this->executePut($ch);
					break;
				case 'DELETE':
					$this->executeDelete($ch);
					break;
				default:
					throw new InvalidArgumentException('Current verb (' . $this->verb . ') is an invalid REST verb.');
			}
		} catch (InvalidArgumentException $e) {
			curl_close($ch);
			throw $e;
		} catch (Exception $e) {
			curl_close($ch);
			throw $e;
		}
	}

	public function buildPostBody($data = null)
	{
		$data = ($data !== null) ? $data : $this->requestBody;

		if (!is_array($data)) {
			throw new InvalidArgumentException('Invalid data input for postBody.  Array expected');
		}

		$data = http_build_query($data, '', '&');
		$this->requestBody = $data;
	}

	protected function executeHead($ch)
	{
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');

		$this->doExecute($ch);
	}

	protected function executeGet($ch)
	{
		$this->doExecute($ch);
	}

	protected function executePost($ch)
	{
		if (!is_string($this->requestBody)) {
			$this->buildPostBody();
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		curl_setopt($ch, CURLOPT_POST, true);

		$this->doExecute($ch);
	}

	protected function executePut($ch)
	{
		if (!is_string($this->requestBody)) {
			$this->buildPostBody();
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

		$this->doExecute($ch);
	}

	protected function executeDelete($ch)
	{
		if (!is_string($this->requestBody)) {
			$this->buildPostBody();
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		$this->doExecute($ch);
	}

	protected function doExecute(&$curlHandle)
	{
		$this->setCurlOpts($curlHandle);
		$this->responseBody = curl_exec($curlHandle);
		$this->responseInfo = curl_getinfo($curlHandle);

		curl_close($curlHandle);
	}

	protected function setCurlOpts(&$curlHandle)
	{
		curl_setopt($curlHandle, CURLOPT_TIMEOUT, 10);
		curl_setopt($curlHandle, CURLOPT_URL, $this->url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array(
			'Accept: ' . $this->acceptType
		));
	}

	protected function setAuth(&$curlHandle)
	{
		if ($this->username !== null && $this->password !== null) {
			curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			curl_setopt($curlHandle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
	}

	public function getAcceptType()
	{
		return $this->acceptType;
	}

	public function setAcceptType($acceptType)
	{
		$this->acceptType = $acceptType;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	public function getResponseBody()
	{
		return $this->responseBody;
	}

	public function getResponseInfo()
	{
		return $this->responseInfo;
	}

	public function getStatusCode()
	{
		return $this->responseInfo['http_code'];
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setUrl($url)
	{
		$this->url = $url;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function getVerb()
	{
		return $this->verb;
	}

	public function setVerb($verb)
	{
		$this->verb = $verb;
	}

	public function setResponseHeader($status = null)
	{
		$codes = Array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);

		if ($status === null) {
			$status = $this->getStatusCode();
		}
		$message = (isset($codes[$status])) ? $codes[$status] : '';

		header('HTTP/1.1 ' . $status . ' ' . $message);
	}
}
