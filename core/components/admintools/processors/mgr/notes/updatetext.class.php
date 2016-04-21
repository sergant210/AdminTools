<?php

/**
 * Update note text
 */
class adminToolsNoteUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'admintools_notes';
	public $classKey = 'adminNotes';
//	public $languageTopics = array('admintools');
	//public $permission = 'save_note';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$id = (int)$this->getProperty('id');
		if (empty($id)) {
			return $this->modx->lexicon('admintools_note_err_ns');
		}
        $text = trim($this->getProperty('text'));
        $text = preg_replace('/(<br>$)/', '', $text);
        $this->setProperty('text',$text);
//        $this->setProperty('text',htmlspecialchars($text));
        $this->setProperty('editedon', time());
        $this->setProperty('editedby', $this->modx->user->get('id'));
		return parent::beforeSet();
	}
}

return 'adminToolsNoteUpdateProcessor';
