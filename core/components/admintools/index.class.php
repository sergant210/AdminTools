<?php

/**
 * Class AdminToolsMainController
 * Not used. May be in the future.
 */
abstract class AdminToolsMainController extends modExtraManagerController {
	/** @var AdminTools $AdminTools */
	public $AdminTools;


	/**
	 * @return void
	 */
	public function initialize() {
		$corePath = $this->modx->getOption('admintools_core_path', null, $this->modx->getOption('core_path') . 'components/admintools/');
		require_once $corePath . 'services/admintools.class.php';

		$this->AdminTools = new AdminTools($this->modx);
		//$this->addCss($this->AdminTools->getOption('cssUrl') . 'mgr/main.css');
		$this->addJavascript($this->AdminTools->getOption('jsUrl') . 'mgr/admintools.js');
		$this->addHtml('
		<script type="text/javascript">
			AdminTools.config = ' . $this->modx->toJSON($this->AdminTools->getOptions()) . ';
			AdminTools.config.connector_url = "' . $this->AdminTools->getOption('connectorUrl') . '";
		</script>
		');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('admintools:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends AdminToolsMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}