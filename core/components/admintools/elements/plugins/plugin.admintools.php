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
        /*
        case 'OnChunkFormSave':
            $elementType = 'chunk';
            break;
        case 'OnSnipFormSave':
            $elementType = 'snippet';
            break;
        case 'OnTempFormSave':
            $elementType = 'template';
            break;
        case 'OnPluginFormSave':
            $elementType = 'plugin';
            break;
        case 'OnTVFormSave':
            $elementType = 'tv';
            break;
        */
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
            if ($modx->user->id == 0 && $modx->getOption('admintools_email_authorization', null, false)) {
                $id = (int) $modx->getOption('admintools_loginform_resource');
                if (!empty($id) && $modx->getCount('modResource', $id)) {
                    $url = $modx->makeUrl($id,'','','full');
                    $modx->setOption('manager_login_url_alternate', $url);
                }
            }
            break;
        case 'OnManagerAuthentication':
            if ($modx->getOption('admintools_email_authorization', null, false)) {
                $modx->event->output(array('true'));
            }
            break;
    }
    /*
    if (!empty($elementType)) {
        $AdminTools->updateElementLog($object->toArray());
    }
    */
}