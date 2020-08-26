<?php
/** @var AdminTools $AdminTools */
/** @var array $scriptProperties */
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/') . 'services/';
$AdminTools = $modx->getService('admintools', 'AdminTools', $path, $scriptProperties);
$get = array_map('trim', $_GET);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    $success = true;
    $message = $modx->lexicon('admintools_link_is_sent');

    try {
        $AdminTools->sendLoginLink($get);
    } catch (InvalidArgumentException $e) {
        $success = false;
        $message =  $e->getMessage();
    }
    $response = ['success' => $success, 'message' => $message];

    exit($modx->toJSON($response));
}

if ($modx->user->isAuthenticated('mgr')) {
    $modx->sendRedirect($AdminTools->getManagerUrl());
}
$errormsg = '';
if (isset($get['a'], $get['token']) && $get['a'] === 'login') {
    $get['token'] = $modx->sanitizeString($get['token']);
    $data = $AdminTools->getLoginState($get['token']);
    if (!empty($data['uid']) && hash_equals($data['key'], $AdminTools->getUserLoginKey())) {
        $errormsg = $AdminTools->loginUser($data['uid'], $get['token']);
    }
}
/** @var array $scriptProperties */
$assetsUrl = $AdminTools->getOption('assetsUrl');
$modx->regClientCss($assetsUrl . 'css/mgr/login.css');
$modx->regClientScript($assetsUrl . 'js/mgr/login.js');
return $modx->getChunk($tpl, ['errormsg' => $errormsg]);