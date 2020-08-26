<?php
/**
 * Grabs all elements for element tree. Used instead the original MODX processor.
 *
 * @param string $id (optional) Parent ID of object to grab from. Defaults to 0.
 *
 * @package AdminTools
 */
class modElementGetNodesProcessor extends modProcessor {
    public $typeMap = [
        'template' => 'modTemplate',
        'tv' => 'modTemplateVar',
        'chunk' => 'modChunk',
        'snippet' => 'modSnippet',
        'plugin' => 'modPlugin',
        'category' => 'modCategory',
    ];
    public $actionMap = [];
    public $checkPermission = true;


    public function checkPermissions() {
        return $this->modx->hasPermission('element_tree');
    }
    public function getLanguageTopics() {
        return ['category', 'element'];
    }

    public function initialize() {
        $this->setDefaultProperties([
            'stringLiterals' => false,
            'id' => 0,
        ]);
        $this->checkPermission = (!$this->modx->user->sudo && $this->modx->getOption('admintools_check_elements_permissions', null, true));
        return true;
    }

    /**
     * @return string JSON response
     */
    public function process() {
        $this->getActions();
        $map = $this->getMap();
        /* load correct mode */
        switch ($map[0]) {
            case 'type': /* if in the element, but not in a category */
                if ($_SESSION['admintools']['favoriteElements']['states'][$map[1]]) {
                    $nodes = $this->getFavoriteElements($map[1]);
                } else {
                    $nodes = $this->getTypeNodes($map);
                }
                break;
            case 'root': /* if clicking one of the root nodes */
                $nodes = $this->getRootNodes($map);
                break;
            case 'category': /* if browsing categories */
                $nodes = $this->getCategoryNodes($map);
                break;
            default: /* if clicking a node in a category */
                $nodes = $this->getInCategoryNodes($map);
                break;
        }

        if ($this->getProperty('stringLiterals', false)) {
            return $this->modx->toJSON($nodes);
        }

        return $this->toJSON($nodes);
    }

    public function getActions() {
        $this->actionMap = [
            'template' => 'element/template/update',
            'tv' => 'element/tv/update',
            'chunk' => 'element/chunk/update',
            'snippet' => 'element/snippet/update',
            'plugin' => 'element/plugin/update',
        ];
    }

    /**
     * @return false|string[]
     */
    public function getMap() {
        /* process ID prefixes */
        $id = $this->getProperty('id');
        $id = empty($id) ? 0 : (substr($id,0,2) == 'n_' ? substr($id,2) : $id);
        /* split the array */
        return explode('_',$id);
    }

