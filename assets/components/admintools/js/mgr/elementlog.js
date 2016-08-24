AdminTools.window.lastEditedElements = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-led-window';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		title: _('admintools_last_edited'),
		width: 1000,
		maxHeight: 800,
		height: 700,
		//autoHeight: true,
		stateful: true,
		//layout: 'fit',
		modal: true,
		maximizable: false,
		items: [{
			xtype: 'admintools-led-elements-grid'
		}],
		buttons: [{
			text: _('admintools_close'),
			//id: 'admintools-led-window-close-btn',
			handler: function(){this.hide();},
			scope: this
		}]
	});
	AdminTools.window.lastEditedElements.superclass.constructor.call(this, config);
	/*this.on('show',function() {
		this.center();
	},this);*/
};
Ext.extend(AdminTools.window.lastEditedElements, MODx.Window);
Ext.reg('admintools-led-window', AdminTools.window.lastEditedElements);

/**************************************************************/

AdminTools.grid.lastEditedElements = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-led-elements-table';
	}

	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/lastedited/getlist'
		},
		primaryKey: 'key',
		//sm: new Ext.grid.CheckboxSelectionModel(),
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0
		},
		fields: ['key','classKey','name','item','occurred','username','actions'],
		columns: [{
			header: 'Key',
			dataIndex: 'key',
			width: 50,
			hidden: true
		}, {
			header: 'ID',
			dataIndex: 'item',
			fixed: true,
			width: 50
		}, {
			header: _('admintools_type'),
			dataIndex: 'classKey',
			sortable: true,
			width: 70
		}, {
			header: _('admintools_name'),
			dataIndex: 'name',
			sortable: false,
			width: 200
		}, {
			header: _('admintools_date'),
			dataIndex: 'occurred',
			sortable: true,
			width: 80
		}, {
			header: _('admintools_user'),
			dataIndex: 'username',
			sortable: true,
			width: 100
		}, {
			header: '<i class="icon icon-cog"></i>',
			dataIndex: 'actions',
			renderer: AdminTools.utils.renderActions,
			sortable: false,
			width: 40,
			fixed: true,
			id: 'actions'
		}],
		tbar: [{
			xtype: 'modx-combo-user',
			id: 'log-filter-user-id',
			//fieldLabel: _('user'),
			emptyText: _('user'),
			width: 200,
			listeners: {
				'select': {fn: this._doSearch, scope: this}
			}
		},{
			xtype: 'datefield',
			emptyText: _('date_start'),
			id: 'log-filter-datestart',
			allowBlank: true,
			format: 'd.m.Y',
			startDay: 1,
			width: 140,
			listeners: {
				'select': {fn: this._doSearch, scope: this},
				'render': {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		},{
			xtype: 'datefield',
			emptyText: _('date_end'),
			id: 'log-filter-dateend',
			format: 'd.m.Y',
			startDay: 1,
			allowBlank: true,
			width: 140,
			listeners: {
				'select': {fn: this._doSearch, scope: this},
				'render': {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'textfield',
			name: 'query',
			width: 200,
			id: 'log-filter-query-field',
			emptyText: _('admintools_element_name'),
			listeners: {
				'render': {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'button',
			id: config.id + '-search-clear',
			text: '<i class="icon icon-times"></i>',
			listeners: {
				click: {fn: this._clearSearch, scope: this}
			}
		}],
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.openElement(grid,e,row);
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
	openElement: function(o,e,row) {
		//row = row || this.getSelectionModel().getSelected();
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var grid = this,
			type = this.menu.record.classKey.toLowerCase().substring(3);
		if (type == 'templatevar') type = 'tv';

		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/lastedited/verify',
				classKey: this.menu.record.classKey,
				id: this.menu.record.item
			},
			listeners: {
				success: {
					fn: function(r) {
						MODx.loadPage('element/'+ type +'/update', 'id='+ this.menu.record.item);
					}
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
	},
	_doSearch: function (tf, nv, ov) {
		this.getStore().baseParams.user = Ext.getCmp('log-filter-user-id').getValue();
		this.getStore().baseParams.query = Ext.getCmp('log-filter-query-field').getValue();
		this.getStore().baseParams.datestart = Ext.getCmp('log-filter-datestart').getValue();
		this.getStore().baseParams.dateend = Ext.getCmp('log-filter-dateend').getValue();
		//this.getStore().baseParams.query = tf.getValue();
		this.getBottomToolbar().changePage(1);
		//this.refresh();
	},

	_clearSearch: function (btn, e) {
		this.getStore().baseParams.query = '';
		this.getStore().baseParams.user = '';
		this.getStore().baseParams.datestart = '';
		this.getStore().baseParams.dateend = '';
		Ext.getCmp('log-filter-user-id').setValue('');
		Ext.getCmp('log-filter-query-field').setValue('');
		Ext.getCmp('log-filter-datestart').setValue('');
		Ext.getCmp('log-filter-dateend').setValue('');
		this.getBottomToolbar().changePage(1);
		//this.refresh();
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
			//closeAction: 'close',
			listeners: {
				hide: {fn: function () {setTimeout(function(){w.destroy()},200);}}
			}
		});
		w.show(Ext.EventObject.target,function() {
			Ext.isSafari ? w.setPosition(null,30) : w.setPosition(null,60);
		},this);
	};
	var arr = [];
	arr.push({xtype:'tbfill'});
	arr.push({
		cls: 'tree-last-edited',
		tooltip: {text: _('admintools_last_edited')},
		scope: tree,
		handler: function() {
			tree.showLastEditedElements();
		}
	});
	tbar.addButton(arr);
	tbar.doLayout();
});