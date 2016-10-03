<?php

/**
 * Update the plugin
 */
class adminToolsPriorityUpdateProcessor extends modObjectProcessor {
    public $objectType = 'admintools_plugin';
    public $classKey = 'modPluginEvent';
//	public $languageTopics = array('admintools');
	public $permission = 'edit_plugin';

    /**
     * @return array|string
     */
    public function process() {
        $priority = (int) $this->getProperty('priority');
        $id = (int) $this->getProperty('id');
        $event = trim($this->getProperty('event'));

        if (!$obj = $this->modx->getObject($this->classKey, array('pluginid' => $id, 'event' => $event))) {
            return $this->failure($this->modx->lexicon('admintools_err_object_nf'));
        }
        $obj->set('priority', $priority);
        $obj->save();

        return $this->success();
    }
}

return 'adminToolsPriorityUpdateProcessor';
