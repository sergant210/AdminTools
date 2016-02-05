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
        $this->modx->lexicon->load('admintools:default');
    }

    public function initialize($ctx = 'mgr') {
        switch ($ctx) {
            case 'mgr':
                if (empty($this->initialized[$ctx])) {
                    $this->modx->controller->addLexiconTopic('admintools:default');
                    $this->modx->controller->addCss($this->config['cssUrl'] . 'mgr/main.css');
                    $this->modx->controller->addJavascript($this->config['jsUrl'] . 'mgr/admintools.js');
                    // favorite elements
                    if ($this->modx->getOption('admintools_enable_favorite_elements',null,true)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/favorites.js');
                        if (empty($_SESSION['admintools']['favoriteElements']['states'])) {
//                            $states = $this->getFromCache('states', 'favorite_elements/' . $this->modx->user->id);
                            $states = $this->getFromProfile('adminToolsStates');
                            if (empty($states)) {
                                $_SESSION['admintools']['favoriteElements']['states'] = array('template' => false, 'chunk' => false, 'tv' => false, 'plugin' => false, 'snippet' => false);
                                $this->saveToProfile($_SESSION['admintools']['favoriteElements']['states'],'adminToolsStates');
                                //$this->saveToCache($_SESSION['admintools']['favoriteElements']['states'], 'states', 'favorite_elements/' . $this->modx->user->id);
                            } else {
                                $_SESSION['admintools']['favoriteElements']['states'] = $states;
                            }
                        }
                        if (empty($_SESSION['admintools']['favoriteElements']['elements'])) {
//                            $elements = $this->getFromCache('elements', 'favorite_elements/' . $this->modx->user->id);
                            $elements = $this->getFromProfile('adminToolsElements');
                            if (empty($elements)) {
                                $_SESSION['admintools']['favoriteElements']['elements'] = array(
                                    'templates' => array(),
                                    'tvs' => array(),
                                    'chunks' => array(),
                                    'plugins' => array(),
                                    'snippets' => array()
                                );
//                                $this->saveToCache($_SESSION['admintools']['favoriteElements']['elements'], 'elements', 'favorite_elements/' . $this->modx->user->id);
                                $this->saveToProfile($_SESSION['admintools']['favoriteElements']['elements'],'adminToolsElements');
                            } else {
                                $_SESSION['admintools']['favoriteElements']['elements'] = $elements;
                            }
                        }
                        $_SESSION['admintools']['favoriteElements']['icon'] = $this->modx->getOption('admintools_favorites_icon') ? 'icon '. $this->modx->getOption('admintools_favorites_icon') : '';
                    }
                    // system settings
                    if ($this->modx->getOption('admintools_remember_system_settings',null,true)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/systemsettings.js');
                        if (empty($_SESSION['admintools']['systemSettings'])) {
//                            $settings = $this->getFromCache('systemSettings', 'favorite_elements/' . $this->modx->user->id);
                            $settings = $this->getFromProfile('systemSettings');
                            if (empty($settings)) {
                                $_SESSION['admintools']['systemSettings'] = array('namespace'=>'core','area'=>'');
//                                $this->saveToCache($_SESSION['admintools']['systemSettings'], 'systemSettings', 'favorite_elements/' . $this->modx->user->id);
                                $this->saveToProfile($_SESSION['admintools']['systemSettings'],'systemSettings');
                            } else {
                                $_SESSION['admintools']['systemSettings'] = $settings;
                            }
                        }
                        if (empty($_SESSION['admintools']['systemSettings']['namespace'])) $_SESSION['admintools']['systemSettings']['namespace'] = 'core';
                    }
                    // edited elements log
                    if ($this->modx->getOption('admintools_enable_elements_log',null,true)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/elementlog.js');
                        $this->modx->controller->addLexiconTopic('manager_log');

                    }
                    // taskpanel
                    /*
                    if ($this->modx->getOption('admintools_enable_taskpanel',null,false)) {
                        $this->modx->controller->addLastJavascript($this->config['jsUrl'] . 'mgr/taskpanel.js');
                    }
                    */
                    // config
                    $_SESSION['admintools']['config'] = array(
                        'connector_url' => $this->config['assetsUrl'].'connector.php',
                    );
                    $_html = "<script type=\"text/javascript\">\n";
                    $_html .= "\tvar adminToolsSettings = ".$this->modx->toJSON($_SESSION['admintools'])."\n";
                    // Hide description of components at the menu Components
                    if ($this->modx->getOption('admintools_hide_component_description',null,true)) {
                        $_html .= "\tExt.onReady(function() {\n";
                        $_html .= "\t\tliComponents = Ext.get('limenu-components');\n\t\tliComponents.select('span.description').setVisibilityMode(Ext.Element.DISPLAY).hide();\n";
                        $_html .= "\t});\n";
                    }
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

    /**
     * TODO Remove after test
     * @param $cacheElementKey
     * @param $cacheFolder
     * @return mixed
     * @deprecated
     */
    public function getFromCache($cacheElementKey, $cacheFolder) {
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/' . $cacheFolder,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        return $this->modx->cacheManager->get($cacheElementKey, $cacheOptions);
    }

    /**
     * TODO Remove after test
     * @param $data
     * @param $cacheElementKey
     * @param $cacheFolder
     * @deprecated
     */
    public function saveToCache($data, $cacheElementKey, $cacheFolder) {
        $cacheHandler = $this->modx->getOption(xPDO::OPT_CACHE_HANDLER, null, 'xPDOFileCache');
        $cacheOptions = array(
            xPDO::OPT_CACHE_KEY => 'admintools/' . $cacheFolder,
            xPDO::OPT_CACHE_HANDLER => $cacheHandler,
        );
        $this->modx->cacheManager->set($cacheElementKey, $data, 0, $cacheOptions);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function getFromProfile($key) {
        if ($this->modx->user->isAuthenticated('mgr')) {
            $profile = $this->modx->user->getOne('Profile');
            $fields = $profile->get('extended');

            if (isset($fields[$key])) {
                return $fields[$key];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     * @param string $key
     */
    public function saveToProfile($data, $key) {
        if ($this->modx->user->isAuthenticated('mgr')) {
            $profile = $this->modx->user->getOne('Profile');
            $fields = $profile->get('extended');

            $fields[$key] = $data;
            $profile->set('extended', $fields);
            if (!$profile->save()) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[' . __METHOD__ . '] Could not save extended fields = ' . print_r($fields, 1));
            }
        }
    }

    /**
     * @param array $object
     * @deprecated
     */
    public function updateElementLog(array $object) {
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
        $elements = $this->getFromCache('element_log', 'elementlog/');
        if (is_array($elements)) {
            if (isset($elements[$key])) unset($elements[$key]);
            $elements = array_merge($data,$elements);
        } else {
            $elements = $data;
        }
        $this->saveToCache($elements, 'element_log', 'elementlog/');
    }

    public function getElementLog() {
        return $this->getFromCache('element_log', 'elementlog/');
    }

    /**
     * @param modResource $resource
     * @return modCacheManager
     */
    public function clearResourceCache(&$resource) {
        $resource->_contextKey = $resource->context_key;
        /** @var modCacheManager $cache */
        $cache = $this->modx->cacheManager->getCacheProvider($this->modx->getOption('cache_resource_key', null, 'resource'));
        $key = $resource->getCacheKey();
        $cache->delete($key, array('deleteTop' => true));
        $cache->delete($key);

        $this->modx->_clearResourceCache = true;
        $this->modx->cacheManager = new atCacheManager($this->modx);
    }


    public function sendLoginLink($data){
        $c = $this->modx->newQuery('modUser');
        $c->select(array('modUser.*','Profile.email','Profile.fullname'));
        $c->innerJoin('modUserProfile','Profile');
        $c->where(array(
            'modUser.username' => $data['userdata'],
            'OR:Profile.email:=' => $data['userdata'],
        ));
        $message = '';
        /** @var modUser $user */
        $user = $this->modx->getObject('modUser',$c);
        if ($user) {
            $this->modx->user = $user;
            if (!$this->modx->hasPermission('frames')) {
                $message = $this->modx->lexicon('admintools_user_not_found');
                $this->modx->user = null;
                $this->modx->user = $this->modx->getUser();
                return $message;
            }
            $hash = $this->addLoginState($user);
            if (!empty($hash)) {
                $key = md5($_SERVER['REMOTE_ADDR'].'/'.$_SERVER['HTTP_USER_AGENT'].$user->id);
                $args = array('a' => 'login', 'id' => $key, 'hash' => $hash);
                $url = $this->modx->makeUrl($this->modx->resource->id, '', $args,'full');
                $options['email_body'] = $this->modx->lexicon('admintools_authorization_email_body', array('url'=>$url));
                $this->sendEmail($user->get('email'), $options);
            } else {
                $message = $this->modx->lexicon('admintools_link_already_sent');
            }
        } else {
            $message = $this->modx->lexicon('admintools_user_not_found');
        }
        return $message;
    }

    /**
     * @param modUser $user
     * @return bool
     */
    public function addLoginState($user){
        $hash = '';
        $key = md5($_SERVER['REMOTE_ADDR'].'/'.$_SERVER['HTTP_USER_AGENT'].$user->id);
        $state = $this->getLoginState($key);
        if (empty($state)) {
            $ttl = $this->modx->getOption('admintools_authorization_ttl',null,200);
            $hash = md5(uniqid(md5($user->get('email') . '/' . $key), true));
            $this->modx->registry->user->subscribe('/admintools/login/');
            $this->modx->registry->user->send('/admintools/login/', array(
                $key => array(
                    'hash' => $hash,
                    'uid' => $user->get('id'),
                )
            ), array('ttl' => $ttl));
        }
        return $hash;
    }

    public function getLoginState($key){
        $data = '';
        if ($this->modx->getService('registry', 'registry.modRegistry')) {
            $this->modx->registry->addRegister('user', 'registry.modDbRegister');
            $this->modx->registry->user->connect();
            $this->modx->registry->user->subscribe('/admintools/login/'.$key);
            if ($msgs = $this->modx->registry->user->read(array('remove_read' => false, 'poll_limit' => 1))) {
                $data = reset($msgs);
            }
        }
        return $data;
    }
    public function deleteLoginState($key){
        $deleted = false;
        if ($this->modx->getService('registry', 'registry.modRegistry')) {
            $this->modx->registry->addRegister('user', 'registry.modDbRegister');
            $this->modx->registry->user->connect();
            $this->modx->registry->user->subscribe('/admintools/login/'.$key);
            $this->modx->registry->user->read(array('remove_read' => true, 'poll_limit' => 1));
            $deleted = true;
        }
        return $deleted;
    }

    /**
     * Sends email with authorization link
     *
     * @param $email
     * @param array $options
     *
     * @return string|bool
     */
    public function sendEmail($email, array $options = array()) {
        /** @var modPHPMailer $mail */
        $mail = $this->modx->getService('mail', 'mail.modPHPMailer');

        $mail->set(modMail::MAIL_BODY, $this->modx->getOption('email_body', $options, ''));
        $mail->set(modMail::MAIL_FROM, $this->modx->getOption('email_from', $options, $this->modx->getOption('emailsender'), true));
        $mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('email_from_name', $options, $this->modx->getOption('site_name'), true));
        $mail->set(modMail::MAIL_SUBJECT, $this->modx->getOption('email_subject', $options, $this->modx->lexicon('admintools_authorization_email_subject'), true));

        $mail->address('to', $email);
        $mail->address('reply-to', $this->modx->getOption('email_from', $options, $this->modx->getOption('emailsender'), true));
        $mail->setHTML(true);

        $response = !$mail->send()
            ? $mail->mailer->errorInfo
            : true;
        $mail->reset();

        return $response;
    }

    public function loginUser($userId)
    {
        $error_message = '';
        /** @var modUser $user */
        if ($user = $this->modx->getObject('modUser', $userId)) {
            $data['username'] = $user->get('username');
            $data['password'] = 'password';
            $data['login_context'] = 'mgr';
            $data['addContexts'] = array();
            $data['rememberme'] = (int) $this->modx->getOption('admintools_rememberme',null, 0);
        } else {
            return 'Error when try to login.';
        }
        $query = $this->modx->newQuery('modPlugin', array(
            'name' => 'AdminTools',
        ));
        $query->select('id');
        $id = $this->modx->getValue($query->prepare());
        if (!empty($id)) {
            $this->modx->eventMap['OnManagerPageBeforeRender'][$id] = $id;
            $this->modx->eventMap['OnManagerAuthentication'][$id] = $id;
        } else {
            $error_message = $this->modx->lexicon('admintools_plugin_not_found');
            return $error_message;
        }
        $this->modx->setOption('admintools_user_can_login', true);
        /** @var modProcessorResponse $response */
        $response = $this->modx->runProcessor('security/login', $data);
        if (($response instanceof modProcessorResponse) && !$response->isError()) {
            $key = md5($_SERVER['REMOTE_ADDR'] . '/' . $_SERVER['HTTP_USER_AGENT'] . $userId);
            $this->deleteLoginState($key);
            $url = $this->modx->getOption('manager_url', null, MODX_MANAGER_URL);
            $url = $this->modx->getOption('url_scheme', null, MODX_URL_SCHEME) . $this->modx->getOption('http_host', null, MODX_HTTP_HOST) . rtrim($url, '/');
            $this->modx->sendRedirect($url);
        } else {
            $errors = $response->getAllErrors();
            $error_message = implode("\n", $errors);
        }

        return $error_message;
    }
}

/**
 * Cache manager class for adminTools.
 */
require_once MODX_CORE_PATH.'model/modx/modcachemanager.class.php';
class atCacheManager extends modCacheManager
{
    public function refresh(array $providers = array(), array &$results = array())
    {
        if ($this->modx->getOption('admintools_clear_only resource_cache',null,false) && !empty($this->modx->_clearResourceCache)) {
            $this->modx->_clearResourceCache = false;
            unset($providers['resource']);
            $this->modx->cacheManager = null;
            $this->modx->getCacheManager();
        }
        return parent::refresh($providers, $results);
    }
}