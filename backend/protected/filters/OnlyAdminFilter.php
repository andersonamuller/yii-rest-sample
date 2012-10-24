<?php
/**
 * OnlyAdminFilter class file.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.filters
 */
class OnlyAdminFilter extends CFilter
{
	/**
	 * Performs the pre-action filtering.
	 *
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action
	 * should be executed.
	 */
	protected function preFilter($filterChain)
	{
		$user = User::model()->findByUsername(Yii::app()->httpAuthentication->username);
		if (($user instanceof User) && $user->isAdmin()) {
			return true;
		}

		return false;
	}
}
