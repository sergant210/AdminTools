<?php
/**
 * Removean element from the favorite list
 */
class atFavoritesRemoveElementProcessor extends modProcessor {
    public $objectType = 'admintools';
//	public $classKey = '';
    public $languageTopics = array('admintools:default');
    //public $permission = 'view';
    /**
     * @return boolean
     */
    public function initialize() {
        $path = $this->modx->getOption('admintools_core_path', null, $this->modx->getOption('core_path') . 'components/admintools/') . 'model/admintools/';
        $this->modx->getService('admintools', 'AdminTools', $path, array());
        return ($this->modx->admintools instanceof AdminTools);
    }
    /**
     * @return mixed
     */
    public function process() {
        $id = (int) $this->getProperty('id');
        $type = $this->getProperty('type') . 's';
        $_SESSION['admintools']['favoriteElements']['elements'][$type] = array_values(array_diff($_SESSION['admintools']['favoriteElements']['elements'][$type],array($id)));
//        $this->modx->admintools->saveToCache($_SESSION['admintools']['favoriteElements']['elements'], 'elements', 'favorite_elements/' . $this->modx->user->id);
        $this->modx->admintools->saveToProfile($_SESSION['admintools']['favoriteElements']['elements'],'adminToolsElements');
        @session_write_close();

        return $this->success('', $_SESSION['admintools']['favoriteElements']['elements']);
    }
}
return 'atFavoritesRemoveElementProcessor';