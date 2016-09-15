<?php

/**
 * Download the exported notes
 */
class adminToolsDownloadFileProcessor extends modObjectProcessor {
    public $objectType = 'admintools_notes';
    public $classKey = 'adminNotes';
    //public $languageTopics = array('admintools');
    //public $permission = 'export_notes';

    /**
     * @return array|string
     */
    public function process() {
        $path = $this->modx->getOption('admintools_core_path', NULL, $this->modx->getOption('core_path') . 'components/admintools/')."elements/tmp/";
        $file = $path.'notes.txt';

        if (!file_exists($file)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[adminNotes] '.$this->modx->lexicon('admintools_err_file_nf'));
            return $this->failure($this->modx->lexicon('admintools_err_file_nf'));
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: attachment; filename=notes.txt');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_get_level() && @ob_end_flush();
        @readfile($file);
        die();
    }
}

return 'adminToolsDownloadFileProcessor';