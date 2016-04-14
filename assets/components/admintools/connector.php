<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
}
else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools', 'AdminTools', $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/') . 'model/admintools/');
$modx->lexicon->load('admintools:default');

// handle request
$corePath = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/');
$path = $modx->getOption('processorsPath', $AdminTools->getOptions(), $corePath . 'processors/');
/** @noinspection PhpMethodParametersCountMismatchInspection */
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));