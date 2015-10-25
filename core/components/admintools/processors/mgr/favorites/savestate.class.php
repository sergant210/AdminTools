<?php

/**
 * Save the state of Favorite Elements
 */
class atFavoritesSaveStateProcessor extends modProcessor {
	public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
//	public $classKey = '';
	//public $permission = '';

    public function process()
    {
        $state = $this->getProperty('state') == 'true' ? true : false;
        $type = $this->getProperty('type');
        $_SESSION['favoriteElements']['states'][$type] = $state;

        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheElementKey = 'states';
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/favorite_elements',
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $this->modx->cacheManager->set($cacheElementKey,  $_SESSION['favoriteElements']['states'], 0, $cacheOptions);
        @session_write_close();
        return $this->success('');
    }
}

return 'atFavoritesSaveStateProcessor';