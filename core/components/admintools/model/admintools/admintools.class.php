<?php

/**
 * The base class for AdminTools.
 */
class AdminTools {
    /* @var modX $modx */
    public $modx;
    public $initialized = array();

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('admintools_core_path', $config, $this->modx->getOption('core_path') . 'components/admintools/');
		$assetsUrl = $this->modx->getOption('admintools_assets_url', $config, $this->modx->getOption('assets_url') . 'components/admintools/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'connectorUrl' => $connectorUrl,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'templatesPath' => $corePath . 'elements/templates/',
			'processorsPath' => $corePath . 'processors/'
		), $config);

		//$this->modx->addPackage('admintools', $this->config['modelPath']);
		//$this->modx->lexicon->load('admintools:default');
	}

    public function initialize($ctx = 'mgr') {
        switch ($ctx) {
            case 'mgr':
                if (empty($this->initialized[$ctx])) {
                    $this->modx->controller->addLexiconTopic('admintools:default');
                    $this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/main.css');
                    $this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/admintools.js');
                    $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/favorites.js');
                    if (empty($_SESSION['admintools']['favoriteElements']['states'])) {
                        $states = $this->getFromCache('states');
                        if (empty($states)) {
                            $_SESSION['admintools']['admintools']['favoriteElements']['states'] = array('template' => false, 'chunk' => false, 'tv' => false, 'plugin' => false, 'snippet' => false);
                            $this->saveToCache('states');
                        } else {
                            $_SESSION['admintools']['favoriteElements']['states'] = $states;
                        }
                    }
                    if (empty($_SESSION['admintools']['favoriteElements']['elements'])) {
                        $elements = $this->getFromCache('elements');
                        if (empty($elements)) {
                            $_SESSION['admintools']['favoriteElements']['elements'] = array('templates'=>array(),'tvs'=>array(),'chunks'=>array(),'plugins'=>array(),'snippets'=>array());
                            $this->saveToCache('elements');
                        } else {
                            $_SESSION['admintools']['favoriteElements']['elements'] = $elements;
                        }
                    }
                    $_SESSION['admintools']['favoriteElements']['icon'] = $this->modx->getOption('admintools_favorites_icon',null,'');

                    // system settings
                    if ($this->modx->getOption('admintools_remember_system_settings',null,true)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/systemsettings.js');
                        if (empty($_SESSION['admintools']['systemSettings'])) {
                            $settings = $this->getFromCache('systemSettings');
                            if (empty($settings)) {
                                $_SESSION['admintools']['systemSettings'] = array('namespace'=>'core','area'=>'');
                                $this->saveToCache('systemSettings');
                            } else {
                                $_SESSION['admintools']['systemSettings'] = $settings;
                            }
                        }
                        if (empty($_SESSION['admintools']['systemSettings']['namespace'])) $_SESSION['admintools']['systemSettings']['namespace'] = 'core';
                    }
                    // elements log
                    if ($this->modx->getOption('admintools_enable_elements_log',null,true)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/elementlog.js');
                    }
                    $_SESSION['admintools']['config'] = array(
                        'connector_url' => $this->config['assetsUrl'].'connector.php',
                    );
                    $_html = "<script type=\"text/javascript\">\n";
                    $_html .= "\tvar adminToolsSettings = ".$this->modx->toJSON($_SESSION['admintools'])."\n";
                    $_html .= "</script>";
                    $this->modx->controller->addHtml($_html);
                    $this->initialized[$ctx] = true;
                }
                break;
            case 'web':
                break;
        }
        return true;
    }

    public function getFromCache($cacheElementKey){
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/favorite_elements/'.$this->modx->user->id,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        return $this->modx->cacheManager->get($cacheElementKey, $cacheOptions);
    }

    public function saveToCache($cacheElementKey){
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/favorite_elements/'.$this->modx->user->id,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $this->modx->cacheManager->set($cacheElementKey,  $_SESSION['admintools']['favoriteElements'][$cacheElementKey], 0, $cacheOptions);
    }

    public function updateElementLog(array $object){
//$this->modx->log(modX::LOG_LEVEL_ERROR, print_r($object,1));
        $type = explode('/',$object['action']);
        $elementData = array(
            'type'=>$type[1],
            'eid' => $object['id'],
            'name' => $type[1] == 'template' ? $object['templatename'] : $object['name'],
            'editedon' => date('Y-m-d H:i:s'),
            'user' => $this->modx->user->get('username'),
        );
        $key = $elementData['type'].'-'.$elementData['eid'];
        $data[$key] = $elementData;

        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/elementlog/',
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $elements = $this->modx->cacheManager->get('element_log', $cacheOptions);
        if (is_array($elements)) {
            if (isset($elements[$key])) unset($elements[$key]);
            $elements = array_merge($data,$elements);
        }

        $this->modx->cacheManager->set('element_log',  $elements, 0, $cacheOptions);
    }

    public function getElementLog(){
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/elementlog/',
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        return $this->modx->cacheManager->get('element_log', $cacheOptions);
    }
}