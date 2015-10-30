AdminTools.window.lastEditedElements = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-led-window';
	}
	Ext.applyIf(config, {
		title: _('admintools_last_edited'),
		width: 1000,
		maxHeight: 800,
		height: 500,
		autoHeight: true,
		//stateful: false,
		//layout: 'fit',
		modal: true,
		maximizable: false,
		items: [{
			xtype: 'admintools-led-elements-grid'
		}],
		buttons: [{
			text: _('admintools_clear'),
			//id: 'admintools-led-window-btn',
			handler: function () {
				this.clearList();
			},
			scope: this
		}, {
			text: _('admintools_close'),
			//id: 'admintools-led-window-close-btn',
			handler: function(){this.hide();},
			scope: this
		}]
	});
	AdminTools.window.lastEditedElements.superclass.constructor.call(this, config);
	this.on('show',function() {
		this.center();
	},this);
};
Ext.extend(AdminTools.window.lastEditedElements, MODx.Window, {
	clearList: function () {
		MODx.msg.confirm({
			title: _('admintools_clear'),
			text: _('admintools_item_remove_all_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/lastedited/removeall'
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
					}, scope: this
				}
			}
		});
	}
});
Ext.reg('admintools-led-window', AdminTools.window.lastEditedElements);

/**************************************************************/

AdminTools.grid.lastEditedElements = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-led-elements-table';
	}
	this.sm = new Ext.grid.CheckboxSelectionModel();
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/lastedited/getlist'
		},
		primaryKey: 'key',
		sm: this.sm,
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0
		},
		fields: ['key','type','name','eid','editedon','user','actions'],
		columns: [this.sm,{
			header: 'Key',
			dataIndex: 'key',
			width: 50,
			hidden: true
		}, {
			header: 'ID',
			dataIndex: 'eid',
			fixed: true,
			width: 50
		}, {
			header: _('admintools_type'),
			dataIndex: 'type',
			sortable: false,
			width: 70
		}, {
			header: _('admintools_name'),
			dataIndex: 'name',
			sortable: false,
			width: 200
		}, {
			header: _('admintools_date'),
			dataIndex: 'editedon',
			sortable: false,
			width: 80
		}, {
			header: _('admintools_user'),
			dataIndex: 'user',
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
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.openElement(row);
			}
		},
		height: '100%',
		paging: true,
		pageSize: 10,
		remoteSort: true
	});
	AdminTools.grid.lastEditedElements.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.grid.lastEditedElements, MODx.grid.Grid, {
	getMenu: function (grid, rowIndex) {
		var ids = this._getSelectedIds();

		var row = grid.getStore().getAt(rowIndex);
		var menu = AdminTools.utils.getMenu(row.data['actions'], this, ids);

		this.addContextMenuItem(menu);
	},
	openElement: function(row) {
		row = row || this.getSelectionModel().getSelected();
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var grid = this;
		//MODx.loadPage();
		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/lastedited/verify',
				type: row.data.type,
				id: row.data.eid
			},
			listeners: {
				success: {
					fn: function(r) {
						location.href = 'index.php?a=element/'+row.data.type+'/update&id=' + row.data.eid;					}
					,scope: this
				}
				,failure: {
					fn: function(r) {
						var oldFn = MODx.form.Handler.showError;

						MODx.form.Handler.showError =  function(message) {
							if (message === '') {
								MODx.msg.hide();
							} else {
								Ext.MessageBox.show({
									title: _('error'),
									msg: message,
									buttons: Ext.MessageBox.OK,
									fn: function(btn) {
										grid.refresh();
										MODx.form.Handler.showError = oldFn;
									}
								});
							}
						};
					}
					,scope: this
				}
			}
		});
	},
	removeItem: function () {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}
		MODx.msg.confirm({
			title: _('admintools_item_remove'),
			text: _('admintools_item_remove_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/lastedited/remove',
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
					var ri = this.getStore().find('eid', row.data.eid);
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
			ids.push(selected[i]['data']['key']);
		}

		return ids;
	}
});
Ext.reg('admintools-led-elements-grid', AdminTools.grid.lastEditedElements);

/** ******************************** **/

Ext.onReady(function () {
	var tree = Ext.getCmp('modx-tree-element'),
		tbar = tree.topToolbar;

	tree.showLastEditedElements = function(){
		var w = MODx.load({
			xtype: 'admintools-led-window',
			id: Ext.id(),
			//record: r,
			listeners: {
				success: {
					fn: function () {
						//this.refresh();
					}, scope: this
				}
			}
		});
		w.show(Ext.EventObject.target);
	};
	var arr = [];
	//arr.push('->');
	arr.push({
		cls: 'tree-last-edited'
		, tooltip: {text: _('admintools_last_edited')}
		, scope: tree
		,handler: function() {
			tree.showLastEditedElements();
		}
	});
	tbar.addSeparator();
	tbar.addButton(arr);
	tbar.doLayout();
});