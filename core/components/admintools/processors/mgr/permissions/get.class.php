<?php

/**
 * Get an Item
 */
class resourcePermissionsGetProcessor extends modObjectGetProcessor {
    public $objectType = 'admintools_permissions';
    public $classKey = 'adminToolsPermissions';
    public $permission = 'access_permissions';


	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return mixed
	 */
	public function process() {
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		return parent::process();
	}
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