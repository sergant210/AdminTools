<?php

/**
 * Get last edited elements list
 */
class LastEditedElementGetListProcessor extends modProcessor {
	public $objectType = 'admintools';
//	public $classKey = '';
	public $languageTopics = array('admintools:default');
	//public $permission = 'view';

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
        function sortEditedElements ($a, $b) {
            global $sort;
            global $dir;

            if ($dir == 'ASC') {
                return strcmp($a[$sort],$b[$sort]);
            } else {
                return strcmp($b[$sort],$a[$sort]);
            }
        }

        $sort = $this->getProperty('sort', '');

        $elements = $this->modx->admintools->getFromCache('element_log', 'elementlog/');

        if ($sort) {
            uasort($elements, 'sortEditedElements');
        }
        
        $data = array();
        if (is_array($elements)) {
            foreach ($elements as $key=>$element) {
                $element['key'] = $key;
                $element['actions'] = array();
                // Open
                $element['actions'][] = array(
                    'cls' => '',
                    'icon' => 'icon icon-link',
                    'title' => $this->modx->lexicon('admintools_open'),
                    //'multiple' => $this->modx->lexicon('fullcalendar_items_update'),
                    'action' => 'openElement',
                    'button' => true,
                    'menu' => true,
                );
                // Remove
                $element['actions'][] = array(
                    'cls' => '',
                    'icon' => 'icon icon-trash-o action-red',
                    'title' => $this->modx->lexicon('admintools_item_remove'),
                    'multiple' => $this->modx->lexicon('admintools_items_remove'),
                    'action' => 'removeItem',
                    'button' => true,
                    'menu' => true,
                );

                $data[] = $element;
            }
        }

        return $this->outputArray($data,count($data));
	}

    public function outputArray(array $array,$count = false) {
        if ($count === false) { $count = count($array); }
        return '{"success":true,"total":"'.$count.'","results":'.$this->modx->toJSON($array).'}';
    }
}

return 'LastEditedElementGetListProcessor';