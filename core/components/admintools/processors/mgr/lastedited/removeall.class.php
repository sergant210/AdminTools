<?php
/**
 * Clear the last edited list
 */
class lastEditedElementsRemoveAllProcessor extends modProcessor {
    public $objectType = 'admintools';
//	public $classKey = '';
    public $languageTopics = array('admintools:default');
    public $permission = 'remove_led_elements';
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
        $elements = array();
        $this->modx->admintools->saveToCache($elements, 'element_log', 'elementlog/');
        return $this->success();
    }
}
return 'lastEditedElementsRemoveAllProcessor';