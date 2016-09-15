<?php

/**
 * Get an Item
 */
class resourcePermissionsGetProcessor extends modObjectGetProcessor {
    public $objectType = 'admintools_permissions';
    public $classKey = 'adminToolsPermissions';
    public $permission = 'access_permissions';


    /**
     * Return the response
     * @return array
     */
    public function cleanup() {
        $output = $this->object->toArray();
        switch ($output['principal_type']) {
            case 'all':
                $output['principal'] = 'all-0';
                break;
            case 'gst':
                $output['principal'] = 'gst-0';
                break;
            case 'grp':
                $output['principal'] = 'grp-' . $output['principal'];
                break;
            case 'usr':
                $output['principal'] = 'usr-' . $output['principal'];
                break;
        }
        return $this->success('', $output);
    }
}

return 'resourcePermissionsGetProcessor';