<?php
switch ($modx->event->name) {
    case 'OnManagerPageBeforeRender':
        $path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
        $AdminTools = $modx->getService('admintools','AdminTools',$path, $scriptProperties);
        if ($AdminTools instanceof AdminTools) {
            $AdminTools->initialize();
        }
        break;
    case 'OnChunkFormSave':
        $elementType = 'chunk';
    case 'OnSnipFormSave':
        if (!isset($elementType)) $elementType = 'snippet';
    case 'OnTempFormSave':
        if (!isset($elementType)) $elementType = 'templatet';
    case 'OnPluginFormSave':
        if (!isset($elementType)) $elementType = 'plugin';
        $path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
        /** @var AdminTools $AdminTools */
        $AdminTools = $modx->getService('admintools','AdminTools',$path, $scriptProperties);
        if ($AdminTools instanceof AdminTools) {
            $AdminTools->updateElementLog($object->toArray());
        }
        break;
}