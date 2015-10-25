<?php

/**
 * The home manager controller for AdminTools.
 *
 */
class AdminToolsHomeManagerController extends AdminToolsMainController {
	/* @var AdminTools $AdminTools */
	public $AdminTools;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('admintools');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addCss($this->AdminTools->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->AdminTools->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
		$this->addJavascript($this->AdminTools->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->AdminTools->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->AdminTools->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->AdminTools->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->AdminTools->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "admintools-page-home"});
		});
		</script>');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->AdminTools->config['templatesPath'] . 'home.tpl';
	}
}