<?php
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools','AdminTools',$path, $scriptProperties);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $get = $modx->sanitize($_GET, $modx->sanitizePatterns);
    $get = array_map('trim',$get);
    $success = true;
    $message = $modx->lexicon('admintools_link_is_sent');
    if (empty($get['action']) || $get['action'] != 'login') {
        $message = 'Access is denied';
        $success = false;
    } elseif (empty($get['userdata'])) {
        $message =  $modx->lexicon('admintools_enter_username_or_email');
        $success = false;
    }
    if ($success) {
        if ($msg = $AdminTools->sendLoginLink($get)){
            $success = false;
            $message = $msg;
        };
    }
    $response = array('success'=>$success, 'message'=>$message);
    exit($modx->toJSON($response));
} else {
    if ($modx->user->isAuthenticated('mgr')) {
        $url = $modx->getOption('manager_url', null, MODX_MANAGER_URL);
        $url = $modx->getOption('url_scheme', null, MODX_URL_SCHEME) . $modx->getOption('http_host', null, MODX_HTTP_HOST) . rtrim($url, '/');
        $modx->sendRedirect($url);
    }
    $errormsg = '';
    if (isset($_GET['a']) && isset($_GET['hash']) && isset($_GET['id'])) {
        $get = $modx->sanitize($_GET, $modx->sanitizePatterns);
        $data = $AdminTools->getLoginState($get['id']);
        if (!empty($data) && $get['hash'] == $data['hash'] && !empty($data['uid'])) {
            $key = md5($_SERVER['REMOTE_ADDR'] . '/' . $_SERVER['HTTP_USER_AGENT'] . $data['uid']);
            if ($key == $get['id']) {
                $errormsg = $AdminTools->loginUser($data['uid']);
            }
        }
    }
    /** @var array $scriptProperties */
    $assetsUrl = $AdminTools->getOption('assetsUrl');
    $modx->regClientCss($assetsUrl . 'css/mgr/login.css');
    $modx->regClientScript($assetsUrl . 'js/mgr/login.js');
    return $modx->getChunk($tpl,array('errormsg'=>$errormsg));
}