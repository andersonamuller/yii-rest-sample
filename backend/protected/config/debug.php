<?php
$config = array();

if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false)) {
	$config[] = array(
		'class'     => 'ext.yii-debug-toolbar.YiiDebugToolbarRoute',
		'ipFilters' => YII_DEBUG ? array(
			'127.0.0.1',
			'::1'
		) : array(
			'999.999.999.999',
			'::999'
		)
	);
}

return $config;
