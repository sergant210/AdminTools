<?php
/**
 * Get plugins
 */
class adminToolsPluginGetListProcessor extends modObjectGetListProcessor {
    public $objectType = 'admintools_plugin';
    public $classKey = 'modEvent';
//    public $languageTopics = array('admintools:default');
    public $defaultSortField = 'name';
    public $defaultSortDirection = 'ASC';
    public $permission = 'edit_plugin';

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                'name:LIKE' => "%{$query}%",
            ));
        }

        return $c;
    }
}
return 'adminToolsPluginGetListProcessor';