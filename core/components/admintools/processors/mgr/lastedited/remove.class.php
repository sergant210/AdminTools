<?php

/**
 * Remove element from the last edited list
 */
class lastEditedElemntsRemoveProcessor extends modProcessor {
    public $objectType = 'admintools';
//	public $classKey = '';
    public $languageTopics = array('admintools:default');
    public $permission = 'remove_led_elements';


    /**
     * @return mixed
     */
    public function process() {
        $ids = $this->getProperty('ids');
        $ids = $this->modx->fromJSON($ids);
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/elementlog/',
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $elements = $this->modx->cacheManager->get('element_log', $cacheOptions);

        foreach ($ids as $id) {
            unset($elements[$id]);
        }
        $this->modx->cacheManager->set('element_log',  $elements, 0, $cacheOptions);

        return $this->success();
    }
}

return 'lastEditedElemntsRemoveProcessor';