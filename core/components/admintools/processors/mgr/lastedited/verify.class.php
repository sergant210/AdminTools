<?php

/**
 * Verify the existence of element
 */
class lastEditedElementsVerifyProcessor extends modProcessor {
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
        $id = (int) $this->getProperty('id');
        $type = $this->getProperty('type');
        $classKey = 'mod'.ucfirst($type);

        if (!$this->modx->getCount($classKey, $id)) {
            $elements = $this->modx->admintools->getFromCache('element_log', 'elementlog/');
            unset($elements[$type . '-' . $id]);
            $this->modx->admintools->saveToCache($elements, 'element_log', 'elementlog/');
            
            return $this->failure($this->modx->lexicon('admintools_element_nf'));
        }

        return $this->success();
    }
}

return 'lastEditedElementsVerifyProcessor';