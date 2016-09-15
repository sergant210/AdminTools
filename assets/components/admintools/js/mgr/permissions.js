AdminTools.window.Permissions = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-window-permissions';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		action: config.action,
		title: _('admintools_permissions'),
		width: 500,
		autoHeight: true,
		//stateful: true,
		modal: true,
		maximizable: false,
		fields: [{
			xtype: 'hidden',
			name: 'id',
			id: config.id + '-id'
		}, {
			xtype: 'hidden',
			name: 'rid',
			id: config.id + '-rid'
		}, {
			xtype: 'admintools-combo-principals',
			fieldLabel: _('admintools_principal_name'),
			name: 'principal',
			id: config.id + '-principal',
			anchor: '100%',
			allowBlank: false,
			listeners: {'select': {fn: function() {
				var pricipalId = Ext.getCmp('admintools-window-permissions-principal').getValue();
				if (pricipalId.indexOf('grp') == 0) {
					Ext.getCmp('admintools-window-permissions-priority').enable();
				} else {
					Ext.getCmp('admintools-window-permissions-priority').disable();
				}

			}, scope: this}}
		}, {
			xtype: 'textfield',
			fieldLabel: _('admintools_priority'),
			name: 'priority',
			id: config.id + '-priority',
			disabled: true,
			anchor: '100%',
			allowBlank: true
		}, {
			xtype: 'radiogroup',
			fieldLabel: _('admintools_permissions_choose_action'),
			columns: 2,
			items: [{
				id: config.id + '-status-a',
				name: 'status',
				boxLabel: _('admintools_permissions_allow'),
				xtype: 'radio',
				value: 1,
				inputValue: 1,
				checked: config.status
			},{
				id: config.id + '-status-d',
				name: 'status',
				boxLabel: _('admintools_permissions_deny'),
				xtype: 'radio',
				value: 0,
				inputValue: 0,
				checked: !config.status
			}]
		}]
	});
	AdminTools.window.Permissions.superclass.constructor.call(this, config);
	this.on('render', function() {
		var principal = Ext.getCmp('admintools-window-permissions-principal'),
			pricipalId = principal.getValue();
		principal.focus(false, 300);
		if (pricipalId.indexOf('grp') == 0) Ext.getCmp('admintools-window-permissions-priority').enable();

	},this);
};
Ext.extend(AdminTools.window.Permissions, MODx.Window);
Ext.reg('admintools-window-permissions', AdminTools.window.Permissions);

/**************************************************************/

AdminTools.grid.Permissions = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-table-permissions';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/permissions/getlist',
			resource: config.resource
		},
		//sm: new Ext.grid.CheckboxSelectionModel(),
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0
		},
		fields: ['id','principal_name','principal_type','priority','status','actions'],
		columns: [{
			header: 'ID',
			dataIndex: 'id',
			width: 50,
			hidden: true
		}, {
			header: _('admintools_principal_type'),
			dataIndex: 'principal_type',
			sortable: false,
			renderer: AdminTools.utils.renderPrincipalType,
			fixed: true,
			width: 30
		}, {
			header: _('admintools_principal_name'),
			dataIndex: 'principal_name',
			sortable: false,
			width: 150
		}, {
			header: _('admintools_priority'),
			dataIndex: 'priority',
			sortable: false,
			width: 80
		}, {
			header: _('admintools_permissions_status_column'),
			dataIndex: 'status',
			renderer: AdminTools.utils.renderBoolean,
			sortable: false,
			width: 100
		}, {
			header: '<i class="icon icon-cog"></i>',
			dataIndex: 'actions',
			renderer: AdminTools.utils.renderActions,
			sortable: false,
			width: 70,
			fixed: true,
			id: 'actions'
		}],
		tbar: [{
			text: '<i class="icon icon-plus"></i>&nbsp;' + _('admintools_add_permission'),
			handler: this.addPermission,
			scope: this
		}],
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updatePermission(grid,e,row);
			}
		},
		height: '100%',
		paging: true,
		pageSize: 10,
		remoteSort: true
	});
	AdminTools.grid.Permissions.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.grid.Permissions, MODx.grid.Grid, {
	getMenu: function (grid, rowIndex) {
		var ids = this._getSelectedIds();

		var row = grid.getStore().getAt(rowIndex);
		var menu = AdminTools.utils.getMenu(row.data['actions'], this, ids);

		this.addContextMenuItem(menu);
	},
	addPermission: function (btn, e) {
		var w = MODx.load({
			xtype: 'admintools-window-permissions',
			action: 'mgr/permissions/add',
			status: true,
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					}, scope: this
				},
				hide: {
					fn: function () {
						setTimeout(function () {
							w.destroy()
						}, 200);
					}
				}
			}
		});
		w.reset();
		w.setValues({principal:'all-0', priority:1, rid:MODx.request.id});
		w.show(e.target);
	},
	updatePermission: function(o,e,row) {
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var id = this.menu.record.id;

		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/permissions/get',
				id: id
			},
			listeners: {
				success: {
					fn: function (r) {
						var w = MODx.load({
							xtype: 'admintools-window-permissions',
							action: 'mgr/permissions/update',
							record: r,
							status: r.object.status,
							listeners: {
								success: {
									fn: function () {
										this.refresh();
									}, scope: this
								},
								hide: {
									fn: function () {
										setTimeout(function () {
											w.destroy()
										}, 200);
									}
								}
							}
						});
						w.reset();
						w.setValues(r.object);
						w.show(e.target);
					}, scope: this
				}
			}
		});
	},
	removePermission: function () {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}
		MODx.msg.confirm({
			title: _('admintools_permission_remove'),
			text: _('admintools_permission_remove_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/permissions/remove',
				ids: Ext.util.JSON.encode(ids)
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
					}, scope: this
				}
			}
		});
		return true;
	},
	onClick: function (e) {
		var elem = e.getTarget();
		if (elem.nodeName == 'BUTTON') {
			var row = this.getSelectionModel().getSelected();
			if (typeof(row) != 'undefined') {
				var action = elem.getAttribute('action');
				if (action == 'showMenu') {
					var ri = this.getStore().find('id', row.id);
					return this._showMenu(this, ri, e);
				}
				else if (typeof this[action] === 'function') {
					this.menu.record = row.data;
					return this[action](this, e);
				}
			}
		}
		return this.processEvent('click', e);
	},
	_getSelectedIds: function () {
		var ids = [];
		var selected = this.getSelectionModel().getSelections();
		for (var i in selected) {
			if (!selected.hasOwnProperty(i)) {
				continue;
			}
			ids.push(selected[i]['id']);
		}
		return ids;
	}
});
Ext.reg('admintools-grid-permissions', AdminTools.grid.Permissions);

/** ********************************************  **/
AdminTools.combo.Principals = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'principal',
		hiddenName: 'principal',
		pageSize: 20,
		emptyText: _('admintools_choose_principal'),
		fields: ['id','name','class','icon'],
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/permissions/getprincipals'
		},
		typeAhead: true,
		autoSelect: false,
		editable: true,
		tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item {class}">{icon}{name}</div></tpl>')
	});
	AdminTools.combo.Principals.superclass.constructor.call(this,config);
};
Ext.extend(AdminTools.combo.Principals,MODx.combo.ComboBox);
Ext.reg('admintools-combo-principals',AdminTools.combo.Principals);