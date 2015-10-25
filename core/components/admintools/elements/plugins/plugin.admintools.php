<?php
switch ($modx->event->name) {
    case 'OnManagerPageBeforeRender':
        $path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/').'model/admintools/';
        $AdminTools = $modx->getService('admintools','AdminTools',$path, $scriptProperties);
        if ($AdminTools instanceof AdminTools) {
            $AdminTools->initialize();
        }
        break;
}