<?php

/**
 * Save the state of system settings
 */
class atSysSettingsSaveStateProcessor extends modProcessor {
	public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
//	public $classKey = '';
	//public $permission = '';

    public function process()
    {
        $namespace = $this->getProperty('namespace','');
        $area = $this->getProperty('area','');
        $_SESSION['admintools']['systemSettings'] = array('namespace'=>$namespace,'area'=>$area);

        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheElementKey = 'system_settings';
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/'.$this->modx->user->id,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $this->modx->cacheManager->set($cacheElementKey,  $_SESSION['admintools']['systemSettings'], 0, $cacheOptions);
        @session_write_close();
        return $this->success('',$_SESSION['admintools']['systemSettings']);
    }
}

return 'atSysSettingsSaveStateProcessor';