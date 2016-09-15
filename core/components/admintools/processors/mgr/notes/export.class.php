<?php
/**
 * Export notes
 */
class adminToolsExportNotesProcessor extends modObjectProcessor{
    public $objectType = 'admintools_notes';
    public $classKey = 'adminNotes';
    //public $languageTopics = array('admintools');
    //public $permission = 'export_notes';

    public function process() {
        $path = $this->modx->getOption('admintools_core_path', NULL, $this->modx->getOption('core_path') . 'components/admintools/')."elements/tmp/";
        if (!is_dir($path) && !mkdir($path,0755)) $this->failure($this->modx->lexicon('admintools_err_path_nf'));
        $notes = array();
        $q = $this->modx->newQuery('adminNotes');
        $q->select($this->modx->getSelectColumns('adminNotes'));
        if ($q->prepare() && $q->stmt->execute()) {
            $notes = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (empty($notes)) return $this->failure($this->modx->lexicon('admintools_no_notes'));
        $output = serialize($notes);
        $file = $path.'notes.txt';
        file_put_contents($file, $output);
        return $this->success();
    }
}

return 'adminToolsExportNotesProcessor';