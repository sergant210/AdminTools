<?php
/**
 * Add element to favorite list
 */
class atFavoritesAddElementProcessor extends modProcessor {
    public $objectType = 'admintools';
//  public $classKey = '';
    public $languageTopics = array('admintools:default');
    //public $permission = 'view';
    /**
     * @return boolean
     */
    public function initialize() {
        if (is_null($this->modx->admintools)) {
            $path = $this->modx->getOption('admintools_core_path', null, $this->modx->getOption('core_path') . 'components/admintools/') . 'services/';
            $this->modx->getService('admintools', 'AdminTools', $path, []);
        }
        return ($this->modx->admintools instanceof AdminTools);
    }
    /**
     * @return mixed
     */
    public function process() {
        $id = (int) $this->getProperty('id');
        $type = $this->getProperty('type') . 's';
        if (!in_array($id, $_SESSION['admintools']['favoriteElements']['elements'][$type])) {
            $_SESSION['admintools']['favoriteElements']['elements'][$type][] = $id;
        }
//        $this->modx->admintools->saveToCache($_SESSION['admintools']['favoriteElements']['elements'], 'elements', 'favorite_elements/' . $this->modx->user->id);
        $this->modx->admintools->saveToProfile($_SESSION['admintools']['favoriteElements']['elements'],'adminToolsElements');
        @session_write_close();
        return $this->success('', $_SESSION['admintools']['favoriteElements']['elements']);
    }
}
return 'atFavoritesAddElementProcessor';