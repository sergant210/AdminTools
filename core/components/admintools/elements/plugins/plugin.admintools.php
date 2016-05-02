<?php
/** @var array $scriptProperties */
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools','AdminTools',$path);
$elementType = null;
if ($AdminTools instanceof AdminTools) {
    switch ($modx->event->name) {
        case 'OnManagerPageBeforeRender':
            if ($modx->user->id) $AdminTools->initialize();
            //$modx->controller->addHtml('<style type="text/css">#modx-navbar li.active  ul.modx-subnav {opacity: 1 !important;visibility: visible !important;} </style>');
            break;
        case 'OnDocFormSave':
            if ($modx->getOption('admintools_clear_only resource_cache',null,false)) {
                if ($modx->event->params['mode'] != 'upd') {
                    return;
                }
                if ($resource->get('syncsite')) {
                    $AdminTools->clearResourceCache($resource);
                }
            }
            break;
        case 'OnManagerPageInit':
            if (!$modx->user->isAuthenticated('mgr') && $modx->getOption('admintools_email_authorization', null, false)) {
                $id = (int) $modx->getOption('admintools_loginform_resource');
                if (!empty($id) && $modx->getCount('modResource', array('id'=>$id, 'published'=>1, 'deleted'=>0))) {
                    $url = $modx->makeUrl($id,'','','full');
                    $modx->setOption('manager_login_url_alternate', $url);
                }
            }
            break;
        case 'OnManagerAuthentication':
            if ($modx->getOption('admintools_user_can_login', null, false)) {
                $modx->setOption('admintools_user_can_login', false);
                $modx->event->output(array('true'));
            }
            break;
        case 'OnLoadWebDocument':
            if ($modx->user->isAuthenticated($modx->context->get('key')) && (!$modx->user->active || $modx->user->Profile->blocked)) {
                $modx->runProcessor('security/logout');
            }
            break;
        case 'OnTempFormPrerender':
            $modx->controller->addLastJavascript($AdminTools->getOption('jsUrl') . 'mgr/templates.js');
            break;
        case 'OnDocFormPrerender':
            $_html = '<script>
	Ext.onReady(function() {
        setTimeout(function(){
            var tmpl = Ext.getCmp("modx-resource-template");
            tmpl.label.update(" <a href=\"?a=element/template/update&id=" + tmpl.getValue() + "\">" + tmpl.label.dom.innerText + "</a>");
        }, 200);
    });
</script>';
            $modx->controller->addHtml($_html);
            break;
    }
}