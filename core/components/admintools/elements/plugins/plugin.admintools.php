<?php
if (!$modx->user->id) return;
/** @var array $scriptProperties */
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools','AdminTools',$path);
$elementType = null;
if ($AdminTools instanceof AdminTools) {
    switch ($modx->event->name) {
        case 'OnManagerPageBeforeRender':
            $AdminTools->initialize();
            //$modx->controller->addHtml('<style type="text/css">#modx-navbar li.active  ul.modx-subnav {opacity: 1 !important;visibility: visible !important;} </style>');
            break;
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
    }
    if (!empty($elementType)) {
        $AdminTools->updateElementLog($object->toArray());
    }
}