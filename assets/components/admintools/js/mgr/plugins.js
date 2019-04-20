AdminTools.window.Plugins = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-plugins-window';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		title: _('plugins'),
		width: 1000,
		maxHeight: 800,
		height: 700,
		//autoHeight: true,
		stateful: true,
		//layout: 'fit',
		modal: true,
		maximizable: false,
		items: [{
			xtype: 'admintools-plugins-grid'
		}],
		buttons: [{
			text: _('admintools_close'),
			handler: function(){this.hide();},
			scope: this
		}]
	});
	AdminTools.window.Plugins.superclass.constructor.call(this, config);
	/*this.on('show',function() {
	 this.center();
	 },this);*/
};
Ext.extend(AdminTools.window.Plugins, MODx.Window);
Ext.reg('admintools-plugins-window', AdminTools.window.Plugins);

AdminTools.window.BindPlugin = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-bind-plugin-window';
	}
	Ext.applyIf(config, {
		title: _('admintools_plugin_bind'),
		width: 400,
		autoHeight: true,
		modal: true,
		maximizable: false,
		url: adminToolsSettings.config.connector_url,
		action: 'mgr/plugins/bind',
		fields: [{
			xtype: 'admintools-combo-plugins',
			fieldLabel: _('admintools_plugin'),
			id: config.id + '-plugin',
			anchor: '100%',
			allowBlank: false
		}, {
			xtype: 'admintools-combo-events',
			fieldLabel: _('admintools_event'),
			name: 'event',
			id: config.id + '-event',
			anchor: '100%',
			allowBlank: false
		}, {
			xtype: 'numberfield',
			fieldLabel: _('admintools_priority'),
			name: 'priority',
			id: config.id + '-priority',
			anchor: '100%',
			allowBlank: true
		}],
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	AdminTools.window.BindPlugin.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.window.BindPlugin, MODx.Window);
Ext.reg('admintools-bind-plugin-window', AdminTools.window.BindPlugin);

/** Plugins Table **/
AdminTools.grid.Plugins = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-plugins-grid';
	}

	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/plugins/getlist'
		},
		primaryKey: 'key',
		autosave: true,
		//sm: new Ext.grid.CheckboxSelectionModel(),
		fields: ['key','id','event','name','priority','catName','active','description','actions'],
		columns: [{
			header: 'Key',
			dataIndex: 'key',
			width: 80,
			hidden: true
		}, {
/*			header: 'ID',
			dataIndex: 'id',
			width: 80,
			fixed: false
		}, {*/
			header: _('admintools_event'),
			dataIndex: 'event',
			sortable: false,
			// hidden: true,
			width: 150
		}, {
			header: _('admintools_plugin'),
			dataIndex: 'name',
			sortable: false,
			width: 150
		}, {
			header: _('admintools_category'),
			dataIndex: 'catName',
			sortable: false,
			menuDisabled: true,
			width: 150
		}, {
			header: _('admintools_priority'),
			dataIndex: 'priority',
			sortable: false,
			editable: true,
			editor:	{xtype: 'numberfield'},
			menuDisabled: true,
			width: 90
		}, {
			header: _('admintools_active'),
			dataIndex: 'active',
			renderer: AdminTools.utils.renderBoolean,
			fixed: true,
			menuDisabled: true,
			width: 90
		}, {
			header: '<i class="icon icon-cog"></i>',
			dataIndex: 'actions',
			renderer: AdminTools.utils.renderActions,
			sortable: false,
			width: 140,
			fixed: true,
			menuDisabled: true,
			id: 'actions'
		}],
		tbar: new Ext.Toolbar({
			style: {paddingRight:'20px'},
			items: [{
				text: '<i class="icon icon-plus"></i>',
				handler: this.createPlugin,
				scope: this
			}, {
				text: '',
				iconCls: 'icon-columns',
				tooltip: _('admintools_change_view'),
				// tooltipType: 'title',
				style: {width: '15px'},
				handler: function (b,e) {
					this.store.groupField = this.store.groupField == 'event' ? 'name' : 'event';
					this.store.sortInfo = {field: this.store.groupField, direction: 'ASC'};
					this.store.load();
					Ext.getCmp('admintools-view-toggle-btn').setIconClass('icon-plus-square-o');
					Ext.getCmp('admintools-view-toggle-btn').collapsed = false;
				},
				scope: this
			}, {
				id: 'admintools-view-toggle-btn',
				text: '',
				iconCls: 'icon-plus-square-o',
				tooltip: _('admintools_collapse_all'),
				// tooltipType: 'title',
				handler: this._toggleCollapsible,
				collapsed: false,
				style: {width: '15px'},
				scope: this
			}, '->', {
				xtype: 'trigger',
				onTriggerClick: this._clearEvent,
				triggerClass: 'x-field-trigger-clear',
				name: 'event',
				width: 200,
				id: 'admintools-filter-event-field',
				emptyText: _('admintools_event'),
				listeners: {
					render: {
						fn: function (tf) {
							tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
								this._doSearch();
							}, this);
						}, scope: this
					},
					clearEvent: {
						fn: function(field) {
							field.setValue('');
							this._clearSearch(field, 'event');
						}, scope: this
					}
				}
			}, {
				xtype: 'trigger',
				onTriggerClick: this._clearQuery,
				triggerClass: 'x-field-trigger-clear',
				name: 'query',
				width: 200,
				id: 'admintools-filter-query-field',
				emptyText: _('admintools_plugin_category'),
				style: {marginRight:'15px'},
				listeners: {
					render: {
						fn: function (tf) {
							tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
								this._doSearch();
							}, this);
						}, scope: this
					},
					clearQuery: {
						fn: function(field) {
							field.setValue('');
							this._clearSearch(field, 'query');
						}, scope: this
					}
				}
			}]
		}),
		listeners: {
			/*rowDblClick: function (grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.openElement(grid,e,row);
			}*/
		},
		height: '100%',
		width: '100%',
		paging: false,
		pageSize: 0,
		remoteSort: true,
		//
		singleText: _('admintools_element'),
		pluralText: _('admintools_elements'),
		grouping: true,
		groupBy: 'event',
		sortBy: 'event',
		sortDir: 'ASC',
		tools: [{
			id: 'plus',
			qtip: _('admintools_expand_all'),
			handler: this.expandAll,
			scope: this
		},{
			id: 'minus',
			hidden: true,
			qtip: _('admintools_collapse_all'),
			handler: this.collapseAll,
			scope: this
		}],
		groupingConfig: {
			emptyText: 'Empty',
			// groupOnSort: false,
			hideGroupedColumn: true,
			forceFit: true,
			autoFill: true,
			showPreview: true,
			enableRowBody: false,
			scrollOffset: 0
		}
	});
	AdminTools.grid.Plugins.superclass.constructor.call(this, config);
	if (config.autosave) {
		this.on('afteredit',this.saveRecord,this);
	}
};
Ext.extend(AdminTools.grid.Plugins, MODx.grid.Grid, {
	getMenu: function (grid, rowIndex) {
		var ids = this._getSelectedIds();

		var row = grid.getStore().getAt(rowIndex);
		var menu = AdminTools.utils.getMenu(row.data['actions'], this, ids);

		this.addContextMenuItem(menu);
	},
	saveRecord: function(e) {
		var oldValue = +e.originalValue,
			newValue = e.value;
		if (oldValue==newValue || newValue === '') {
			e.record.reject();
			return false;
		}
		var id = e.record.data.key;
		id = id.substring(id.indexOf('_') + 1);
		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/plugins/updatefromgrid',
				priority: newValue,
				id: id,
				event: e.record.data.event
			},
			listeners: {
				success: {
					fn: function(r) {
						e.record.commit();
						this.refresh();
						this.fireEvent('afterAutoSave',r);
					}
					,scope: this
				}
				,failure: {
					fn: function(r) {
						e.record.reject();
						this.fireEvent('afterAutoSave', r);
					}
					,scope: this
				}
			}
		});
	},
	createPlugin: function (btn, e) {
		var w = MODx.load({
			xtype: 'admintools-quick-create-plugin',
			listeners: {
				success: {
					fn: function () {
						AdminTools.treePluginNode.reload();
						this.refresh();
					}, scope: this
				},
				hide: {
					fn: function () {
						setTimeout(function (){w.destroy();},200);
					}, scope: this
				}
			}
		});
		w.reset();
		w.setValues({plugincode:"<?php\n",clearCache:true,formCode:true});
		w.show(e.target);
	},
	bindEvent: function (g,e) {
		var id = this.getSelectionModel().getSelected().data.key;
		id = id.substring(id.indexOf('_') + 1);
		this.bindAll(this, e, {pluginid:id, priority:0});
	},
	bindPlugin: function (g,e) {
		this.bindAll(this, e, {event:this.getSelectionModel().getSelected().data.event, priority:0});
	},
	bindAll: function (obj, e, data) {
		var w = MODx.load({
			xtype: 'admintools-bind-plugin-window',
			listeners: {
				success: {
					fn: function () {
						this.refresh();
					}, scope: this
				},
				hide: {
					fn: function () {
						setTimeout(function (){w.destroy();},200);
					}, scope: this
				}
			}
		});
		w.reset();
		w.setValues(data);
		w.show(e.target);
	},
	disablePlugin: function (act, btn, e) {
		var id = this.getSelectionModel().getSelected().data.key;
		id = id.substring(id.indexOf('_') + 1);
		MODx.Ajax.request({
			url: MODx.config.connectors_url,
			params: {
				action: 'element/plugin/deactivate',
				id: id
			},
			listeners: {
				success: {
					fn: function () {
						this.refresh();
						//Ext.getCmp('modx-tree-element').refreshParentNode();
						AdminTools.treePluginNode.reload();
					}, scope: this
				}
			}
		})
	},

	enablePlugin: function (act, btn, e) {
		var id = this.getSelectionModel().getSelected().data.key;
		id = id.substring(id.indexOf('_') + 1);
		MODx.Ajax.request({
			url: MODx.config.connectors_url,
			params: {
				action: 'element/plugin/activate',
				id: id
			},
			listeners: {
				success: {
					fn: function () {
						this.refresh();
						AdminTools.treePluginNode.reload();
						// Ext.getCmp('modx-tree-element').refreshParentNode();
					}, scope: this
				}
			}
		})
	},
	unbindPlugin: function (act, btn, e) {
		var data = this.getSelectionModel().getSelected().data,
			id = data.key,
			event = data.event;
		id = id.substring(id.indexOf('_') + 1);
		MODx.msg.confirm({
			title: _('warning'),
			text: _('admintools_plugin_unbind_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/plugins/unbind',
				id: id,
				event: event
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
	removePlugin: function (act, btn, e) {
		var data = this.getSelectionModel().getSelected().data,
			id = data.key;
		id = id.substring(id.indexOf('_') + 1);
		MODx.msg.confirm({
			title: _('warning'),
			text: _('admintools_plugin_remove_confirm'),
			url: MODx.config.connectors_url,
			params: {
				action: 'element/plugin/remove',
				id: id
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
						AdminTools.treePluginNode.reload();
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
					var ri = this.getStore().find('key', row.data.key);
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
		this.getStore().baseParams.event = Ext.getCmp('admintools-filter-event-field').getValue();
		this.getStore().baseParams.query = Ext.getCmp('admintools-filter-query-field').getValue();
		// this.getBottomToolbar().changePage(1);
		this.refresh();
	},
	_clearEvent: function () {
		this.fireEvent('clearEvent', this);
	},
	_clearQuery: function () {
		this.fireEvent('clearQuery', this);
	},
	_clearSearch: function (field, param) {
		field.setValue('');
		this.getStore().baseParams[param] = '';
		this.refresh();
	},
	_toggleCollapsible: function (btn, e) {
		if (btn.collapsed) {
			btn.setIconClass('icon-plus-square-o');
			btn.setTooltip(_('admintools_collapse_all'));
			this.view.expandAllGroups();
			btn.collapsed = false;
		} else {
			btn.setIconClass('icon-minus-square-o');
			btn.setTooltip(_('admintools_expand_all'));
			this.view.collapseAllGroups();
			btn.collapsed = true;
		}
	}
});
Ext.reg('admintools-plugins-grid', AdminTools.grid.Plugins);


Ext.onReady(function () {
	function showEventPluginButton() {
		if (typeof tree.nodeHash.n_type_plugin == 'undefined') return;
		var elId = tree.nodeHash.n_type_plugin.ui.elNode.id + '_tools';
		if (document.getElementById(elId)) {
			AdminTools.treePluginNode = tree.nodeHash.n_type_plugin;
			var btn = MODx.load({
				xtype: 'modx-button',
				text: '',
				tooltip: new Ext.ToolTip({
					title: _('admintools_events'),
					target: this
				}),
				node: AdminTools.treePluginNode,
				enableToggle: true,
				handler: function (btn, e) {
					e.stopEvent();
					var w = MODx.load({
						xtype: 'admintools-plugins-window',
						listeners: {
							hide: {
								fn: function () {
									setTimeout(function () {
										w.destroy()
									}, 200);
								}
							}
						}
					});
					w.show(Ext.EventObject.target, function () {
						Ext.isSafari ? w.setPosition(null, 30) : w.setPosition(null, 60);
					}, w);
				},
				iconCls: 'icon-list',
				listeners: {
					mouseover: function (button, e) {
						button.tooltip.onTargetOver(e);
					},
					mouseout: function (button, e) {
						button.tooltip.onTargetOut(e);
					}
				}
			});
			btn.render(elId, 0);
			clearInterval(interval);
		}
	}
	var tree = Ext.getCmp('modx-tree-element');
	if (tree) {
		if (tree.rendered) {
			var interval = setInterval(showEventPluginButton, 500);
		} else {
			tree.on('render', function () {
				setTimeout(showEventPluginButton, 500);
			});
		}
	}
});

/**************** Dialog **************************/
AdminTools.window.QuickCreatePlugin = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		title: _('quick_create_plugin'),
		width: 1000,
		autoHeight: true,
		layout: 'anchor',
		modal: true,
		stateful: false,
		url: adminToolsSettings.config.connector_url,
		action: 'mgr/plugins/create',
		fields: [{
			xtype: 'hidden',
			name: 'id'
		},{
			xtype: 'hidden',
			name: 'events'
		},{
			layout: 'column',
			border: false,
			defaults: {
				layout: 'form',
				labelAlign: 'top',
				anchor: '100%',
				border: false,
				style: {padding: '15px 0'},
				labelSeparator: ''
			},
			items: [{
				columnWidth: .5,
				items: [{
					xtype: 'textfield',
					name: 'name',
					fieldLabel: _('name'),
					anchor: '100%'
				}, {
					xtype: 'textarea',
					name: 'description',
					fieldLabel: _('description'),
					anchor: '100%',
					height: 92
					//,rows: 2
				}, {
					xtype: 'admintools-combo-events',
					fieldLabel: _('admintools_event'),
					name: 'event',
					id: 'admintools-combo-events',
					anchor: '100%',
					allowBlank: true,
					triggerConfig: {
						tag: 'span',
						cls: 'x-field-combo-btns',
						cn: [
							{tag: 'div', cls: 'x-form-trigger x-field-combo-events'},
							{tag: 'div', cls: 'x-form-trigger x-field-combo-event-add'}
						]
					},
					onTriggerClick: function(event, btn){
						if (btn && Ext.get(btn).hasClass('x-field-combo-event-add')) {
							this.addEventElement();
						} else {
							AdminTools.combo.Events.superclass.onTriggerClick.call(this);
						}
					},
					addEventElement: function (event) {
						event = event || Ext.getCmp('admintools-combo-events').getValue();
						if (!event) return;
						var span = Ext.DomHelper.createDom({tag:'span',id:'event-'+event,cls:'x-superboxselect-item',html:event}),
							a = Ext.DomHelper.createDom({tag:'a',cls:'x-superboxselect-item-close',href:'#'});
						a.addEventListener('click', function (e) {
							this.parentNode.remove();
						});
						span.appendChild(a);
						if (!document.getElementById('event-'+event)) {
							document.getElementById('admintools-plugin-events').appendChild(span);
						}
					},
					listeners: {
						render: {
							fn: function (c) {
								var self = this;
								c.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
									var event = c.getValue();
									if (!event || Ext.getCmp('admintools-combo-events').isExpanded()) return;
									Ext.getCmp('admintools-combo-events').addEventElement(event);
								}, this);
							}, scope: this
						}
					}
				}]
			},{
				columnWidth: .5,
				items: [{
					xtype: 'modx-combo-category',
					name: 'category',
					fieldLabel: _('category'),
					anchor: '100%'
				},{
					xtype: MODx.expandHelp ? 'label' : 'hidden',
					html: _('plugin_desc_category'),
					cls: 'desc-under',
					style: {marginBottom: '10px'}
				},{
					xtype: 'xcheckbox',
					name: 'disabled',
					boxLabel: _('disabled'),
					hideLabel: true,
					inputValue: 1,
					checked: false
				}, {
					xtype: 'xcheckbox',
					name: 'clearCache',
					hideLabel: true,
					boxLabel: _('clear_cache_on_save'),
					description: _('clear_cache_on_save_msg'),
					inputValue: 1,
					checked: true
				}, {
					xtype: 'button',
					id: 'admintools-plugin-formcode-btn',
					text: _('admintools_plugin_form_code'),
					tooltip: _('admintools_plugin_form_code_desc'),
					tooltipType: 'title',
					style: {marginTop:'10px'},
					handler: function () {
						var childNodes = document.getElementById('admintools-plugin-events').childNodes,
							count = childNodes.length;
						var code = "<?php\n";
						if (count) {
							code += "switch ($modx->event->name) {\n";
							for (var i = 0; i < count; i++) {
								code += "\tcase '"+childNodes[i].textContent+"':\n\t\tbreak;\n";
							}
							code += "}";
						}
						Ext.getCmp(config.id+'-plugincode').setValue(code);
					}
				}, {
					xtype: 'displayfield',
					fieldLabel: _('admintools_events'),
					// hideLabel: true,
					id: 'admintools-plugin-events',
					labelStyle: 'margin-top:0px;'
				}]
			}]
		}, {
			// xtype: 'textarea'
			xtype: Ext.ComponentMgr.types['modx-texteditor'] ? 'modx-texteditor' : 'textarea',
			mimeType: 'application/x-php',
			name: 'plugincode',
			id: config.id + '-plugincode',
			fieldLabel: _('code'),
			anchor: '100%',
			height: 280,
			labelStyle: 'padding-top:0;'
			// ,grow: true
			// ,growMax: 300
		}],
		keys: [{
			key: Ext.EventObject.ENTER,
			ctrl: true,
			fn: function(keyCode, event) {
				var elem = event.getTarget();
				var component = Ext.getCmp(elem.id);
				if (component instanceof Ext.form.TextArea) {
					return component.append("\n");
				} else {
					this.submit();
				}
			}
			,scope: this
		}]
	});
	AdminTools.window.QuickCreatePlugin.superclass.constructor.call(this,config);
	this.on('beforesubmit', function () {
		var childNodes = document.getElementById('admintools-plugin-events').childNodes,
			count = childNodes.length,
			events = [];
		if (count) {
			for (var i = 0; i < count; i++) {
				events.push(childNodes[i].textContent);
			}
		}
		this.fp.getForm().findField('events').setValue(Ext.util.JSON.encode(events));
	});
};
Ext.extend(AdminTools.window.QuickCreatePlugin, MODx.Window, {
	addEventElement: function (event) {
		var span = Ext.DomHelper.createDom({tag:'span',id:'event-'+event,cls:'x-superboxselect-item',html:event}),
			a = Ext.DomHelper.createDom({tag:'a',cls:'x-superboxselect-item-close',href:'#'});
		a.addEventListener('click', function (e) {
			this.parentNode.remove();
		});
		span.appendChild(a);
		document.getElementById('admintools-plugin-events').appendChild(span);
	}
});
Ext.reg('admintools-quick-create-plugin', AdminTools.window.QuickCreatePlugin);

/**************** Combos *************************/
AdminTools.combo.Plugins = function(config) {
	config = config || {};
	Ext.applyIf(config, {
		fields: ['id', 'name'],
		valueField: 'id',
		displayField: 'name',
		name: 'pluginid',
		hiddenName: 'pluginid',
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/plugins/getplugins'
		},
		pageSize: 20,
		typeAhead: true,
		editable: true,
		forceSelection: false,
		allowBlank: false
	});
	AdminTools.combo.Plugins.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.combo.Plugins, MODx.combo.ComboBox);
Ext.reg('admintools-combo-plugins', AdminTools.combo.Plugins);

AdminTools.combo.Events = function(config) {
	config = config || {};
	Ext.applyIf(config, {
		fields: ['name'],
		valueField: 'name',
		displayField: 'name',
		name: 'event',
		hiddenName: 'event',
		url: adminToolsSettings.config.connector_url,
		baseParams: {
			action: 'mgr/plugins/getevents'
		},
		pageSize: 20,
		typeAhead: false,
		editable: true,
		forceSelection: true,
		triggerAction: 'all'
		//
	});
	AdminTools.combo.Events.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.combo.Events, MODx.combo.ComboBox);
Ext.reg('admintools-combo-events', AdminTools.combo.Events);