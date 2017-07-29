var AdminTools = function (config) {
	config = config || {};
	AdminTools.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, toolbar: {}
});
Ext.reg('admintools', AdminTools);

AdminTools = new AdminTools();

AdminTools.utils.renderBoolean = function (value, props, row) {
	return value
		? String.format('<span class="green">{0}</span>', _('yes'))
		: String.format('<span class="red">{0}</span>', _('no'));
};

AdminTools.utils.renderPrincipalType = function (value, props, row) {
	var output;
	switch (value) {
		case 'grp':
			output = '<i class="icon icon-group"></i>';
			break;
		case 'usr':
			output = '<i class="icon icon-user"></i>';
			break;
		default:
			output = '';
			break;
	}
	return output;
};

AdminTools.utils.getMenu = function (actions, grid, selected) {
	var menu = [];
	var cls, icon, title, action = '';

	for (var i in actions) {
		if (!actions.hasOwnProperty(i)) {
			continue;
		}

		var a = actions[i];
		if (!a['menu']) {
			if (a == '-') {
				menu.push('-');
			}
			continue;
		}
		else if (menu.length > 0 && /^remove/i.test(a['action'])) {
			menu.push('-');
		}

		if (selected.length > 1) {
			if (!a['multiple']) {
				continue;
			}
			else if (typeof(a['multiple']) == 'string') {
				a['title'] = a['multiple'];
			}
		}

		cls = a['cls'] ? a['cls'] : '';
		icon = a['icon'] ? a['icon'] : '';
		title = a['title'] ? a['title'] : a['title'];
		action = a['action'] ? grid[a['action']] : '';

		menu.push({
			handler: action,
			text: String.format(
				'<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
				cls, icon, title
			),
		});
	}

	return menu;
};


AdminTools.utils.renderActions = function (value, props, row) {
	var res = [];
	var cls, icon, title, action, item = '';
	for (var i in row.data.actions) {
		if (!row.data.actions.hasOwnProperty(i)) {
			continue;
		}
		var a = row.data.actions[i];
		if (!a['button']) {
			continue;
		}

		cls = a['cls'] ? a['cls'] : '';
		icon = a['icon'] ? a['icon'] : '';
		action = a['action'] ? a['action'] : '';
		title = a['title'] ? a['title'] : '';

		item = String.format(
			'<li class="{0}"><button class="btn btn-default {1}" action="{2}" title="{3}"></button></li>',
			cls, icon, action, title
		);

		res.push(item);
	}

	return String.format(
		'<ul class="admintools-row-actions">{0}</ul>',
		res.join('')
	);
};
// Status
AdminTools.combo.SearchTypes = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		triggerAction: 'all',
		typeAhead: true,
		mode: 'local',
		hideMode: 'offsets',
		autoScroll: true,
		maxHeight: 200,
		store: [[1,_('admintools_search_everywhere')],[2,_('admintools_search_in_titles')],[3,_('admintools_search_in_text')],[4,_('admintools_search_in_tags')]],
		hiddenName: 'wheresearch',
		editable: true
	});
	AdminTools.combo.SearchTypes.superclass.constructor.call(this,config);
};
Ext.extend(AdminTools.combo.SearchTypes,MODx.combo.ComboBox);
Ext.reg('admintools-combo-wheresearch',AdminTools.combo.SearchTypes);

Ext.onReady(function () {
	var theme = '', region = '';
	//var adminToolsSettings = adminToolsSettings || {config:{theme:'', region:'west'}};
	if (adminToolsSettings) {
		theme = adminToolsSettings.config.theme;
		region = adminToolsSettings.config.region;
	}
	if (theme) Ext.getBody().addClass(theme);
	if (region == 'east') {
		Ext.getBody().addClass('right-side-tree');
		var contentNode = Ext.get('modx-content'),
			actionButtonsNode = Ext.get('modx-action-buttons-container');
		if (actionButtonsNode) actionButtonsNode.appendTo(contentNode);
	}
	var Items = Ext.query('ul.modx-subsubnav');
	for (var i = 0; Items.length > i; i++) {
		Items[i].parentNode.classList.add('has-subnav');
	}
	// Package actions
	if (MODx.grid.Package) {
		Ext.override(MODx.grid.Package, {
			onClick: function (e) {
				var t = e.getTarget();
				var classes = t.className.split(' ');
				if (classes[0] == 'controlBtn') {
					var action = classes[1];
					var record = this.getSelectionModel().getSelected();
					var packageOptions = adminToolsPackageActions[record.data.name] || adminToolsPackageActions[record.data.name] || false;
					this.menu.record = record.data;
					if (packageOptions) {
						var message;
						[action, 'all'].every(function (item, i) {
							if (packageOptions[item] !== undefined) {
								if (Ext.isString(packageOptions[item])) {
									message = packageOptions[item];
								} else if (!packageOptions[item]) {
									message = packageOptions['message'] ? packageOptions['message'] : _('permission_denied');
								} else if (packageOptions[item]) {
									return false;
								}
							}
							if (message !== undefined) {
								Ext.MessageBox.alert(_('warning'), message);
								action = '';
								return false;
							}
							return true;
						});
					}
					switch (action) {
						case 'remove':
							this.remove(record, e);
							break;
						case 'install':
						case 'reinstall':
							this.install(record);
							break;
						case 'uninstall':
							this.uninstall(record, e);
							break;
						case 'update':
						case 'checkupdate':
							this.update(record, e);
							break;
						case 'details':
							this.viewPackage(record, e);
							break;
						default:
							break;
					}
				}
			}
		});
	}
});