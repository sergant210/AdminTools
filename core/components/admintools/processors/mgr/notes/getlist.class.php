<?php
/**
 * Get notes
 */
class NotesGetListProcessor extends modObjectGetListProcessor {
    public $objectType = 'admintools_notes';
	public $classKey = 'adminNotes';
//    public $languageTopics = array('admintools:default');
    public $defaultSortField = 'adminNotes.id';
    public $defaultSortDirection = 'DESC';
    //public $permission = 'view_notes';

    /**
     * @return mixed
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->leftJoin('modUser','UserCreate');
        $c->select('adminNotes.*, UserCreate.username');
        $where = '(adminNotes.private = 0 OR adminNotes.private = 1 AND adminNotes.createdby = '.$this->modx->user->get('id').')';
        $query = trim($this->getProperty('searchQuery'));
        $wheresearch = trim($this->getProperty('wheresearch'));
        if ($query) {
            switch ($wheresearch) {
            	case 1:
                case '':
                    $where .= ' AND (adminNotes.title LIKE "%'.$query.'%" OR adminNotes.text LIKE "%'.$query.'%"  OR FIND_IN_SET(\''.$query.'\', `tags`))';
            		break;
                case 2:
                    $where .= ' AND adminNotes.title LIKE "%'.$query.'%"';
                    break;
                case 3:
                    $where .= ' AND adminNotes.text LIKE "%'.$query.'%"';
                    break;
                case 4:
                    $where .= " AND FIND_IN_SET('{$query}', `tags`)";
                    break;
            }
        }
        $c->where($where);
        $private = $this->getProperty('private');
        if ($private) {
            $c->andCondition(array('adminNotes.createdby'=>$this->modx->user->id));
        }

        return $c;
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();
        if (!empty($array['url'])) {
            $array['url'] = '<a href="' . $array['url'] . '" target="_blank">' . $array['url'] . '</a>';
        }
        // Edit note
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-pencil-square-o ',
            'title' => $this->modx->lexicon('admintools_note_edit'),
            //'multiple' => $this->modx->lexicon('fullcalendar_items_update'),
            'action' => 'updateNote',
            'button' => true,
            'menu' => true,
        );
        // Remove note
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('admintools_note_remove'),
            'multiple' => $this->modx->lexicon('admintools_notes_remove'),
            'action' => 'removeNote',
            'button' => true,
            'menu' => true,
        );

        return $array;
    }
}
return 'NotesGetListProcessor';