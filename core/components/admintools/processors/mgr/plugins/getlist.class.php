<?php
/**
 * Get plugins
 */
class eventPluginsGetListProcessor extends modObjectProcessor {
    public $objectType = 'admintools_plugin';
    public $classKey = 'modPluginEvent';
//    public $languageTopics = array('admintools:default');
    public $defaultSortField = 'event';
    public $defaultSortDirection = 'ASC';
    public $sortKey = 'event';  //plugin
    public $permission = 'view_plugin';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize() {

        $this->setDefaultProperties(array(
            'start' => 0,
            'limit' => 0,
            'sort' => $this->defaultSortField,
            'dir' => $this->defaultSortDirection,
            'combo' => true,
            'query' => '',
        ));
        return parent::initialize();
    }
    /**
     * @return mixed
     */
    public function process() {
        $c = $this->modx->newQuery($this->classKey);
        $c->innerJoin('modPlugin','Plugin');
        $c->leftJoin('modCategory','Category', 'Category.id = Plugin.category');
        $c->select('Plugin.id,Plugin.name,Plugin.disabled,Plugin.description,modPluginEvent.event,modPluginEvent.priority,Category.category as catName');
        $c->where('1=1');
        $event = trim($this->getProperty('event'));
        $query = trim($this->getProperty('query'));

        if (!empty($event)) {
            $c->andCondition(array(
                'modPluginEvent.event:LIKE'=>"%{$event}%",
            ));
        }
        if (!empty($query)) {
            $c->andCondition(array(
                'Category.category:LIKE'=>"%{$query}%",
                'OR:Plugin.name:LIKE'=>"%{$query}%",
            ));
        }
        $data = array();
        $data['total'] = $this->modx->getCount($this->classKey,$c);
        $limit = (int)$this->getProperty('limit');
        $start = (int)$this->getProperty('start');
        $this->sortKey = $this->getProperty('sort');
        $c->sortby($this->sortKey,$this->getProperty('dir'));
        if ($this->sortKey === 'event') {
            $c->sortby('modPluginEvent.priority','ASC');
            $c->sortby('Plugin.id','ASC');
        }

        if ($limit > 0) {
            $c->limit($limit,$start);
        }
        $c->prepare();
//$this->prepareQueryAfterCount($c);
//$this->modx->log(modX::LOG_LEVEL_ERROR, $c->toSql());
        $c->stmt->execute();
        $data['results'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);

        $list = array();
        $currentIndex = 0;
        foreach ($data['results'] as $object) {
            $objectArray = $this->prepareRow($object);
            if (!empty($objectArray) && is_array($objectArray)) {
                $list[] = $objectArray;
                $currentIndex++;
            }
        }
        return $this->outputArray($list,$data['total']);
    }

    public function prepareQueryAfterCount(xPDOQuery $c) {
        $c->prepare();
        $this->modx->log(modX::LOG_LEVEL_ERROR, $c->toSql());
        return $c;
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function prepareRow($array) {
        $array['key'] = $array['event'] . '_' . $array['id'];
        $array['name'] = '<a href="index.php?a=element/plugin/update&id='. $array['id'] . '">' . $array['name'] . '</a>';
        unset($array['id']);
        if ($this->sortKey === 'event') {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-link',
                'title' => $this->modx->lexicon('admintools_event_bind'),
                //'multiple' => $this->modx->lexicon('modextra_items_update'),
                'action' => 'bindEvent',
                'button' => true,
                'menu' => true,
            );
        } else {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-link',
                'title' => $this->modx->lexicon('admintools_plugin_bind'),
                //'multiple' => $this->modx->lexicon('modextra_items_update'),
                'action' => 'bindPlugin',
                'button' => true,
                'menu' => true,
            );
        }
        $array['active'] = !$array['disabled'];
        if (!$array['active']) {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-power-off action-green',
                'title' => $this->modx->lexicon('admintools_plugin_enable'),
//                'multiple' => $this->modx->lexicon('admintools_plugins_enable'),
                'action' => 'enablePlugin',
                'button' => true,
                'menu' => true,
            );
        }
        else {
            $array['actions'][] = array(
                'cls' => '',
                'icon' => 'icon icon-power-off action-red',
                'title' => $this->modx->lexicon('admintools_plugin_disable'),
//                'multiple' => $this->modx->lexicon('admintools_plugins_disable'),
                'action' => 'disablePlugin',
                'button' => true,
                'menu' => true,
            );
        }
        // Unbind
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-chain-broken',
            'title' => $this->modx->lexicon('admintools_plugin_unbind'),
//            'multiple' => $this->modx->lexicon('admintools_plugins_remove'),
            'action' => 'unbindPlugin',
            'button' => true,
            'menu' => true,
        );
        // Remove
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('admintools_plugin_remove'),
//            'multiple' => $this->modx->lexicon('admintools_plugins_unbind'),
            'action' => 'removePlugin',
            'button' => true,
            'menu' => true,
        );

        return $array;
    }
}
return 'eventPluginsGetListProcessor';