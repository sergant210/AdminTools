<?php
/**
 * Remove element from the last edited list
 * @deprecated Not used
 */
class adminToolsNotesRemoveProcessor extends modProcessor {
    public $objectType = 'admintools_notes';
    public $classKey = 'adminNotes';
//    public $languageTopics = array('admintools:default');
//    public $permission = 'remove_notes';

    /**
     * @return array|string
     */
    public function process() {
        /*if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }*/

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('admintools_notes_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var adminNotes $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('admintools_notes_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }
}
return 'adminToolsNotesRemoveProcessor';