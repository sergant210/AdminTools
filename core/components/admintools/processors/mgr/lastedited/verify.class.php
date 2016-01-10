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
     * @return mixed
     */
    public function process() {
        $id = (int) $this->getProperty('id');
        $classKey = $this->getProperty('classKey');
//        $classKey = 'mod'.ucfirst($type);
        if (!$this->modx->getCount($classKey, $id)) {
            return $this->failure($this->modx->lexicon('admintools_element_nf'));
        }
        return $this->success();
    }
}
return 'lastEditedElementsVerifyProcessor';