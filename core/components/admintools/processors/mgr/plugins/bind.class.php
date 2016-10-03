<?php

/**
 * Bind plugin from event
 */
class adminToolsEventPluginBindProcessor extends modObjectProcessor {
    public $objectType = 'admintools_plugin';
    public $classKey = 'modPluginEvent';
//    public $languageTopics = array('admintools:default');
    public $permission = 'edit_plugin';


	/**
	 * @return array|string
	 */
	public function process() {
		$id = (int) $this->getProperty('pluginid');
		$event = $this->getProperty('event');
		$priority = $this->getProperty('priority');
		if (empty($id)) {
			return $this->failure($this->modx->lexicon('admintools_error'));
		}
        /** @var modPluginEvent $object */
        if ($this->modx->getCount($this->classKey, array('pluginid' => $id, 'event' => $event)) == 0) {
            $object = $this->modx->newObject($this->classKey);
            $object->fromArray(array('pluginid' => $id, 'event' => $event, 'priority' => $priority), '', true);

            if ($object->save()) {
                $this->modx->eventMap[$event][$id] = $id;
            }
        }

		return $this->success();
	}
}

return 'adminToolsEventPluginBindProcessor';