    public function getFavoriteElements($type){
        $elementClassKey = $this->typeMap[$type];;
        $nodes = [];
        if (count($_SESSION['admintools']['favoriteElements']['elements'][$type.'s']) === 0 ) {
            return $nodes;
        }
        $c = $this->modx->newQuery($elementClassKey);
        $c->where([
            'id:IN' => $_SESSION['admintools']['favoriteElements']['elements'][$type . 's'],
        ]);
        $c->sortby($elementClassKey === 'modTemplate' ? 'templatename' : 'name','ASC');

        if ($this->checkPermission) {
            $elements = $this->modx->getCollection($elementClassKey, $c);
            /* do permission checks */
            $canNewCategory = $this->modx->hasPermission('new_category');
            $canEditElement = $this->modx->hasPermission('edit_' . $type);
            $canDeleteElement = $this->modx->hasPermission('delete_' . $type);
            $canNewElement = $this->modx->hasPermission('new_' . $type);
            $showElementIds = $this->modx->hasPermission('tree_show_element_ids');
        } else {
            $c->select($this->modx->getSelectColumns($elementClassKey));
            $c->prepare();
            $c->stmt->execute();
            $elements = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            $canNewCategory = true;
            $canEditElement = true;
            $canDeleteElement = true;
            $canNewElement = true;
            $showElementIds = true;
        }

        /* loop through elements */
        /** @var modElement $element */
        foreach ($elements as $element) {

            if ($this->checkPermission) {
                if (!$element->checkPolicy('list')) {
                    continue;
                }
                /* handle templatename case */
                $name = $elementClassKey === 'modTemplate' ? $element->get('templatename') : $element->get('name');
            } else {
                $name = $elementClassKey === 'modTemplate' ? $element['templatename'] : $element['name'];
            }
            $class = [];
            if ($canNewElement) $class[] = 'pnew';
            if ($this->checkPermission) {
                if ($canEditElement && $element->checkPolicy(['save' => true, 'view' => true])) {
                    $class[] = 'pedit';
                }
                if ($canDeleteElement && $element->checkPolicy('remove')) {
                    $class[] = 'pdelete';
                }
                $element = $element->toArray();
            } else {
                $class[] = 'pedit';
                $class[] = 'pdelete';
            }
            if ($canNewCategory) $class[] = 'pnewcat';
            if ($element['locked']) $class[] = 'element-node-locked';
            if ($elementClassKey === 'modPlugin' && @$element['disabled']) {
                $class[] = 'element-node-disabled';
            }
            if (!empty($scriptProperties['currentElement']) && $scriptProperties['currentElement'] == $element['id'] && $scriptProperties['currentAction'] == $this->actionMap[$type]) {
                $class[] = 'active-node';
            }
            if ($element['static'] ) {
                $class[] = 'static';
            }

            $active = false;
            if ($this->getProperty('currentElement') === $element['id'] && $this->getProperty('currentAction') === $this->actionMap[$type]) {
                $active = true;
            }
            $favIcon = $this->modx->getOption('admintools_favorites_icon',null,'');
            if (in_array($element['id'],$_SESSION['admintools']['favoriteElements']['elements'][$type.'s']) && $favIcon) {
                $icon =  $favIcon;
            } else {
                $icon = ($element['icon'] ? $element['icon'] : ($element['static'] ? 'icon-file-text-o' : 'icon-file-o'));
            }

            $idNote = $showElementIds ? ' (' . $element['id'] . ')' : '';
            $nodes[] = [
                'text' => strip_tags($name) . $idNote,
                'id' => 'n_' . $type . '_element_' . $element['id'] . '_0',
                'pk' => $element['id'],
                'category' => 0,
                'leaf' => true,
                'name' => $name,
                'cls' => implode(' ', $class),
                'iconCls' => $icon,
                'page' => '?a=' . $this->actionMap[$type] . '&id=' . $element['id'],
                'type' => $type,
                'elementType' => ucfirst($type),
                'classKey' => $elementClassKey,
                'active' => (isset($element['disabled']) ? !$element['disabled'] : true),
                'qtip' => strip_tags($element['description']),
                'selected' => $active,
                'favorite' => in_array($element['id'], $_SESSION['admintools']['favoriteElements']['elements'][$type . 's'])
            ];
        }
        return $nodes;
    }
    /**
     * Default icons for element types
     * @param $elementIdentifier string Element Type
     * @return string
     */
    public function getNodeIcon($elementIdentifier){
        $defaults = [
            'template' => 'icon icon-columns',
            'chunk' => 'icon icon-th-large',
            'tv' => 'icon icon-list-alt',
            'snippet' => 'icon icon-code',
            'plugin' => 'icon icon-cogs',
            'category' => 'icon icon-folder'
        ];
        return $this->modx->getOption('mgr_tree_icon_'.$elementIdentifier,null, $defaults[$elementIdentifier]);
    }

