<?php
/**
 * Get all resources of the specified template
 */
class templatesResourcesGetListProcessor extends modObjectGetListProcessor {
    public $objectType = 'modResource';
	public $classKey = 'modResource';
    public $languageTopics = array('admintools:default');
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';
    //public $permission = 'view';

    /**
     * @return mixed
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $tid = $this->getProperty('tid');
        if ($tid) {
            $c->where(array(
                'template' => $tid,
            ));
        }

        return $c;
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();
        $array['pagetitle'] = '<a href="index.php?a=resource/update&id='. $array['id'] . '">' . $array['pagetitle'] . '</a>';

        return $array;
    }
}
return 'templatesResourcesGetListProcessor';