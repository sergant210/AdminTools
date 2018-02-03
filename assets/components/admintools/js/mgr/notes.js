AdminTools.showNotes = function() {
	if (!window.userNotesWindow) {
		userNotesWindow = new MODx.Window({
			height: 640,
			minHeight: 600,
			width: 1200,
			padding: '5px',
			title: _("admintools_notes"),
			stateful: false,
			items: [{
				xtype: 'panel',
				id: 'admintools-notes-panel',
				height: 548,
				autoWidth: true,
				layout: 'border',
				items: [{
					region: 'center',
					//title: 'Центральная панель',
					id: 'admintools-notes-panel1',
					autoScroll: true,
					unstyled: true,
					layout: 'anchor',
					bodyStyle: 'background-color:#fff;',
					height: 250,
					items: [{
						xtype: 'admintools-notes-grid',
						layout: 'anchor',
						anchor: '98%'
					}]
					//width: 400
				}, {
					region: 'south',
					id: 'admintools-notes-panel2',
					split: true,
					forceLayout: true,
					autoScroll: true,
					header: false,
					useSplitTips: true,
					unstyled: true,
					collapsed: false,
					collapsible: false,
					padding: '5px',
					layout: 'anchor',
					bodyStyle: 'background-color:#fafafa;',
					height: 250,
					items: [{
						//xtype: Ext.ComponentMgr.types['modx-texteditor'] ? 'modx-texteditor' : 'textarea',
						xtype: 'htmleditor',
						cls: 'admintools-note text',
						id: 'admintool-notes-text',
						readOnly: true,
						anchor: '100% 97%',
						style: 'background-color: #fff',
						enableSourceEdit: false,
						//enableKeyEvents: true,
						listeners: {
							render: function () {
								this.el.on('keydown', function (e) {
									this.fireEvent('sync', this, this.el.getValue());
								}, this, {buffer: 500});
							},
							sync: function (sender, html) {
								var e = Ext.EventObject;
								if (e.button > 0) {
									if (!AdminTools.currentNote.isDirty) AdminTools.currentNote.setDirty();
								}
							}
						}
					}]

					//width: 400
				}]
			}],
			buttonAlign: 'left',
			buttons: [{
				text: _("save"),
				id: 'admintools_btn_save',
				hidden: true,
				handler: function () {
					MODx.Ajax.request({
						url: adminToolsSettings.config.connector_url,
						params: {
							action: 'mgr/notes/updatetext',
							id: AdminTools.currentNote.id,
							text: AdminTools.readPanel.getEditor().getValue() //AdminTools.currentNote.text
						},
						listeners: {
							success: {
								fn: function (r) {
									AdminTools.currentNote.sync().clearDirty(true);
									var row = Ext.getCmp('admintools-notes-grid').store.getById(AdminTools.currentNote.id);
									row.set('text', AdminTools.readPanel.getEditor().getValue());
									row.commit();
								}, scope: this
							},
							failure: {
								fn: function (r) {
									console.log(r);
								}
							}
						}
					});
				}
			}, {
				text: _("cancel"),
				id: 'admintools_btn_cancel',
				hidden: true,
				handler: function () {
					AdminTools.currentNote.clearDirty(false);
					AdminTools.readPanel.getEditor().setValue(AdminTools.currentNote.text);
				}
			}, '->', {
				text: _("admintools_close"),
				id: 'admintools_btn_close',
				handler: function () {
					userNotesWindow.hide();
				},
				scope: this
			}],
			listeners: {
				/*hide: {
					fn: function () {
						setTimeout(function () {
							userNotesWindow.destroy()
						}, 200);
					}
				},*/
				beforehide: {
					fn: function () {
						if (AdminTools.currentNote.isDirty) {
							Ext.MessageBox.alert(_('admintools_attention'), _("admintools_note_is_dirty"));
							return false;
						}
					}
				},
				resize: {
					fn: function (el, w, h) {
						Ext.getCmp('admintools-notes-panel').setHeight(h - 92);
					}
				}
			}
		});
	}
	userNotesWindow.show(Ext.EventObject.target);
};

