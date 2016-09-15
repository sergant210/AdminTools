<?php
/**
 * Import notes
 */
class adminToolsUploadNotesProcessor extends modObjectProcessor{
    public $objectType = 'admintools_notes';
    public $classKey = 'adminNotes';
    //public $languageTopics = array('admintools');
    //public $permission = 'export_notes';

    public function process() {
        $notes = $this->getProperty('content','');
        if (empty($notes)) return $this->failure($this->modx->lexicon('admintools_err_empty_file'));
        $path = $this->modx->getOption('admintools_core_path', NULL, $this->modx->getOption('core_path') . 'components/admintools/')."elements/tmp/";
        $file = $path.'notes.txt';
        if (!file_exists($file)) {
            return $this->failure($this->modx->lexicon('admintools_err_file_nf'));
        }
        $notes = unserialize($notes);
        if (is_array($notes) && !empty($notes)) {
            foreach ($notes as $note) {
                $obj = $this->modx->newObject('adminNotes');
                $note['createdby'] = $this->modx->user->id;
                $note['createdon'] = time();
                $obj->fromArray($note);
                $obj->save();
            }
        }

        return $this->success();
    }
}

return 'adminToolsUploadNotesProcessor';