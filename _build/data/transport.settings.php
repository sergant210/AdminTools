<?php

$settings = array();

$tmp = array(
	'favorites_icon' => array(
		'xtype' => 'textfield',
		'value' => '',
		'area' => 'admintools_main',
	),
    'check_elements_permissions' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'admintools_main',
	),
    'remember_system_settings' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'admintools_main',
	),
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'admintools_' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
