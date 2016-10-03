<?php

/**
 * Unbind plugin from event
 */
class adminToolsEventPluginRemoveProcessor extends modObjectProcessor {
    public $objectType = 'admintools_plugin';
    public $classKey = 'modPluginEvent';
//    public $languageTopics = array('admintools:default');
    public $permission = 'edit_plugin';


	/**
	 * @return array|string
	 */
	public function process() {
		$id = (int) $this->getProperty('id');
		$event = $this->getProperty('event');
		if (empty($id)) {
			return $this->failure($this->modx->lexicon('admintools_error'));
		}

        if (!$object = $this->modx->getObject($this->classKey, array('pluginid' => $id, 'event' => $event))) {
            return $this->failure($this->modx->lexicon('admintools_err_object_nf'));
        }

        if ($object->remove()) {
            unset($this->modx->eventMap[$event][$id]);
        }

		return $this->success();
	}
}

return 'adminToolsEventPluginRemoveProcessor';