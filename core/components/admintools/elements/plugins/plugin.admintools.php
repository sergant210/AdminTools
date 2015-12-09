<?php
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools','AdminTools',$path, $scriptProperties);
$elementType = null;

if ($AdminTools instanceof AdminTools) {
    switch ($modx->event->name) {
        case 'OnManagerPageBeforeRender':
            $AdminTools->initialize();

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
    }

    if (!empty($elementType)) {
        $AdminTools->updateElementLog($object->toArray());
    }
}