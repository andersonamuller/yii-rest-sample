<?php
class User
{
	const SECRET_KEY = 'F$g5K&M9';

	private $username;
	private $password;

	public function __construct()
	{
		if ($this->isAuthenticated()) {
			$this->username = $this->decrypt($_SESSION['username']);
			$this->password = $this->decrypt($_SESSION['password']);
		}
	}

	public function login()
	{
		$_SESSION['username'] = $this->encrypt($this->username);
		$_SESSION['password'] = $this->encrypt($this->password);
	}

	public function logout()
	{
		unset($_SESSION['username'], $_SESSION['password']);
	}

	public function isAuthenticated()
	{
		return (isset($_SESSION['username']) && isset($_SESSION['password']));
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($password)
	{
		$this->password = $password;
	}

	private function encrypt($str, $key = self::SECRET_KEY)
	{
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
	}

	private function decrypt($str, $key = self::SECRET_KEY)
	{
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		return substr($str, 0, strlen($str) - $pad);
	}
}
