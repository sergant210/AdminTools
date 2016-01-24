<?php
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools','AdminTools',$path);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $post = $modx->sanitize($_POST, $modx->sanitizePatterns);
    $post = array_map('trim',$post);
    $success = true;
    $message = $modx->lexicon('admintools_link_is_sent');
    if (empty($post['action']) || $post['action'] != 'login') {
        $message = 'Access is denied';
        $success = false;
    } elseif (empty($post['userdata'])) {
        $message =  $modx->lexicon('admintools_enter_username_or_email');
        $success = false;
    }
    if ($success) {
        if ($msg = $AdminTools->sendLoginLink($post)){
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
    $tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.login.form');
    $modx->sjscripts = array();
    $modx->jscripts = array();
    $admintools_assets_path = $modx->getOption('admintools_assets_url', NULL, $modx->getOption('assets_url').'components/admintools/');
    $modx->regClientCss($admintools_assets_path . 'css/mgr/login.css');
    $modx->regClientScript("http://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js");
    $modx->regClientScript($admintools_assets_path . 'js/mgr/login.js');
    return $modx->getChunk($tpl,array('errormsg'=>$errormsg));
}