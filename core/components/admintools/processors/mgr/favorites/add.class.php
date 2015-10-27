<?php

/**
 * Add element to favorite list
 */
class atFavoritesAddElementProcessor extends modProcessor {
	public $objectType = 'admintools';
//	public $classKey = '';
	public $languageTopics = array('admintools:default');
	//public $permission = 'view';


	/**
	 * @return mixed
	 */
	public function process() {
        $id = (int) $this->getProperty('id');
        $type = $this->getProperty('type').'s';
        if (!in_array($id,$_SESSION['admintools']['favoriteElements']['elements'][$type])) $_SESSION['admintools']['favoriteElements']['elements'][$type][] = $id;

        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheElementKey = 'elements';
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/favorite_elements/'.$this->modx->user->id,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $this->modx->cacheManager->set($cacheElementKey,  $_SESSION['admintools']['favoriteElements']['elements'], 0, $cacheOptions);
        @session_write_close();
        return $this->success('',$_SESSION['admintools']['favoriteElements']['elements']);
	}

}

return 'atFavoritesAddElementProcessor';