AdminTools.grid.Notes = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-notes-grid';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		fields: ['id', 'title', 'text', 'private', 'username', 'createdon', 'createdby', 'url', 'tags', 'actions'],
		columns: this.getColumns(config),
		tbar: this.getTopBar(config),
		sm: new Ext.grid.RowSelectionModel(),
		baseParams: {
			action: 'mgr/notes/getlist'
		},
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
				this.updateNote(null, e, grid.store.getAt(rowIndex));
			},
			rowClick: function (grid, rowIndex, e) {
				if (AdminTools.currentNote.isDirty) {
					Ext.MessageBox.alert(_('admintools_attention'), _("admintools_note_is_dirty"));
					Ext.getCmp('admintools-notes-grid').selModel.selectRow(AdminTools.currentNote.rowIndex);
					return false;
				}
				var row = grid.store.getAt(rowIndex),
					editor = AdminTools.readPanel.getEditor();
				AdminTools.currentNote.set({title: row.data.title, text: row.data.text, id: row.data.id, rowIndex: rowIndex, user: row.data.createdby});
				editor.setValue(row.data.text);
				editor.setReadOnly(false);

			},
			rowcontextmenu: function (grid, rowIndex, e) {
			}
		},
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0,
			getRowClass: function (rec, ri, p) {
				return !rec.data.private
					? 'admintools-private-note'
					: '';
			}
		},
		paging: true,
		pageSize: 20,
		remoteSort: true
		//autoHeight: true
	});
	AdminTools.grid.Notes.superclass.constructor.call(this, config);

	// Clear selection on grid refresh
	this.store.on('load', function () {
		AdminTools.currentNote.reset();
		if (this._getSelectedIds().length) {
			this.getSelectionModel().clearSelections();
		}
	}, this);
	this.store.on('beforeload', function (e) {
		if (AdminTools.currentNote.isDirty) {
			Ext.MessageBox.alert(_('admintools_attention'), _("admintools_note_is_dirty"));
			return false;
		}

	}, this);
};
Ext.extend(AdminTools.grid.Notes, MODx.grid.Grid, {
	windows: {},

	getMenu: function (grid, rowIndex) {
		var ids = this._getSelectedIds();

		var row = grid.getStore().getAt(rowIndex);
		var menu = AdminTools.utils.getMenu(row.data['actions'], this, ids);

		this.addContextMenuItem(menu);
	},
	createNote: function (btn, e) {
		var w = MODx.load({
			xtype: 'admintools-note-create-window',
			id: Ext.id(),
			record: {editedby: '', editedon: ''},
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
		w.setValues({private: false});
		w.show(e.target);
	},
	updateNote: function (btn, e, row) {
		if (AdminTools.currentNote.isDirty) {
			Ext.MessageBox.alert(_('admintools_attention'), _("admintools_note_is_dirty"));
			return false;
		}
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var id = this.menu.record.id,
			editor = AdminTools.readPanel.getEditor();
		//TODO: Отражать в заголовке окна title заметки
		AdminTools.currentNote.set({title: this.menu.record.title, text: this.menu.record.text, id: id, user: this.menu.record.createdby});
		editor.setValue(this.menu.record.text);
		editor.setReadOnly(false);

		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/notes/get',
				id: id
			},
			listeners: {
				success: {
					fn: function (r) {
						var w = MODx.load({
							xtype: 'admintools-note-update-window',
							id: Ext.id(),
							record: r.object,
							listeners: {
								success: {
									fn: function (r) {
										//this.refresh();
										var note = r.a.result.object;
										if (AdminTools.currentNote.id && AdminTools.currentNote.id == note.id) {
											AdminTools.readPanel.getEditor().setValue(note.text);
											AdminTools.currentNote.sync();
										}
										var row = Ext.getCmp('admintools-notes-grid').store.getById(note.id);
										row.set('text', note.text);
										if (note.url == 'http://') note.url = '';
										if (note.url) {
											row.set('url', '<a href="' + note.url + '" target="_blank">' + note.url + '</a>');
										} else {
											row.set('url', '');
										}
										row.set('private', note.private);
										row.set('title', note.title);
										row.set('tags', note.tags);
										row.commit();
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
	removeNote: function (act, btn, e) {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}
		MODx.msg.confirm({
			title: ids.length > 1
				? _('admintools_notes_remove')
				: _('admintools_note_remove'),
			text: ids.length > 1
				? _('admintools_notes_remove_confirm')
				: _('admintools_note_remove_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/notes/remove',
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
	download:function(){
		MODx.Ajax.request({
			url: adminToolsSettings.config.connector_url,
			params: {
				action: 'mgr/notes/export'
			},
			listeners: {
				success: {
					fn: function (result) {
						location.href = adminToolsSettings.config.connector_url+"?action=mgr/notes/download&HTTP_MODAUTH="+MODx.siteId;
					}, scope: this
				},
				failure: {
					fn: function (result) {
						//panel.el.unmask();
						MODx.msg.alert(_('_error'), result.message);
					}, scope: this
				}
			}
		});
	},
	upload:function(){
		var input = document.getElementById('admintool_upload_notes');
		if (!input) {
			input = document.createElement('input');
			input.type = 'file';
			input.id = 'admintool_upload_notes';
			input.style = 'display:none';
			document.body.appendChild(input);
			input.addEventListener('change', this.handleFile, false);
		}
		input.click();
	},
	handleFile: function(e) {
		var grid = this;
		var file = e.target.files[0];
		var reader = new FileReader();
		reader.onload = function(e) {
			var content = e.target.result;
			MODx.Ajax.request({
				url: adminToolsSettings.config.connector_url,
				params: {
					action: 'mgr/notes/upload',
					content: content
				},
				listeners: {
					success: {
						fn: function () {
							Ext.getCmp('admintools-notes-grid').refresh();
						}, scope: this
					},
					failure: {
						fn: function (result) {
							//panel.el.unmask();
							MODx.msg.alert(_('_error'), result.message);
						}, scope: this
					}
				}
			});
		};
		reader.readAsText(file);
	},
	getColumns: function (config) {
		return [{
			header: 'ID',
			dataIndex: 'id',
			sortable: true,
			hidden: true,
			fixed: true,
			width: 70
		}, {
			header: _('admintools_notes_title'),
			dataIndex: 'title',
			sortable: true,
			width: 250
		}, {
			header: _('admintools_notes_url'),
			dataIndex: 'url',
			sortable: false,
			width: 120
		}, {
			header: _('admintools_notes_tags'),
			dataIndex: 'tags',
			sortable: false,
			width: 120
		}, {
			header: _('admintools_user'),
			dataIndex: 'username',
			sortable: true,
			width: 90
		}, {
			header: _('admintools_notes_createdon'),
			dataIndex: 'createdon',
			sortable: true,
			fixed: true,
			width: 140
		}, {
			header: _('admintools_notes_private'),
			dataIndex: 'private',
			renderer: AdminTools.utils.renderBoolean,
			sortable: true,
			fixed: true,
			width: 70
		}, {
			header: _('admintools_grid_actions'),
			dataIndex: 'actions',
			renderer: AdminTools.utils.renderActions,
			sortable: false,
			width: 70,
			fixed: true,
			id: 'actions'
		}, {
			header: _('admintools_notes_text'),
			dataIndex: 'text',
			sortable: false,
			hidden: true,
			width: 50
		}, {
			header: _('admintools_user'),
			dataIndex: 'createdby',
			sortable: true,
			hidden: true,
			width: 10
		}];
	},

	getTopBar: function (config) {
		return [{
			xtype: 'buttongroup',
			columns: 3,
			items: [{
				text: '<i class="icon icon-plus"></i>',
				handler: this.createNote,
				// style: {marginRight: '5px'},
				tooltip: _('admintools_create_note'),
				tooltipType: 'title',
				scope: this
			}, {
				text: '<i class="icon icon-download"></i>',
				handler: this.download,
				tooltip: _('admintools_export_notes'),
				tooltipType: 'title',
				// style: {marginRight: '5px'},
				scope: this
			}, {
				text: '<i class="icon icon-upload"></i>',
				handler: this.upload,
				tooltip: _('admintools_import_notes'),
				tooltipType: 'title',
				scope: this
			}]
		}, '->', {
			xtype: 'admintools-combo-wheresearch',
			name: 'wheresearch',
			width: 150,
			emptyText: _('admintools_search_where'),
			style: {marginRight: '20px'},
			editable: false,
			id: config.id + '-where-search'
		}, {
			xtype: 'textfield',
			name: 'searchQuery',
			width: 200,
			id: config.id + '-search-field',
			emptyText: _('admintools_grid_search'),
			style: {backgroundColor:'#fff'},
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._search();
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'button',
			id: config.id + '-search-btn',
			text: '<i class="icon icon-search"></i>',
			listeners: {
				click: {fn: this._search, scope: this}
			}
		}, {
			xtype: 'button',
			id: config.id + '-search-clear',
			text: '<i class="icon icon-times"></i>',
			listeners: {
				click: {fn: this._clearSearch, scope: this}
			}
		}, {
			xtype: 'tbspacer', width: 2
		}]
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
	},

	_search: function (tf, nv, ov) {
		this.store.baseParams.searchQuery = Ext.getCmp(this.config.id + '-search-field').getValue();
		this.store.baseParams.wheresearch = Ext.getCmp(this.config.id + '-where-search').getValue();
		this.getBottomToolbar().changePage(1);
		AdminTools.currentNote.reset();
		//this.refresh();
	},

	_clearSearch: function (btn, e) {
		this.store.baseParams.searchQuery = '';
		Ext.getCmp(this.config.id + '-search-field').setValue('');
		AdminTools.currentNote.reset();
		this.getBottomToolbar().changePage(1);
		//this.refresh();
	}
});
Ext.reg('admintools-notes-grid', AdminTools.grid.Notes);

/** ******************* Dialogs ********************** **/
AdminTools.window.CreateNote = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-note-create-window';
	}
	Ext.applyIf(config, {
		title: _('admintools_create_note'),
		width: 900,
		modal: true,
		maximizable: false,
		autoHeight: true,
		url: adminToolsSettings .config.connector_url,
		action: 'mgr/notes/add',
		fields: this.getFields(config),
		keys: [{
			key: Ext.EventObject.ENTER, shift: true, fn: function () {
				this.submit()
			}, scope: this
		}]
	});
	AdminTools.window.CreateNote.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.window.CreateNote, MODx.Window, {

	getFields: function (config) {
		return [{
			xtype: 'hidden',
			name: 'id',
			id: config.id + '-id'
		}, {
			xtype: 'textfield',
			fieldLabel: _('admintools_notes_title'),
			name: 'title',
			id: config.id + '-title',
			anchor: '100%',
			allowBlank: false
		}, {
			xtype: 'textfield',
			fieldLabel: _('admintools_notes_url'),
			emptyText: 'http://',
			name: 'url',
			id: config.id + '-url',
			anchor: '100%',
			allowBlank: true
		}, {
			xtype: 'textfield',
			fieldLabel: _('admintools_notes_tags'),
			name: 'tags',
			id: config.id + '-tags',
			anchor: '100%',
			allowBlank: true
		}, {
			xtype: 'htmleditor',
			fieldLabel: _('admintools_notes_text'),
			name: 'text',
			id: config.id + '-text',
			enableSourceEdit: false,
			height: 250,
			style: 'background-color: #fff',
			anchor: '100%'
		}, {
			layout: 'column'
			,border: false
			,anchor: '100%'
			,items: [{
				columnWidth: .5
				,layout: 'form'
				,defaults: { msgTarget: 'qtip' }
				,border:false
				,items: [{
					xtype: 'xcheckbox',
					boxLabel: _('admintools_notes_private'),
					name: 'private',
					disabled: adminToolsSettings.currentUser !== AdminTools.currentNote.user && AdminTools.currentNote.id,
					id: config.id + '-private'
				}]
			},{
				columnWidth: .5
				,layout: 'form'
				,defaults: { msgTarget: 'qtip' }
				,border:false
				,items: [{
					//xtype: '',
					//boxLabel: _('admintools_notes_editedby'),
					html: _('admintools_notes_editedby') + ': ' + config.record.editedby + '. ' + _('admintools_notes_editedon') + ': ' + config.record.editedon,
					style: {marginTop: '20px', textAlign: 'right'}
				}]
			}]
		}];
	}

});
Ext.reg('admintools-note-create-window', AdminTools.window.CreateNote);
// Update Dialog
AdminTools.window.UpdateNote = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-note-update-window';
	}
	Ext.applyIf(config, {
		title: _('admintools_note_edit'),
		action: 'mgr/notes/update'
	});
	AdminTools.window.UpdateNote.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.window.UpdateNote, AdminTools.window.CreateNote);
Ext.reg('admintools-note-update-window', AdminTools.window.UpdateNote);

/** ************************************************* **/
Ext.onReady(function() {
	AdminTools.currentNote = {
		id: 0,
		text: '',
		title: '',
		isDirty: false,
		rowIndex: null,
		user: 0,
		set: function (data) { //text, id, rowIndex, user
			this.text = data.text ? data.text : '';
			this.title = data.title ? data.title : '';
			this.id = data.id ? data.id : 0;
			this.user = data.user ? data.user : 0;
			this.rowIndex = data.rowIndex ? data.rowIndex : null;
		},
		setDirty: function () {
			this.isDirty = true;
			Ext.getCmp('admintools_btn_save').setVisible(true);
			Ext.getCmp('admintools_btn_cancel').setVisible(true);
			userNotesWindow.setTitle(userNotesWindow.title + ' (*)');
			return this;
		},
		clearDirty: function (save) {
			this.isDirty = false;
			Ext.getCmp('admintools_btn_save').setVisible(false);
			Ext.getCmp('admintools_btn_cancel').setVisible(false);
			userNotesWindow.setTitle(userNotesWindow.title.replace(' (*)', ''));
			return this;
		},
		reset: function () {
			var editor = AdminTools.readPanel.getEditor();
			editor.setValue('');
			editor.setReadOnly(true);
			this.id = 0;
			this.text = '';
			this.isDirty = false;
			this.rowIndex = null;
			return this;
		},
		sync: function () {
			this.text = AdminTools.readPanel.getEditor().getValue();
			return this;
		}
	};
	AdminTools.readPanel = {
		editor: null,
		getEditor: function () {
			if (!this.editor) {
				this.editor = Ext.getCmp('admintool-notes-text');
			}
			return this.editor;
		}
		/*collapse: function () {
			var panel = Ext.getCmp('admintools-notes-panel2'),
				that = this;

			if (panel.collapsed) {
				panel.expand(true);

				if (AdminTools.currentNote.id) {
					setTimeout(function () {
						//this.editor = Ext.getCmp('admintool-notes-text');

						that.getEditor().setValue(AdminTools.currentNote.text);
					}, 300);
				}
			} else {
				AdminTools.currentNote.set(that.getEditor().getValue());
				panel.collapse(true);
				//panel.setVisible(false);
			}
		}*/
	};

	var userMenuList = document.querySelector('#limenu-user ul.modx-subnav');
	var newLi = document.createElement('li');
	newLi.id = 'admintools-notes';
	newLi.innerHTML = '<a href="javascript:AdminTools.showNotes()">' + _('admintools_notes') + ' <span class="description">' + _('admintools_notes_desc') + '</span></a>';

	userMenuList.insertBefore(newLi, userMenuList.lastChild);
});