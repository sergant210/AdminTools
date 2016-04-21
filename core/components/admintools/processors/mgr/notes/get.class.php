<?php

/**
 * Get a note
 */
class adminToolsNoteGetProcessor extends modObjectGetProcessor {
	public $objectType = 'admintools_notes';
	public $classKey = 'adminNotes';
//	public $languageTopics = array('admintools:default');
	//public $permission = 'view_note';


    /** {@inheritdoc} */
    public function cleanup() {
        $output = $this->object->toArray();
        if (!$userUpdate = $this->object->getOne('UserUpdate')) {
            $output['editedby'] = '';
            $output['editedon'] = '';
        } else {
            $output['editedby'] = $userUpdate->get('username');
        }

        return $this->success('', $output);
    }
}

return 'adminToolsNoteGetProcessor';