<?php
/**
 * RestWebApplication extends CWebApplication by providing functionalities specific to rest applications.
 *
 * @property User $user The user authorized.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.base
 */
class RestApplication extends CWebApplication
{
	/**
	 * Returns the authorized user
	 *
	 * @param User $user the autorized user
	 * @return void
	 */
	public function setUser($user)
	{
		return $this->user = $user;
	}

}
