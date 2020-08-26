<?php
/** @var array $scriptProperties */
$path = $modx->getOption('admintools_core_path', null, $modx->getOption('core_path') . 'components/admintools/') . 'services/';
/** @var AdminTools $AdminTools */
$AdminTools = $modx->getService('admintools', 'AdminTools', $path);
$elementType = null;
if ($AdminTools instanceof AdminTools) {
    switch ($modx->event->name) {
        case 'OnManagerPageBeforeRender':
            if ($modx->user->id) {
                $AdminTools->initialize();
            }
            break;
        case 'OnManagerPageAfterRender':
            if ($AdminTools->isLocked()) {
                $controller->content = $modx->getChunk('tpl.lockScreen', [
                        'username' => $modx->user->username,
                        'photo' => $modx->user->getPhoto(),
                        'title' => $modx->getOption('site_name'),
                        'lang' => $modx->getOption('manager_language'),
                        'form_action' => $AdminTools->getOption('connectorUrl'),
                        'auth' => $modx->user->getUserToken('mgr'),
                        'assets_url' => MODX_ASSETS_URL,
                        'input_placeholder' => $AdminTools->getInputPlaceholder(),
                    ]
                );
            }
            break;
        case 'OnDocFormSave':
            if ($modx->getOption('admintools_clear_only_resource_cache', null, false) && $modx->event->params['mode'] === modSystemEvent::MODE_UPD) {
                if ($resource->get('syncsite')) {
                    $AdminTools->clearResourceCache($resource);
                }
                if (!empty($_POST['createCache'])) {
                    $AdminTools->createResourceCache($resource->uri);
                }
            }
            break;
        case 'OnManagerPageInit':
            if (!$modx->user->isAuthenticated('mgr') && $modx->getOption('admintools_email_authorization', null, false)) {
                $id = (int) $modx->getOption('admintools_loginform_resource');
                if (!empty($id) && $modx->getCount('modResource', ['id' => $id, 'published' => 1, 'deleted' => 0])) {
                    $url = $modx->makeUrl($id, '', '', 'full');
                    $modx->setOption('manager_login_url_alternate', $url);
                }
            }
            break;
        case 'OnManagerAuthentication':
            if ($modx->getOption('admintools_user_can_login', null, false)) {
                $modx->setOption('admintools_user_can_login', false);
                $modx->event->output(true);
            }
            break;
        case 'OnLoadWebDocument':
            if ((!$modx->user->active || $modx->user->Profile->blocked) && $modx->user->isAuthenticated($modx->context->get('key'))) {
                $modx->runProcessor('security/logout');
            }
            if ($modx->getOption('admintools_alternative_permissions', null, false) && !$AdminTools->hasPermissions()){
                $modx->sendUnauthorizedPage();
            }
            break;
        case 'OnTempFormPrerender':
            if ($modx->getOption('admintools_template_resource_relationship', null, true)) {
                $modx->controller->addLastJavascript($AdminTools->getOption('jsUrl') . 'mgr/templates.js');
            }
            break;
        case 'OnDocFormPrerender':
            $_html = [];
            $output = '';
            if ($modx->getOption('admintools_template_resource_relationship', null, true)) {
                $_html['tpl_res_relationship'] = '
            var tmpl = Ext.getCmp("modx-resource-template");
            if (tmpl.getValue()) tmpl.label.update(_("resource_template") + "&nbsp;&nbsp;<a href=\"?a=element/template/update&id=" + tmpl.getValue() + "\"><i class=\"icon icon-external-link\"></i></a>");';
            }
            if ($modx->getOption('admintools_clear_only_resource_cache', null, true) && $modx->event->params['mode'] != modSystemEvent::MODE_NEW) {
                $_html['create_resource_cache'] = '
            var cb = Ext.create({
                xtype: "xcheckbox",
                boxLabel: _("admintools_create_resource_cache"),
                description: _("admintools_create_resource_cache_help"),
                hideLabel: true,
                name: "createCache",
                id: "createCache",
                checked: '. (int)$modx->getOption('admintools_create_resource_cache', null, false) .'
            });
            if (Ext.getCmp("modx-page-settings-right-box-right")) {
                Ext.getCmp("modx-page-settings-right-box-right").insert(2,cb);
                Ext.getCmp("modx-page-settings-right-box-left").add(Ext.getCmp("modx-resource-uri-override"));
                Ext.getCmp("modx-panel-resource").on("success", function(o){
                    if (o.result.object.createCache != 0) {
                        cb.setValue(true);
                    }
                });
            }';
            }
            if (!empty($_html)) {
            $output .= '
    Ext.onReady(function() {
        setTimeout(function(){' . implode("\n", $_html) . '
        }, 200);
    });';
            }
            if ($modx->getOption('admintools_alternative_permissions', null, true) && $modx->hasPermission('access_permissions')) {
                $modx->controller->addLastJavascript($AdminTools->getOption('jsUrl') . 'mgr/permissions.js');
                $output .= '
    Ext.ComponentMgr.onAvailable("modx-resource-tabs", function() {
		this.on("beforerender", function() {
			this.add({
				title: _("admintools_permissions"),
				border: false,
				items: [{
					layout: "anchor",
					border: false,
					items: [{
						html: _("admintools_permissions_desc"),
						border: false,
						bodyCssClass: "panel-desc"
					}, {
						xtype: "admintools-grid-permissions",
						anchor: "100%",
						cls: "main-wrapper",
						resource: ' . $id . '
					}]
				}]
			});
		});
	});
';
            }
            if (!empty($output)) {
                $modx->controller->addHtml('<script>' . $output . '</script>');
            }
            break;
        case 'OnMODXInit':
            if (($modx->context->get('key') !== 'mgr')
                && $modx->getOption('admintools_only_current_context_user', null, false)
                && $modx->user->isAuthenticated('mgr')
                && !$modx->user->isAuthenticated($modx->context->get('key')))
            {
               $modx->user = $modx->newObject('modUser');
                $modx->user->fromArray(['id' => 0, 'username' => $modx->getOption('default_username', '', '(anonymous)', true)], '', true);
            }
            break;
    }
}