<?php

/**
 * Remove resource permissions
 */
class resourcePermissionsRemoveProcessor extends modObjectProcessor {
    public $objectType = 'admintools_permissions';
    public $classKey = 'adminToolsPermissions';
    public $permission = 'access_permissions';


	/**
	 * @return array|string
	 */
	public function process() {
		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
			return $this->failure($this->modx->lexicon('admintools_permissions_err_ns'));
		}

		foreach ($ids as $id) {
			/** @var adminToolsPermissions $object */
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('admintools_permissions_err_nf'));
			}

			$object->remove();
		}

		return $this->success();
	}

}

return 'resourcePermissionsRemoveProcessor';