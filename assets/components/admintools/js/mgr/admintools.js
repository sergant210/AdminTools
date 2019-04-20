let AdminTools = function (config) {
	config = config || {};
	AdminTools.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, toolbar: {},
	lock: function () {
		MODx.Ajax.request({
			url: adminToolsSettings.config.connector_url,
			params: {
				action: 'mgr/system/lock'
			},
			listeners: {
				success: {
					fn: function(response) {
						document.location.reload();
					},
					scope: this
				}
			}
		});
	},
	setTimeout: function (time) {
		return setTimeout(function () {
			AdminTools.lock();
		}, time)
	},
	syncMessageCounter: function () {
		if (AdminTools.messageCounterEl === undefined) {
			AdminTools.messageCounterEl = document.getElementById('message-counter');
		}
		let counter = this.getMessageCounter();
		AdminTools.messageCounterEl.innerText = (counter == 0) ? '' : counter;
	},
	getMessageCounter: function () {
		return adminToolsSettings.config.messages;
	},
	increaseMessageCounter: function () {
		adminToolsSettings.config.messages++;
		this.syncMessageCounter();
		return adminToolsSettings.config.messages;
	},
	decreaseMessageCounter: function () {
		adminToolsSettings.config.messages--;
		if (adminToolsSettings.config.messages < 0) {
			adminToolsSettings.config.messages = 0;
		}
		this.syncMessageCounter();
		return adminToolsSettings.config.messages;
	},
});
Ext.reg('admintools', AdminTools);

AdminTools = new AdminTools();

AdminTools.utils.renderBoolean = function (value, props, row) {
	return value
		? String.format('<span class="green">{0}</span>', _('yes'))
		: String.format('<span class="red">{0}</span>', _('no'));
};

AdminTools.utils.renderPrincipalType = function (value, props, row) {
	let output;
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
	let menu = [];
	let cls, icon, title, action = '';

	for (let i in actions) {
		if (!actions.hasOwnProperty(i)) {
			continue;
		}

		let a = actions[i];
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
	let res = [];
	let cls, icon, title, action, item = '';
	for (let i in row.data.actions) {
		if (!row.data.actions.hasOwnProperty(i)) {
			continue;
		}
		let a = row.data.actions[i];
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
	let theme = '', region = '';
	//let adminToolsSettings = adminToolsSettings || {config:{theme:'', region:'west'}};
	if (adminToolsSettings) {
		theme = adminToolsSettings.config.theme;
		region = adminToolsSettings.config.region;
	}
	if (theme) Ext.getBody().addClass(theme);
	if (region == 'east') {
		Ext.getBody().addClass('right-side-tree');
		let contentNode = Ext.get('modx-content'),
			actionButtonsNode = Ext.get('modx-action-buttons-container');
		if (actionButtonsNode) actionButtonsNode.appendTo(contentNode);
	}
	let Items = Ext.query('ul.modx-subsubnav');
	for (let i = 0; Items.length > i; i++) {
		Items[i].parentNode.classList.add('has-subnav');
	}
	// Lock
	if (adminToolsSettings.config.show_lockmenu > 0) {
		let userMenuList = document.querySelector('#limenu-user ul.modx-subnav');
		let newLi = document.createElement('li');
		newLi.id = 'admintools-lock';
		newLi.innerHTML = '<a href="javascript:AdminTools.lock()">' + _('admintools_lock') + ' <span class="description">' + _('admintools_lock_desc') + '</span></a>';

		setTimeout(function () {
			if (userMenuList) userMenuList.insertBefore(newLi, userMenuList.lastChild);
		}, 300);
	}
	if (adminToolsSettings.config.lock_timeout > 0) {
		let lockTimeout = AdminTools.setTimeout(adminToolsSettings.config.lock_timeout);
		['mousemove','keydown','wheel','click','contextmenu'].forEach(function(event) {
			Ext.select('body').on(event, function(e) {
				clearTimeout(lockTimeout);
				lockTimeout = AdminTools.setTimeout(adminToolsSettings.config.lock_timeout);
			});

		});
	}

	// Messages
	if (adminToolsSettings.config.messages >= 0) {
		Ext.ComponentMgr.onAvailable('modx-grid-message', function () {
			this.getStore().on("update", function (g, p, o) {
				if (o == 'commit') {
					if (p.data.read) {
						AdminTools.decreaseMessageCounter();
					} else {
						AdminTools.increaseMessageCounter();
					}
				}
			}, this);
			this.on('afterRemoveRow', function (r) {
				AdminTools.decreaseMessageCounter();
			})
		});
		let userMenu = document.querySelector('#limenu-user > a');
		let newSpan = document.createElement('span');
		newSpan.id = 'message-counter';
		newSpan.className = 'badge';
		newSpan.innerText = AdminTools.getMessageCounter() ? AdminTools.getMessageCounter() : '';

		setTimeout(function () {
			if (userMenu) userMenu.appendChild(newSpan);
		}, 300);
	}

	// Package actions
	if (MODx.grid.Package) {
		Ext.override(MODx.grid.Package, {
			onClick: function (e) {
				let t = e.getTarget();
				let classes = t.className.split(' ');

				if (classes[0] == 'controlBtn') {
					let action = classes[1];
					let record = this.getSelectionModel().getSelected();
					let packageOptions = adminToolsPackageActions[record.data.name] || adminToolsPackageActions[record.data.name] || false;

					this.menu.record = record.data;
					if (packageOptions) {
						let message;
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