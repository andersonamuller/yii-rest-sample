<?php
$config = array(
	'emulatePrepare'        => true,
	'charset'               => 'utf8',
	'nullConversion'        => PDO::NULL_EMPTY_STRING,
	'schemaCachingDuration' => 3600,
	'attributes'            => array(
		PDO::MYSQL_ATTR_FOUND_ROWS => true
	),
	'initSQLs'              => array(
		'SET NAMES utf8'
	)
);

if (isset($_SERVER['PLATFORM']) && $_SERVER['PLATFORM'] == 'pagodabox') {
	$config = CMap::mergeArray($config, array(
		'connectionString' => 'mysql:host=' . $_SERVER['DB1_HOST'] . ';mysql:port=' . $_SERVER['DB1_PORT'] . ';dbname=' . $_SERVER['DB1_NAME'],
		'username'         => $_SERVER['DB1_USER'],
		'password'         => $_SERVER['DB1_PASS']
	));
} else {
	$config = CMap::mergeArray($config, array(
		'connectionString' => 'mysql:host=localhost;dbname=yii-rest-sample',
		'username'         => 'root',
		'password'         => ''
	));
}

if (isset($_SERVER['ENVIRONMENT']) && $_SERVER['ENVIRONMENT'] == 'development') {
	$config = CMap::mergeArray($config, array(
		'enableProfiling'       => true,
		'enableParamLogging'    => true,
		'schemaCachingDuration' => 3600,
		//'initSQLs'              => array(
		//	'SET FOREIGN_KEY_CHECKS = 0'
		//)
	));
}

return $config;
