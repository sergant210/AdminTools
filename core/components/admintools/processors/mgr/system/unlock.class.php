<?php
/**
 * Lock the manager page.
 */
class atUnlockAdminPanelProcessor extends modProcessor {
    public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
    // public $classKey = '';
    // public $permission = '';

    public function initialize() {
        $path = $this->modx->getOption('admintools_core_path', null, $this->modx->getOption('core_path') . 'components/admintools/') . 'model/admintools/';
        $this->modx->getService('admintools', 'AdminTools', $path, array());
        return ($this->modx->admintools instanceof AdminTools);
    }

    public function process() {
        if (isset($_POST['admintools_action'])
            && $_POST['admintools_action'] == 'unlock'
            && $this->modx->admintools->unlock(filter_input(INPUT_POST, 'unlock_code', FILTER_SANITIZE_SPECIAL_CHARS))
        ) {
            $_SESSION['admintools']['locked'] = false;
            return $this->success('');
        }
        return $this->failure('Error');
    }
}
return 'atUnlockAdminPanelProcessor';