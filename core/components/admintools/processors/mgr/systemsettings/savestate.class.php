<?php
/**
 * Save the state of system settings
 */
class atSysSettingsSaveStateProcessor extends modProcessor {
    public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
    // public $classKey = '';
    // public $permission = '';
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
        $namespace = $this->getProperty('namespace', '');
        $area = $this->getProperty('area', '');
        $_SESSION['admintools']['systemSettings'] = array('namespace' => $namespace, 'area' => $area);
//        $this->modx->admintools->saveToCache($_SESSION['admintools']['systemSettings'], 'systemSettings', 'favorite_elements/' . $this->modx->user->id);
        $this->modx->admintools->saveToProfile($_SESSION['admintools']['systemSettings'],'systemSettings');
        @session_write_close();
        return $this->success('', $_SESSION['admintools']['systemSettings']);
    }
}
return 'atSysSettingsSaveStateProcessor';