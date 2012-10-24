<?php
/**
 * HttpDigestAuthentication
 *
 * This class provides methods to extract authorization information from the headers of a request,
 * and based on the configuration of this class, authenticate or send unauthorized and bad request responses.
 *
 * Inspired by
 * - https://github.com/alanshaw/php-http-digest-auth/blob/master/HTTPDigestAuth.php
 * - http://www.peej.co.uk/files/httpdigest.phps
 * - http://en.wikipedia.org/wiki/Http_digest_authentication
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.components
 */
class HttpDigestAuthentication extends CApplicationComponent
{
	const CACHE_KEY_PREFIX = 'http-authentication';
	/**
	 * @var string the header that contains the authorization information
	 */
	public $authorizationHeader = 'HTTP_AUTHORIZATION';
	/**
	 * @var string the digest opaque value (any string will do, never sent in plain text over the wire).
	 */
	public $opaque = 'opaque';
	/**
	 * @var string the authentication realm name.
	 */
	public $realm = 'Realm';
	/**
	 * @var string the private key that will be used to scramble the nonce.
	 */
	public $privateKey = 'privatekey';
	/**
	 * @var integer the life of the nonce value in seconds
	 */
	public $nonceLife = 300;
	/**
	 * @var integer the allowed difference between the nonce count sent and the one registered.
	 * This is useful because it's difficult to maintain a sequential counter. We just want to avoid
	 * big jumps in the count as it may suggest a brute force attack
	 */
	public $countDifference = 5;
	/**
	 * @var string if passwords are stored as an a1 hash (username:realm:password) rather than plain text.
	 */
	public $passwordHashed = true;
	/**
	 * @var array the list of allowed users
	 * The following is an example
	 * <pre>
	 * array(
	 *     'username' => 'password'
	 * )
	 * </pre>
	 * or if {@link passwordHashed} is true
	 * <pre>
	 * array(
	 *     'username' => 'hash' //md5('username' . ':' . $this->realm . ':' . 'password')
	 * )
	 * </pre>
	 */
	public $users = array();
	/**
	 * @var string if the authentication is successful keep the current username to be used across the application
	 */
	private $_username = null;

	/**
	 * Get the authorized username
	 *
	 * @return string the username
	 */
	public function getUsername()
	{
		return $this->_username;
	}

	/**
	 * @return mixed the authenticated username on success, false otherwise.
	 */
	public function authenticate()
	{
		if (empty($_SERVER[$this->authorizationHeader])) {
			$this->sendUnauthorized(false, '{remoteAddress} sent a request without an authorization header.', array(
				'{remoteAddress}' => $_SERVER['REMOTE_ADDR']
			));
			return false;
		}

		$authenticationData = new HttpDigestAuthenticationData($_SERVER[$this->authorizationHeader]);

		// Check for stale nonce
		// 		if ($this->isStaleNonce($authenticationData->nonce)) {
		// 		    $this->sendUnauthorized(true, '{remoteAddress} sent a request with a stale nonce "{nonce}".', array(
		// 		        '{remoteAddress}' => $_SERVER['REMOTE_ADDR'],
		// 		        '{nonce}'         => $authenticationData->nonce
		// 		    ));
		// 		    return false;
		// 		}

		// Check for correct nonce count
		// 		$nonceCount = hexdec($authenticationData->nc);
		// 		$expectedNonceCount = $this->getNonceCount($authenticationData->nonce) + 1;

		// 		if (abs($nonceCount - $expectedNonceCount) > $this->countDifference) {
		// 			$this->sendBadRequest('{remoteAddress} sent a request with an invalid nonce "{nonce}" count of {nonceCount} expected {expectedNonceCount}.', array(
		// 				'{remoteAddress}'      => $_SERVER['REMOTE_ADDR'],
		// 				'{nonce}'              => $authenticationData->nonce,
		// 				'{nonceCount}'         => $nonceCount,
		// 				'{expectedNonceCount}' => $expectedNonceCount
		// 			));
		// 			return false;
		// 		}

		// 		$this->incrementNonceCount($authenticationData->nonce);

		$requestURI = $_SERVER['REQUEST_URI'];
		if (strpos($requestURI, '?') !== false) {
			// IE which does not pass querystring in URI element of Digest string or in response hash
			$requestURI = substr($requestURI, 0, strlen($authenticationData->uri));
		}
		// Check request URI is the same as the auth digest uri
		if ($authenticationData->uri != $requestURI) {
			$this->sendBadRequest('{remoteAddress} sent a request with an invalid uri "{uri}".', array(
				'{remoteAddress}' => $_SERVER['REMOTE_ADDR'],
				'{uri}'           => $authenticationData->uri
			));
			return false;
		}

		// Check if opaque is correct
		if ($authenticationData->opaque != $this->opaque) {
			$this->sendBadRequest('{remoteAddress} sent a request with an invalid opaque "{opaque}".', array(
				'{remoteAddress}' => $_SERVER['REMOTE_ADDR'],
				'{opaque}'        => $authenticationData->opaque
			));
			return false;
		}

		// Check if user exists
		if (!isset($this->users[$authenticationData->username])) {
			$this->sendUnauthorized(false, '{remoteAddress} sent a request with an invalid user "{username}".', array(
				'{remoteAddress}' => $_SERVER['REMOTE_ADDR'],
				'{username}'      => $authenticationData->username
			));
			return false;
		}

		// Generate A1 hash
		$ha1 = $this->getHA1ForUser($authenticationData->username);

		// Generate A2 hash
		if ($authenticationData->qop == 'auth-int') {
			$a2 = $_SERVER['REQUEST_METHOD'] . ':' . stripslashes($requestURI) . ':' . file_get_contents('php://input');
			$ha2 = md5($a2);
		} else {
			$a2 = $_SERVER['REQUEST_METHOD'] . ':' . stripslashes($requestURI);
			$ha2 = md5($a2);
		}

		// Generate the expected response
		if ($authenticationData->qop == 'auth' || $authenticationData->qop == 'auth-int') {
			$expectedResponse = md5($ha1 . ':' . $authenticationData->nonce . ':' . $authenticationData->nc . ':' . $authenticationData->cnonce . ':' . $authenticationData->qop . ':' . $ha2);
		} else {
			$expectedResponse = md5($expectedResponse = $ha1 . ':' . $authenticationData->nonce . ':' . $ha2);
		}

		// Check request contained the expected response
		if ($authenticationData->response != $expectedResponse) {
			$this->sendUnauthorized(false, '{remoteAddress} sent a request with an invalid response "{response}".', array(
				'{remoteAddress}' => $_SERVER['REMOTE_ADDR'],
				'{response}'      => $authenticationData->response
			));
			return false;
		}

		return $this->_username = $authenticationData->username;
	}

