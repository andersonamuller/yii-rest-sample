<?php
if (isset($_SERVER['ENVIRONMENT']) && $_SERVER['ENVIRONMENT'] == 'production') {
	$yii = dirname(__FILE__) . '/protected/vendors/yii/framework/yiilite.php';
	$config = dirname(__FILE__) . '/protected/config/main.php';

	defined('YII_DEBUG') or define('YII_DEBUG', false);
} else {
	$yii = dirname(__FILE__) . '/protected/vendors/yii/framework/yii.php';
	$config = dirname(__FILE__) . '/protected/config/development.php';

	defined('YII_DEBUG') or define('YII_DEBUG', true);
	defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 0);
}

require_once($yii);
require_once(dirname(__FILE__) . '/protected/base/RestApplication.php');
Yii::createApplication('RestApplication', $config)->run();
