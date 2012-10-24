<?php
/**
 * HttpDigestAuthenticationData
 *
 * This class represents the authorization information from the headers of a request.
 *
 * Inspired by
 * - https://github.com/alanshaw/php-http-digest-auth/blob/master/HTTPDigestAuth.php
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.components
 */
class HttpDigestAuthenticationData
{
	/**
	 * @var string
	 */
	public $username;
	/**
	 * @var string
	 */
	public $nonce;
	/**
	 * @var string
	 */
	public $nc;
	/**
	 * @var string
	 */
	public $cnonce;
	/**
	 * @var string
	 */
	public $qop;
	/**
	 * @var string
	 */
	public $uri;
	/**
	 * @var string
	 */
	public $response;
	/**
	 * @var string
	 */
	public $opaque;

	/**
	 * Parses the header to create a object with all the authentication data
	 *
	 * @param string $header the authentication header to parse
	 */
	public function __construct($header)
	{
		preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response|opaque)=[\'"]?([^\'",]+)@', $header, $t);

		$data = array_combine($t[1], $t[2]);

		$this->username = $data['username'];
		$this->nonce = $data['nonce'];
		$this->nc = $data['nc'];
		$this->cnonce = $data['cnonce'];
		$this->qop = $data['qop'];
		$this->uri = $data['uri'];
		$this->response = $data['response'];
		$this->opaque = $data['opaque'];
	}
}
?>