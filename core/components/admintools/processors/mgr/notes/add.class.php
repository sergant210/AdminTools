<?php

/**
 * Create a note
 */
class adminToolsNoteCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'admintools_notes';
	public $classKey = 'adminNotes';
	//public $languageTopics = array('admintools');
	//public $permission = 'create_notes';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$title = trim($this->getProperty('title'));
		if (empty($title)) {
			$this->modx->error->addField('title', $this->modx->lexicon('admintools_note_err_title'));
		}
        $text = trim($this->getProperty('text'));
        $text = preg_replace('/(<br>$)/', '', $text);
        $this->setProperty('text',$text);
//        $this->setProperty('text',htmlspecialchars($text));
        $url = trim($this->getProperty('url'));
        if ($url == 'http://') $this->setProperty('url', '');
        $this->setProperty('createdon', time());
        $this->setProperty('createdby', $this->modx->user->get('id'));

		return parent::beforeSet();
	}
}

return 'adminToolsNoteCreateProcessor';