    public function getRootNodes(array $map) {
        $elementType = ucfirst($map[0]);
        $nodes = [];

        /* Templates */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_template')) {
                $class = $this->modx->hasPermission('new_template') ? ' pnew' : '';
                $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('templates'),
                    'id' => 'n_type_template',
                    'leaf' => false,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('template'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'template',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('templates'),
                'id' => 'n_type_template',
                'leaf' => false,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('template'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'template',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }

        /* TVs */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_tv')) {
                $class = $this->modx->hasPermission('new_tv') ? ' pnew' : '';
                $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('tmplvars'),
                    'id' => 'n_type_tv',
                    'leaf' => false,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('tv'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'tv',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('tmplvars'),
                'id' => 'n_type_tv',
                'leaf' => false,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('tv'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'tv',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }
        /* Chunks */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_chunk')) {
                $class = $this->modx->hasPermission('new_chunk') ? ' pnew' : '';
                $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('chunks'),
                    'id' => 'n_type_chunk',
                    'leaf' => false,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('chunk'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'chunk',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('chunks'),
                'id' => 'n_type_chunk',
                'leaf' => false,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('chunk'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'chunk',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }

        /* Snippets */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_snippet')) {
                $class = $this->modx->hasPermission('new_snippet') ? ' pnew' : '';
                $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('snippets'),
                    'id' => 'n_type_snippet',
                    'leaf' => false,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('snippet'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'snippet',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('snippets'),
                'id' => 'n_type_snippet',
                'leaf' => false,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('snippet'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'snippet',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }

        /* Plugins */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_plugin')) {
                $class = $this->modx->hasPermission('new_snippet') ? ' pnew' : '';
                $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('plugins'),
                    'id' => 'n_type_plugin',
                    'leaf' => false,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('plugin'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'plugin',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('plugins'),
                'id' => 'n_type_plugin',
                'leaf' => false,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('plugin'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'plugin',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }
        /* Categories */
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('view_category')) {
                $class = $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
                $class .= ' tree-pseudoroot-node';

                $nodes[] = [
                    'text' => $this->modx->lexicon('categories'),
                    'id' => 'n_category',
                    'leaf' => 0,
                    'cls' => $class,
                    'iconCls' => $this->getNodeIcon('category'),
                    'page' => '',
                    'classKey' => 'root',
                    'type' => 'category',
                    'draggable' => false,
                    'pseudoroot' => true,
                ];
            }
        } else {
            $nodes[] = [
                'text' => $this->modx->lexicon('categories'),
                'id' => 'n_category',
                'leaf' => 0,
                'cls' => ' pnew pnewcat tree-pseudoroot-node',
                'iconCls' => $this->getNodeIcon('category'),
                'page' => '',
                'classKey' => 'root',
                'type' => 'category',
                'draggable' => false,
                'pseudoroot' => true,
            ];
        }

        return $nodes;
    }

    public function getCategoryNodes(array $map) {
        if (!empty($map[1])) {
            /* if grabbing subcategories */
            $c = $this->modx->newQuery('modCategory');
            $c->where([
                'parent' => $map[1],
            ]);
            $c->sortby($this->modx->getSelectColumns('modCategory', 'modCategory', '', ['category']), 'ASC');
        } else {
            /* if trying to grab all root categories */
            $c = $this->modx->newQuery('modCategory');
            $c->where([
                'parent' => 0,
            ]);
            $c->sortby($this->modx->getSelectColumns('modCategory', 'modCategory', '', ['category']), 'ASC');
        }

        $c->select($this->modx->getSelectColumns('modCategory', 'modCategory'));
        $c->select([
            'COUNT(' . $this->modx->getSelectColumns('modCategory', 'Children', '', ['id']) . ') AS childrenCount',
        ]);
        $c->leftJoin('modCategory', 'Children');
        $c->groupby($this->modx->getSelectColumns('modCategory', 'modCategory'));

        /* set permissions as css classes */
        $class = ['folder'];
        $types = ['template', 'tv', 'chunk', 'snippet', 'plugin'];
        if ($this->checkPermission) {
            foreach ($types as $type) {
                if ($this->modx->hasPermission('new_'.$type)) {
                    $class[] = 'pnew_'.$type;
                }
            }
            if ($this->modx->hasPermission('new_category')) $class[] = 'pnewcat';
            if ($this->modx->hasPermission('edit_category')) $class[] = 'peditcat';
            if ($this->modx->hasPermission('delete_category')) $class[] = 'pdelcat';
        } else {
            foreach ($types as $type) {
                $class[] = 'pnew_'.$type;
            }
            $class[] = 'pnewcat';
            $class[] = 'peditcat';
            $class[] = 'pdelcat';
        }

        $class = implode(' ',$class);

        /* get and loop through categories */
        $nodes = [];
        if ($this->checkPermission) {
            $categories = $this->modx->getCollection('modCategory',$c);
        } else {
            $c->prepare();
            $c->stmt->execute();
            $categories = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        /** @var modCategory $category */
        foreach ($categories as $category) {
            if ($this->checkPermission) {
                if (!$category->checkPolicy('list')) continue;
                $idNote = $this->modx->hasPermission('tree_show_element_ids') ? ' (' . $category->get('id') . ')' : '';
                $category = $category->toArray();
            } else {
                $idNote = ' (' . $category['id'] . ')';
            }

            $nodes[] = [
                'text' => strip_tags($category['category']) . $idNote,
                'id' => 'n_category_' . $category['id'],
                'pk' => $category['id'],
                'data' => $category,
                'category' => $category['id'],
                'leaf' => false,
                'cls' => $class,
                'iconCls' => $this->getNodeIcon('category'),
                'page' => '',
                'classKey' => 'modCategory',
                'type' => 'category',
            ];
        }
        if (!empty($map[1])) {
            foreach (array_keys($this->actionMap) as $type) {
                $nodes = array_merge($nodes, $this->getInCategoryElements([$type, $map[1]]));
            }
        }
        return $nodes;
    }

    public function getInCategoryNodes(array $map) {
        $nodes = [];
        /* 0: type,  1: element/category  2: elID  3: catID */
        $categoryId = isset($map[3]) ? $map[3] : ($map[1] === 'category' ? $map[2] : 0);
        $elementIdentifier = $map[0];
        $elementType = ucfirst($elementIdentifier);
        $elementClassKey = $this->typeMap[$elementIdentifier];

        /* first handle subcategories */
        $c = $this->modx->newQuery('modCategory');
        $c->select($this->modx->getSelectColumns('modCategory', 'modCategory'));
        $c->select('COUNT(DISTINCT ' . $elementClassKey . '.id) AS elementCount');
        $c->select('COUNT(DISTINCT ' . $this->modx->getSelectColumns('modCategory', 'Children', '', ['id']) . ') AS childrenCount');
        $c->leftJoin($elementClassKey, $elementClassKey, $elementClassKey . '.category = modCategory.id');
        $c->leftJoin('modCategory', 'Children');
        $c->where([
            'parent' => $categoryId,
        ]);
        $c->groupby($this->modx->getSelectColumns('modCategory', 'modCategory'));
        $c->sortby($this->modx->getSelectColumns('modCategory', 'modCategory', '', ['category']), 'ASC');

        if ($this->checkPermission) {
            $categories = $this->modx->getCollection('modCategory',$c);
        } else {
            $c->prepare();
            $c->stmt->execute();
            $categories = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /* set permissions as css classes */
        $class = ['folder'];
        $types = ['template', 'tv', 'chunk', 'snippet', 'plugin'];
        foreach ($types as $type) {
            if ($this->checkPermission) {
                if ($this->modx->hasPermission('new_'.$type)) {
                    $class[] = 'pnew_'.$type;
                }
            } else {
                $class[] = 'pnew_'.$type;
            }
        }
        if ($this->checkPermission) {
            if ($this->modx->hasPermission('new_category')) $class[] = 'pnewcat';
            if ($this->modx->hasPermission('edit_category')) $class[] = 'peditcat';
            if ($this->modx->hasPermission('delete_category')) $class[] = 'pdelcat';
        } else {
            $class[] = 'pnewcat';
            $class[] = 'peditcat';
            $class[] = 'pdelcat';
        }

        $class = implode(' ',$class);

        /* loop through categories */
        /** @var modCategory $category */
        foreach ($categories as $category) {
            if ($this->checkPermission) {
                if (!$category->checkPolicy('list')) continue;
                if ($category->get('elementCount') <= 0 && $category->get('childrenCount') <= 0) continue;
                $category = $category->toArray();
            } else {
                if ($category['elementCount'] <= 0 && $category['childrenCount'] <= 0) continue;
            }
            /* check subcategories recursively */
            if ($category['childrenCount'] > 0 && $category['elementCount'] < 1) {
                if ($this->subCategoriesHaveElements($category['id'], $elementClassKey) == false) {
                    continue;
                }
            }

            $cc = ($category['elementCount'] > 0) ? ' (' . $category['elementCount'] . ')' : '';
            $nodes[] = [
                'text' => strip_tags($category['category']) . $cc,
                'id' => 'n_' . $map[0] . '_category_' . ($category['id'] != null ? $category['id'] : 0),
                'pk' => $category['id'],
                'category' => $category['id'],
                'data' => $category,
                'leaf' => false,
                'cls' => $class,
                'iconCls' => $this->getNodeIcon('category'),
                'classKey' => 'modCategory',
                'elementType' => $elementType,
                'page' => '',
                'type' => $elementIdentifier,
            ];
        }

        /* all elements in category */
        $c = $this->modx->newQuery($elementClassKey);
        $c->where([
            'category' => $categoryId,
        ]);
        $c->sortby($elementIdentifier === 'template' ? 'templatename' : 'name','ASC');

        if ($this->checkPermission) {
            $elements = $this->modx->getCollection($elementClassKey,$c);
            /* do permission checks */
            $canNewElement = $this->modx->hasPermission('new_'.$elementIdentifier);
            $canEditElement = $this->modx->hasPermission('edit_'.$elementIdentifier);
            $canDeleteElement = $this->modx->hasPermission('delete_'.$elementIdentifier);
            $canNewCategory = $this->modx->hasPermission('new_category');
            $showElementIds = $this->modx->hasPermission('tree_show_element_ids');
        } else {
            $c->select($this->modx->getSelectColumns($elementClassKey));
            $c->prepare();
            $c->stmt->execute();
            $elements = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            $canNewCategory = true;
            $canEditElement = true;
            $canDeleteElement = true;
            $canNewElement = true;
            $showElementIds = true;
        }

        /* loop through elements */
        /** @var modElement $element */
        foreach ($elements as $element) {
            if ($this->checkPermission) {
                if (!$element->checkPolicy('list')) continue;
                /* handle templatename case */
                $name = $elementClassKey === 'modTemplate' ? $element->get('templatename') : $element->get('name');
            } else {
                $name = $elementClassKey === 'modTemplate' ? $element['templatename'] : $element['name'];
            }
            // Fix missing icon field in some elements (like modTemplateVar, modChunk)
            if (is_array($element)) {
                if (!isset($element['icon'])) {
                    $element['icon'] = '';
                }
            } else {
                if (!isset($element->icon)) {
                    $element->icon = '';
                }
            }
            $class = [];
            if ($canNewElement) $class[] = 'pnew';
            if ($this->checkPermission) {
                if ($canEditElement && $element->checkPolicy(['save' => true, 'view' => true])) $class[] = 'pedit';
                if ($canDeleteElement && $element->checkPolicy('remove')) $class[] = 'pdelete';
                $element = $element->toArray();
            } else {
                $class[] = 'pedit';
                $class[] = 'pdelete';
            }
            if ($canNewCategory) $class[] = 'pnewcat';
            if ($element['locked']) $class[] = 'element-node-locked';
            if ($elementClassKey === 'modPlugin' && @$element['disabled']) {
                $class[] = 'element-node-disabled';
            }
            if ($element['static'] ) {
                $class[] = 'static';
            }
            $active = false;
            if ($this->getProperty('currentElement') == $element['id'] && $this->getProperty('currentAction') == $this->actionMap[$map[0]]) {
                $active = true;
            }
            $favIcon = $this->modx->getOption('admintools_favorites_icon',null,'');
            if (in_array($element['id'],$_SESSION['admintools']['favoriteElements']['elements'][$map[0].'s']) && $favIcon) {
                $icon =  $favIcon;
            } else {
                $icon = ($element['icon'] ? $element['icon'] : ($element['static'] ? 'icon-file-text-o' : 'icon-file-o'));
            }

            $idNote = $showElementIds ? ' (' . $element['id'] . ')' : '';

            $nodes[] = [
                'text' => strip_tags($name) . $idNote,
                'id' => 'n_' . $elementIdentifier . '_element_' . $element['id'] . '_' . $element['category'],
                'pk' => $element['id'],
                'category' => $categoryId,
                'leaf' => true,
                'name' => $name,
                'cls' => implode(' ', $class),
                'iconCls' => $icon,
                'page' => '?a=' . $this->actionMap[$elementIdentifier] . '&id=' . $element['id'],
                'type' => $elementIdentifier,
                'elementType' => $elementType,
                'classKey' => $elementClassKey,
                'active' => (isset($element['disabled']) ? !$element['disabled'] : true),
                'qtip' => strip_tags($element['description']),
                'selected' => $active,
                'favorite' => in_array($element['id'], $_SESSION['admintools']['favoriteElements']['elements'][$map[0] . 's'])
            ];
        }

        return $nodes;
    }

    /**
     * @param array $map
     * 0: type of element
     * 1: parent category
     * @return array
     */
    public function getInCategoryElements(array $map)
    {
        $nodes = [];
        $elementIdentifier = $map[0];
        $categoryId = $map[1];
        $elementType = ucfirst($elementIdentifier);
        $elementClassKey = $this->typeMap[$elementIdentifier];

        /* all elements in category */
        $c = $this->modx->newQuery($elementClassKey);
        $c->where([
            'category' => $categoryId,
        ]);
        $c->sortby($elementIdentifier === 'template' ? 'templatename' : 'name','ASC');
        if ($this->checkPermission) {
            $elements = $this->modx->getCollection($elementClassKey, $c);
            /* do permission checks */
            $canNewElement = $this->modx->hasPermission('new_' . $elementIdentifier);
            $canEditElement = $this->modx->hasPermission('edit_' . $elementIdentifier);
            $canDeleteElement = $this->modx->hasPermission('delete_' . $elementIdentifier);
            $canNewCategory = $this->modx->hasPermission('new_category');
            $showElementIds = $this->modx->hasPermission('tree_show_element_ids');
        } else {
            $c->select($this->modx->getSelectColumns($elementClassKey));
            $c->prepare();
            $c->stmt->execute();
            $elements = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            $canNewElement = true;
            $canEditElement = true;
            $canDeleteElement = true;
            $canNewCategory = true;
            $showElementIds = true;
        }
        /* loop through elements */
        /** @var modElement $element */
        foreach ($elements as $element) {
            if ($this->checkPermission) {
                if (!$element->checkPolicy('list')) {
                    continue;
                }
                $name = $elementIdentifier === 'template' ? $element->get('templatename') : $element->get('name');
            } else {
                $name = $elementIdentifier === 'template' ? $element['templatename'] : $element['name'];
            }
            $class = [];
            if ($canNewElement) $class[] = 'pnew';
            if ($this->checkPermission) {
                if ($canEditElement && $element->checkPolicy(['save' => true, 'view' => true])) $class[] = 'pedit';
                if ($canDeleteElement && $element->checkPolicy('remove')) $class[] = 'pdelete';
                $element = $element->toArray();
            } else {
                $class[] = 'pedit';
                $class[] = 'pdelete';
            }
            if ($canNewCategory) $class[] = 'pnewcat';
            if ($element['locked']) $class[] = 'element-node-locked';
            if ($elementClassKey === 'modPlugin' && $element['disabled']) {
                $class[] = 'element-node-disabled';
            }
            $idNote = $showElementIds ? ' (' . $element['id'] . ')' : '';
            $nodes[] = [
                'text' => strip_tags($name) . $idNote,
                'id' => 'n_c_' . $elementIdentifier . '_element_' . $element['id'] . '_' . $element['category'],
                'pk' => $element['id'],
                'category' => $categoryId,
                'leaf' => true,
                'name' => $name,
                'cls' => implode(' ', $class),
                'iconCls' => 'icon ' . $this->getNodeIcon($elementIdentifier),
                'page' => '?a=' . $this->actionMap[$elementIdentifier] . '&id=' . $element['id'],
                'type' => $elementIdentifier,
                'elementType' => $elementType,
                'classKey' => $elementClassKey,
                'active' => !$element['disabled'],
                'qtip' => strip_tags($element['description'])
            ];
        }

        return $nodes;
    }

    public function getTypeNodes(array $map) {
        $nodes = [];
        $elementType = ucfirst($map[1]);
        $elementClassKey = $this->typeMap[$map[1]];

        /* get elements in this type */
        $c = $this->modx->newQuery('modCategory');
        $c->select($this->modx->getSelectColumns('modCategory','modCategory'));
        $c->select('
            COUNT(DISTINCT ' . $this->modx->getSelectColumns($elementClassKey, $elementClassKey, '', ['id']) . ') AS elementCount,
            COUNT(DISTINCT ' . $this->modx->getSelectColumns('modCategory', 'Children', '', ['id']) . ') AS childrenCount
        ');
        $c->leftJoin($elementClassKey, $elementClassKey,
            $this->modx->getSelectColumns($elementClassKey, $elementClassKey, '', ['category']) . ' = ' . $this->modx->getSelectColumns('modCategory', 'modCategory', '', ['id']));
        $c->leftJoin('modCategory', 'Children');
        $c->where([
            'modCategory.parent' => 0,
        ]);
        $c->sortby($this->modx->getSelectColumns('modCategory', 'modCategory', '', ['category']), 'ASC');
        $c->groupby($this->modx->getSelectColumns('modCategory', 'modCategory'));
        if ($this->checkPermission) {
            $categories = $this->modx->getCollection('modCategory', $c);
        } else {
            $c->prepare();
            $c->stmt->execute();
            $categories = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /* set permissions as css classes */
        $class = 'folder';
        $types = ['template', 'tv', 'chunk', 'snippet', 'plugin'];
        foreach ($types as $type) {
            if ($this->checkPermission) {
                if ($this->modx->hasPermission('new_'.$type)) {
                    $class .= ' pnew_'.$type;
                }
            } else {
                $class .= ' pnew_'.$type;
            }
        }
        if ($this->checkPermission) {
            $class .= $this->modx->hasPermission('new_category') ? ' pnewcat' : '';
            $class .= $this->modx->hasPermission('edit_category') ? ' peditcat' : '';
            $class .= $this->modx->hasPermission('delete_category') ? ' pdelcat' : '';
        } else {
            $class .= ' pnewcat peditcat pdelcat';
        }

        /* loop through categories with elements in this type */
        /** @var modCategory $category */
        foreach ($categories as $category) {
            if ($this->checkPermission && !$category->checkPolicy('list')) {
                continue;
            }

            $categoryId = is_object($category) ? (int)$category->get('id') : (int) $category['id'];
            $categoryCat = is_object($category) ? $category->get('category') : $category['category'];
            $elCount = is_object($category) ? (int)$category->get('elementCount') : (int) $category['elementCount'];
            $catCount = is_object($category) ? (int)$category->get('childrenCount') : (int) $category['childrenCount'];
            if ($elCount < 1 && $catCount < 1 && $categoryId != 0) {
                continue;
            }

            if ($catCount > 0 && $elCount < 1 && $this->subCategoriesHaveElements($categoryId, $elementClassKey) == false) {
                continue;
            }

            $cc = $elCount > 0 ? ' ('.$elCount.')' : '';

            $nodes[] = [
                'text' => strip_tags($categoryCat) . $cc,
                'id' => 'n_' . $map[1] . '_category_' . ($categoryId != null ? $categoryId : 0),
                'pk' => $categoryId,
                'category' => $categoryId,
                'data' => is_object($category) ? $category->toArray() : $category,
                'leaf' => false,
                'cls' => $class,
                'iconCls' => $this->getNodeIcon('category'),
                'page' => '',
                'classKey' => 'modCategory',
                'elementType' => $elementType,
                'type' => $map[1],
            ];
            unset($elCount,$childCats,$categoryId,$categoryCat);
        }

        /* now add elements in this type without a category */
        $c = $this->modx->newQuery($elementClassKey);
        $c->where([
            'category' => 0,
        ]);
        $c->sortby($elementClassKey === 'modTemplate' ? 'templatename' : 'name', 'ASC');

        if ($this->checkPermission) {
            $elements = $this->modx->getCollection($elementClassKey, $c);
            /* do permission checks */
            $canNewCategory = $this->modx->hasPermission('new_category');
            $canEditElement = $this->modx->hasPermission('edit_' . $map[1]);
            $canDeleteElement = $this->modx->hasPermission('delete_' . $map[1]);
            $canNewElement = $this->modx->hasPermission('new_' . $map[1]);
            $showElementIds = $this->modx->hasPermission('tree_show_element_ids');
        } else {
            $c->select($this->modx->getSelectColumns($elementClassKey));
            $c->prepare();
            $c->stmt->execute();
            $elements = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            $canNewCategory = true;
            $canEditElement = true;
            $canDeleteElement = true;
            $canNewElement = true;
            $showElementIds = true;
        }
        /* loop through elements */
        /** @var modElement $element */
        foreach ($elements as $element) {
            if ($this->checkPermission) {
                if (!$element->checkPolicy('list')) continue;
                /* handle templatename case */
                $name = $elementClassKey === 'modTemplate' ? $element->get('templatename') : $element->get('name');
                if (!isset($element->icon)) $element->icon = '';
            } else {
                $name = $elementClassKey === 'modTemplate' ? $element['templatename'] : $element['name'];
                if (!isset($element['icon'])) $element['icon'] = '';
            }
            // Fix missing icon field into some elements (like modTemplateVar, modChunk)
            $class = [];
            if ($canNewElement) $class[] = 'pnew';
            if ($this->checkPermission) {
                if ($canEditElement && $element->checkPolicy(['save' => true, 'view' => true])) $class[] = 'pedit';
                if ($canDeleteElement && $element->checkPolicy('remove')) $class[] = 'pdelete';
                $element = $element->toArray();
            } else {
                $class[] = 'pedit';
                $class[] = 'pdelete';
            }
            if ($canNewCategory) $class[] = 'pnewcat';
            if ($element['locked']) $class[] = 'element-node-locked';
            if ($elementClassKey === 'modPlugin' && @$element['disabled']) {
                $class[] = 'element-node-disabled';
            }
            if ($element['static'] ) {
                $class[] = 'static';
            }

            if (!empty($scriptProperties['currentElement']) && $scriptProperties['currentElement'] == $element['id'] && $scriptProperties['currentAction'] == $this->actionMap[$map[1]]) {
                $class[] = 'active-node';
            }

            $active = false;
            if ($this->getProperty('currentElement') == $element['id'] && $this->getProperty('currentAction') == $this->actionMap[$map[1]]) {
                $active = true;
            }
            $favIcon = $this->modx->getOption('admintools_favorites_icon',null,'');
            if (in_array($element['id'],$_SESSION['admintools']['favoriteElements']['elements'][$map[1].'s']) && $favIcon) {
                $icon =  $favIcon;
            } else {
                $icon = ($element['icon'] ? $element['icon'] : ($element['static'] ? 'icon-file-text-o' : 'icon-file-o'));
            }
            $idNote = $showElementIds ? ' (' . $element['id'] . ')' : '';
            $nodes[] = [
                'text' => strip_tags($name) . $idNote,
                'id' => 'n_' . $map[1] . '_element_' . $element['id'] . '_0',
                'pk' => $element['id'],
                'category' => 0,
                'leaf' => true,
                'name' => $name,
                'cls' => implode(' ', $class),
                'iconCls' => $icon,
                'page' => '?a=' . $this->actionMap[$map[1]] . '&id=' . $element['id'],
                'type' => $map[1],
                'elementType' => $elementType,
                'classKey' => $elementClassKey,
                'active' => (isset($element['disabled']) ? !$element['disabled'] : true),
                'qtip' => strip_tags($element['description']),
                'selected' => $active,
                'favorite' => in_array($element['id'], $_SESSION['admintools']['favoriteElements']['elements'][$map[1] . 's']),
            ];
        }
        return $nodes;
    }

    protected function subCategoriesHaveElements($categoryId, $elementClassKey) {
        $return = false;

        $categories = $this->modx->getCollection('modCategory', [
            'parent' => $categoryId
        ]);

        foreach ($categories as $category) {
            $c = $this->modx->newQuery('modCategory');
            $c->select($this->modx->getSelectColumns('modCategory', 'modCategory'));
            $c->select('COUNT(DISTINCT ' . $elementClassKey . '.id) AS elementCount');
            $c->select('COUNT(DISTINCT ' . $this->modx->getSelectColumns('modCategory', 'Children', '', ['id']) . ') AS childrenCount');
            $c->leftJoin($elementClassKey, $elementClassKey, $elementClassKey . '.category = modCategory.id');
            $c->leftJoin('modCategory', 'Children');
            $c->where([
                'id' => $category->get('id'),
            ]);
            $c->groupby($this->modx->getSelectColumns('modCategory','modCategory'));
            $subCategory = $this->modx->getObject('modCategory', $c);

            if ($subCategory->get('elementCount') > 0) {
                $return = true;
            }
            if ($return === false && $subCategory->get('childrenCount') > 0) {
                $return = $this->subCategoriesHaveElements($subCategory->get('id'), $elementClassKey);
            }

        }
        return $return;
    }
}
return 'modElementGetNodesProcessor';
