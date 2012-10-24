<?php
/**
 * HttpAuthenticationFilter performs authorization checks using http authentication
 *
 * By enabling this filter, controller actions can be limited to a couple of users.
 *
 * To specify the authorized users set the 'users' property of the filter, do the following:
 * <pre>
 * public function filters()
 * {
 *     return array(
 *         array(
 *             'HttpAuthenticationFilter',
 *             'users' => array('admin')
 *         )
 *     );
 * }
 * </pre>
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.filters
 */
class HttpAuthenticationFilter extends CFilter
{
	/**
	 * @var mixed use @ for all authenticated users or an array of authorized usernames
	 */
	public $users = '@';

	/**
	 * Performs the pre-action filtering.
	 *
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action
	 * should be executed.
	 */
	protected function preFilter($filterChain)
	{
		$username = Yii::app()->httpAuthentication->authenticate();

		if ($username !== false) {
			if (is_string($this->users) && $this->users === '@') {
				return true;
			} elseif (is_array($this->users) && in_array($username, $this->users)) {
				return true;
			}
		}

		return false;
	}
}
