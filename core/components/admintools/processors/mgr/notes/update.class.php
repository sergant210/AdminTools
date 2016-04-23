<?php

/**
 * Update a note
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
		$title = trim($this->getProperty('title'));
		$text = trim($this->getProperty('text'));
        $text = preg_replace('/(<br>$)/', '', $text);
        $this->setProperty('text',$text);
//        $this->setProperty('text',htmlspecialchars($text));
		if (empty($id)) {
			return $this->modx->lexicon('admintools_note_err_ns');
		}
		if (empty($title)) {
			$this->modx->error->addField('title', $this->modx->lexicon('admintools_note_err_title'));
		}
        $url = trim($this->getProperty('url'));
        if ($url == 'http://') $this->setProperty('url', '');
        $this->setProperty('editedon', time());
        $this->setProperty('editedby', $this->modx->user->get('id'));
		return parent::beforeSet();
	}
}

return 'adminToolsNoteUpdateProcessor';
