<?php

/**
 * Clear the last edited list
 */
class lastEditedElemntsRemoveAllProcessor extends modProcessor {
    public $objectType = 'admintools';
//	public $classKey = '';
    public $languageTopics = array('admintools:default');
    public $permission = 'remove_led_elements';


    /**
     * @return mixed
     */
    public function process() {
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/elementlog/',
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $elements = array();
        $this->modx->cacheManager->set('element_log',  $elements, 0, $cacheOptions);

        return $this->success();
    }
}

return 'lastEditedElemntsRemoveAllProcessor';