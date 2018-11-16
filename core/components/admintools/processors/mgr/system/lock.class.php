<?php
/**
 * Lock the manager page.
 */
class atLockAdminPanelProcessor extends modProcessor {
    public $objectType = 'admintools';
    public $languageTopics = array('admintools:default');
    // public $classKey = '';
    //public $permission = '';

    public function process() {
        $_SESSION['admintools']['locked'] = true;
        return $this->success('');
    }
}
return 'atLockAdminPanelProcessor';