	/**
	 * Generates the nonce that is sent when requesting authentication.
	 *
	 * @return string the new nonce
	 */
	protected function getNonce()
	{
		$time = ceil(time() / $this->nonceLife) * $this->nonceLife;
		$nonce = md5(date('Y-m-d H:i', $time) . ':' . $_SERVER['REMOTE_ADDR'] . ':' . $this->privateKey);
		return $nonce;
	}

	/**
	 * Returns whether or not this nonce has expired. Should return true for
	 * non existent nonce.
	 *
	 * @param string $nonce the nonce to check
	 * @return Boolean
	 */
	protected function isStaleNonce($nonce)
	{
		$created = Yii::app()->cache->get(self::CACHE_KEY_PREFIX . '.' . $nonce . '.created');
		if ($created === false) {
			return true;
		} elseif ($created) {

		}

		return false;
	}

	/**
	 * Gets the current request count for a particular nonce
	 *
	 * @param string $nonce the nonce to get the count of
	 * @return integer the current nonce count
	 */
	protected function getNonceCount($nonce)
	{
		$count = Yii::app()->cache->get(self::CACHE_KEY_PREFIX . '.' . $nonce . '.count');
		if ($count === false) {
			$count = 0;
		}

		return $count;
	}

	/**
	 * Increments the nonce count by 1
	 *
	 * @param string $nonce the nonce to increment
	 * @return void
	 */
	protected function incrementNonceCount($nonce)
	{
		$id = self::CACHE_KEY_PREFIX . '.' . $nonce . '.count';
		$count = Yii::app()->cache->get($id);
		if ($count === false) {
			$count = 0;
		}

		Yii::app()->cache->set($id, $count + 1);
	}

	/**
	 * Returns the A1 hash for the specified user.
	 * <pre>
	 * return md5('username:realm:password')
	 * </pre>
	 *
	 * @param string $username the username to generate the ha1
	 * @return string
	 */
	protected function getHA1ForUser($username)
	{
		$password = $this->users[$username];
		if ($this->passwordHashed) {
			$a1 = $password;
		} else {
			$a1 = md5($username . ':' . $this->realm . ':' . $password);
		}

		return $a1;
	}

	/**
	 * Send the unauthorized headers to the client and log the reason
	 *
	 * @param boolean $stale if the nonce is stale
	 * @param string $reason the reason why it was not authorized
	 * @param array $params the params for the reason message
	 * @return void
	 */
	private function sendUnauthorized($stale, $reason, $params = array())
	{
		Yii::log(Yii::t('ext.http-authentication', $reason, $params), CLogger::LEVEL_INFO, 'http-authentication');

		header('HTTP/1.1 401 Unauthorized');
		$authHeader = 'WWW-Authenticate: Digest realm="' . $this->realm . '",qop="auth",algorithm="MD5",nonce="' . $this->getNonce() . '",opaque="' . $this->opaque . '"';

		if ($stale) {
			$authHeader .= ',stale=TRUE';
		}

		header($authHeader);
		exit();
	}

	/**
	 * Send the bad request headers to the client and log the reason
	 *
	 * @param string $reason the reason why it was not authorized
	 * @param array $params the params for the reason message
	 * @return void
	 */
	private static function sendBadRequest($reason, $params = array())
	{
		Yii::log(Yii::t('ext.http-authentication', $reason, $params), CLogger::LEVEL_INFO, 'http-authentication');
		header('HTTP/1.1 400 Bad Request');
		exit();
	}
}
?>