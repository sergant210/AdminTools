<?php
/**
 * Get list of edited elements
 */
class EditedElementGetListProcessor extends modObjectGetListProcessor {
    public $objectType = 'modManagerLog';
	public $classKey = 'modManagerLog';
    public $languageTopics = array('modmanagerlog:default');
    public $defaultSortField = 'modManagerLog.occurred';
    public $defaultSortDirection = 'DESC';
    //public $permission = 'view';

    /**
     * @return mixed
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->select($this->modx->getSelectColumns('modManagerLog','modManagerLog','',array('action'),true));
//        $c->select($this->modx->getSelectColumns('modManagerLog','modManagerLog'));
        $c->select(array('User.username','Template.templatename','Chunk.name as chunkname','Snippet.name as snippetname','Plugin.name as pluginname','TV.name as tvname'));
        $c->innerJoin('modUser','User');
        $c->leftJoin('modTemplate','Template', '`modManagerLog`.item = `Template`.`id` AND `modManagerLog`.`classKey` = "modTemplate"');
        $c->leftJoin('modChunk','Chunk', '`modManagerLog`.item = `Chunk`.`id` AND `modManagerLog`.`classKey` = "modChunk"');
        $c->leftJoin('modSnippet','Snippet', '`modManagerLog`.item = `Snippet`.`id` AND `modManagerLog`.`classKey` = "modSnippet"');
        $c->leftJoin('modPlugin','Plugin', '`modManagerLog`.item = `Plugin`.`id` AND `modManagerLog`.`classKey` = "modPlugin"');
        $c->leftJoin('modTemplateVar','TV', '`modManagerLog`.item = `TV`.`id` AND `modManagerLog`.`classKey` = "modTemplateVar"');
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(
                '(Template.templatename LIKE "%'.$query.'%" OR Chunk.name LIKE "%'.$query.'%" OR Snippet.name LIKE "%'.$query.'%" OR Plugin.name LIKE "%'.$query.'%" OR TV.name LIKE "%'.$query.'%")'
            );
        } else {
            $c->where(
                '(modManagerLog.action LIKE "template_%" OR modManagerLog.action LIKE "chunk_%" OR modManagerLog.action LIKE "snippet_%" OR modManagerLog.action LIKE "plugin_%" OR modManagerLog.action LIKE "tv_%")'
            );
        }
        $user = intval($this->getProperty('user'));
        if ($user) {
            $c->andCondition(array('modManagerLog.user'=>$user));
        }
        $dateStart = trim($this->getProperty('datestart'));
        if ($dateStart) {
            $dateStart = date('Y-m-d',strtotime($dateStart));
            $c->andCondition(array('modManagerLog.occurred:>='=>$dateStart));
        }
        $dateEnd = trim($this->getProperty('dateend'));
        if ($dateEnd) {
            $dateEnd = date('Y-m-d 23:59:59',strtotime($dateEnd));
            $c->andCondition(array('modManagerLog.occurred:<='=>$dateEnd));
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
        $array['key'] = $array['classKey'].'-'.$array['item'];
        switch ($array['classKey']) {
            case 'modTemplate':
                $array['name'] = '<a href="index.php?a=element/template/update&id='. $array['item'] . '">' . $array['templatename'] . '</a>';
                break;
            case 'modChunk':
                $array['name'] = '<a href="index.php?a=element/chunk/update&id='. $array['item'] . '">' . $array['chunkname'] . '</a>';
                break;
            case 'modSnippet':
                $array['name'] = '<a href="index.php?a=element/snippet/update&id='. $array['item'] . '">' . $array['snippetname'] . '</a>';
                break;
            case 'modPlugin':
                $array['name'] = '<a href="index.php?a=element/plugin/update&id='. $array['item'] . '">' . $array['pluginname'] . '</a>';
                break;
            case 'modTemplateVar':
                $array['name'] = '<a href="index.php?a=element/tv/update&id='. $array['item'] . '">' . $array['tvname'] . '</a>';
                break;
        }
        if (!isset($array['name'])) $array['name'] = '('.$this->modx->lexicon('deleted').')';

        unset($array['templatename'],$array['chunkname'],$array['snippetname'],$array['pluginname'],$array['id']);
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-pencil-square-o ',
            'title' => $this->modx->lexicon('admintools_open'),
            //'multiple' => $this->modx->lexicon('fullcalendar_items_update'),
            'action' => 'openElement',
            'button' => true,
            'menu' => true,
        );

        return $array;
    }
}
return 'EditedElementGetListProcessor';