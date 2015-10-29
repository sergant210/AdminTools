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
        $type = $this->getProperty('type');
        $classKey = 'mod'.ucfirst($type);
        if (!$this->modx->getCount($classKey,$id)) {
            $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
            $cacheOptions = array(
                xPDO::OPT_CACHE_KEY => 'admintools/elementlog/',
                xPDO::OPT_CACHE_HANDLER => $cacheHandler,
            );
            $elements = $this->modx->cacheManager->get('element_log', $cacheOptions);
            unset($elements[$type.'-'.$id]);
            $this->modx->cacheManager->set('element_log',  $elements, 0, $cacheOptions);
            return $this->failure($this->modx->lexicon('admintools_element_nf'));
        }

        return $this->success();
    }
}

return 'lastEditedElementsVerifyProcessor';