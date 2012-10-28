<?php
Yii::setPathOfAlias('packages', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'packages');

$config = array(
	'class' => 'packages.redis.ARedisConnection',
	'port'  => 6379
);

if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'pagodabox') {
	$config = CMap::mergeArray($config, array(
		'hostname' => 'tunnel.pagodabox.com'
	));
} else {
	$config = CMap::mergeArray($config, array(
		'hostname' => 'localhost',
	));
}

return $config;
