<?php
/**
 * Save the state of Favorite Elements
 */
class atFavoritesSaveStateProcessor extends modProcessor {
    public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
//	public $classKey = '';
    //public $permission = '';
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
    public function process() {
        $state = $this->getProperty('state') == 'true' ? true : false;
        $type = $this->getProperty('type');
        $_SESSION['admintools']['favoriteElements']['states'][$type] = $state;
        //$this->modx->admintools->saveToCache($_SESSION['admintools']['favoriteElements']['states'], 'states', 'favorite_elements/' . $this->modx->user->id);
        $this->modx->admintools->saveToProfile($_SESSION['admintools']['favoriteElements']['states'],'adminToolsStates');

        @session_write_close();
        return $this->success('', $_SESSION['admintools']['favoriteElements']['states']);
    }
}
return 'atFavoritesSaveStateProcessor';