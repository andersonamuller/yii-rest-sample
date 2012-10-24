<?php
/**
 * This is the bootstrap file for test application.
 * This file will not be accessable in production.
 */
if (isset($_SERVER['ENVIRONMENT']) && $_SERVER['ENVIRONMENT'] == 'development') {
	$yii = dirname(__FILE__) . '/protected/vendors/yii/framework/yii.php';
	$config = dirname(__FILE__) . '/protected/config/test.php';

	require_once($yii);
	Yii::createWebApplication($config)->run();
}
