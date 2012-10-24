<?php
/* @var $this Controller */
if (strpos(Yii::app()->request->acceptTypes, 'application/json') !== false) :
	echo CJSON::encode($data);
else :
	Yii::app()->clientScript->registerCoreScript('jquery');
?>
<!DOCTYPE html>
<html lang="<?php echo substr(Yii::app()->getLanguage(), 0, 2); ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo Yii::app()->charset; ?>" />
<title><?php echo CHtml::encode(Yii::app()->name); ?></title>
</head>
<body>
	<pre id="json"></pre>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery('#json').html(JSON.stringify(<?php echo CJSON::encode($data); ?>, null, "\t"));
		});
	</script>
</body>
</html>
<?php
endif;
?>