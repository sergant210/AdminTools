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
		'area' => 'admintools_permissions',
	),
    'remember_system_settings' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'admintools_main',
	),
    'enable_elements_log' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'admintools_main',
	),
    'enable_favorite_elements' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'admintools_main',
	),
    'clear_only_resource_cache' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
		'area' => 'admintools_main',
	),
    'hide_component_description' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
		'area' => 'admintools_main',
	),
    'email_authorization' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'admintools_authorization',
    ),
    'authorization_ttl' => array(
        'xtype' => 'numberfield',
        'value' => '600',
        'area' => 'admintools_authorization',
    ),
    'loginform_resource' => array(
        'xtype' => 'numberfield',
        'value' => '',
        'area' => 'admintools_authorization',
    ),
    'enable_notes' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'admintools_main',
    ),
    'template_resource_relationship' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'admintools_main',
    ),
    'animate_menu' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'admintools_main',
    ),
    'alternative_permissions' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'admintools_permissions',
    ),
    'plugins_events' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'admintools_main',
    ),
    'theme' => array(
        'xtype' => 'textfield',
        'value' => 'default',
        'area' => 'admintools_main',
    ),
    'modx_tree_position' => array(
        'xtype' => 'textfield',
        'value' => 'left',
        'area' => 'admintools_main',
    ),
    'admintools_custom_css' => array(
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'admintools_main',
    ),
    'admintools_custom_js' => array(
        'xtype' => 'textfield',
        'value' => '',
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
