<?php
/**
 * Get principals
 */
class PrincipalsGetListProcessor extends modProcessor {

    //public $permission = '';

    public function initList($query) {
        $query = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $list = array();
        $all = $this->modx->lexicon('admintools_permissions_all');
        $_all = function_exists('mb_strtolower') ? mb_strtolower($all) : strtolower($all);
        $guest = $this->modx->lexicon('admintools_permissions_guest');
        $_guest = function_exists('mb_strtolower') ? mb_strtolower($guest) : strtolower($guest);
        if (empty($query) || ($query && strpos($_all, $query) !== false)){
            $list[] = array(
                'id' => 'all-0',
                'name' => $all,
                'class' => 'at-principal-custom',
                'icon' => '',
            );
        }
        if (empty($query) || ($query && strpos($_guest, $query) !== false)){
            $list[] = array(
                'id' => 'gst-0',
                'name' => $guest,
                'class' => 'at-principal-custom',
                'icon' => '',
            );
        }
        return $list;
    }
    /**
     * @return mixed
     */
    public function process() {
        $list = $this->getPrincipals();

        $total = count($list);
        $start = $this->getProperty('start');
        $limit = $this->getProperty('limit');
        $list = array_slice($list, $start, $limit);
        return '{"success":true,"total":"'.$total.'","results":'.$this->modx->toJSON($list).'}';
    }

    /**
     * @return array
     */
    public function getPrincipals()
    {
        $query = trim($this->getProperty('query'));

        $list = $this->initList($query);
        // Get groups
        $q = $this->modx->newQuery('modUserGroup');
        $q->select('id, name');
        if ($query) {
            $q->where(array(
                'name:LIKE' => "%{$query}%",
            ));
        }
        if ($q->prepare() && $q->stmt->execute()) {
            $groups = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (!empty($groups)) {
            $_list = array();
            foreach ($groups as $group) {
                $_list[] = array(
                    'id' => 'grp-' . $group['id'],
                    'name' => $group['name'],
                    'class' => 'at-principal-group',
                    'icon' => '<i class="icon icon-group"></i> ',
                );
            }
            $list = array_merge($list, $_list);
        }
        // Get users
        $q = $this->modx->newQuery('modUser');
        $q->innerJoin('modUserProfile', 'Profile');
        $q->select('modUser.id, Profile.fullname');
        $q->where(array(
            'Profile.blocked' => 0,
            'modUser.active' => 1,
        ));
        if ($query) {
            $q->where(array(
                'Profile.fullname:LIKE' => "%{$query}%",
            ));
        }
        if ($q->prepare() && $q->stmt->execute()) {
            $users = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (!empty($users)) {
            $_list = array();
            foreach ($users as $user) {
                $_list[] = array(
                    'id' => 'usr-' . $user['id'],
                    'name' => $user['fullname'],
                    'class' => 'at-principal-user',
                    'icon' => '<i class="icon icon-user"></i> ',
                );
            }
            $list = array_merge($list, $_list);
        }
        return $list;
    }

    //TODO Debug
    public function prepareQueryAfterCount(xPDOQuery $c) {
//        $c->prepare();
//        $this->modx->log(modX::LOG_LEVEL_ERROR, $c->toSQL());
        return $c;
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();

        return $array;
    }
}
return 'PrincipalsGetListProcessor';