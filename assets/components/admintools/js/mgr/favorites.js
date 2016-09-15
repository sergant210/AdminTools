if (typeof MODx.tree.Element != 'undefined') {
	Ext.apply(MODx.tree.Element.prototype, {
		_onAppend: function (tree, parent, node) {
			if (node.attributes.pseudoroot) {
				setTimeout((function (tree) {
					return function () {
						var elId = node.ui.elNode.id + '_tools';
						var el = document.createElement('div');
						el.id = elId;
						el.className = 'modx-tree-node-tool-ct';
						node.ui.elNode.appendChild(el);

						var inlineButtonsLang = tree.getInlineButtonsLang(node);

						var type = node.id.substr(2).split('_');
						if (type[0] != 'category') {
							var btn1 = MODx.load({
								xtype: 'modx-button',
								text: '',
								scope: this,
								tooltip: new Ext.ToolTip({
									title: adminToolsSettings.favoriteElements.states[type[1]] ? _('admintools_show_all') : _('admintools_show_favorites'),
									target: this
								}),
								node: node,
								enableToggle: true,
								handler: function (btn, evt) {
									evt.stopPropagation(evt);
									if (btn.pressed) {
										btn.getEl().removeClass('icon-star-o').addClass('icon-star');
										btn.pressed = true;
										btn.tooltip.setTitle(_('admintools_show_all'));
										adminToolsSettings.favoriteElements.states[type[1]] = true;
									} else {
										btn.getEl().removeClass('icon-star').addClass('icon-star-o');
										btn.pressed = false;
										btn.tooltip.setTitle(_('admintools_show_favorites'));
										adminToolsSettings.favoriteElements.states[type[1]] = false;
									}
									node.getOwnerTree().saveFavoritesState(node, btn.pressed);
								},
								iconCls: adminToolsSettings.favoriteElements.states[type[1]] ? 'icon-star' : 'icon-star-o',
								pressed: adminToolsSettings.favoriteElements.states[type[1]],
								renderTo: elId,
								listeners: {
									mouseover: function (button, e) {
										button.tooltip.onTargetOver(e);
									},
									mouseout: function (button, e) {
										button.tooltip.onTargetOut(e);
									}
								}
							});
						}
						var btn2 = MODx.load({
							xtype: 'modx-button',
							text: '',
							scope: this,
							tooltip: new Ext.ToolTip({
								title: inlineButtonsLang.add,
								target: this
							}),
							node: node,
							handler: function (btn, evt) {
								evt.stopPropagation(evt);
								node.getOwnerTree().handleCreateClick(node);
							},
							iconCls: 'icon-plus-circle',
							renderTo: elId,
							listeners: {
								mouseover: function (button, e) {
									button.tooltip.onTargetOver(e);
								},
								mouseout: function (button, e) {
									button.tooltip.onTargetOut(e);
								}
							}
						});
						var btn3 = MODx.load({
							xtype: 'modx-button',
							text: '',
							scope: this,
							tooltip: new Ext.ToolTip({
								title: inlineButtonsLang.refresh,
								target: this
							}),
							node: node,
							handler: function (btn, evt) {
								evt.stopPropagation(evt);
								node.reload();
							},
							iconCls: 'icon-refresh',
							renderTo: elId,
							listeners: {
								mouseover: function (button, e) {
									button.tooltip.onTargetOver(e);
								},
								mouseout: function (button, e) {
									button.tooltip.onTargetOut(e);
								}
							}
						});
						window.BTNS.push(btn1, btn2, btn3);

					}
				}(this)), 200);

				return false;
			}
		},
		saveFavoritesState: function (node, state) {
			var type = node.attributes.type;
			Ext.Ajax.request({
				url: adminToolsSettings.config.connector_url
				, params: {
					action: 'mgr/favorites/savestate',
					type: type,
					state: state
				}
				, success: function (r) {
					var res = Ext.decode(r.responseText);
					adminToolsSettings.favoriteElements.states = res.object;
					node.reload();
				}
				, scope: this
			});
		},
		_showContextMenu: function (n, e) {
			this.cm.activeNode = n;
			this.cm.removeAll();
			if (n.attributes.menu && n.attributes.menu.items) {
				this.addContextMenuItem(n.attributes.menu.items);
				this.cm.show(n.getUI().getEl(), 't?');
			} else {
				var m = [];
				var ui = n.getUI();
				switch (n.attributes.classKey) {
					case 'root':
						m = this._getRootMenu(n);
						break;
					case 'modCategory':
						m = this._getCategoryMenu(n);
						break;
					default:
						m = this._getElementMenu(n);
						m.push('-');
						if (ui.hasClass('x-element-favorite')) {
							m.push({
								text: _('admintools_remove_from_favorites'),
								handler: this.fromFavorites
							});
						} else {
							m.push({
								text: _('admintools_add_to_favorites'),
								handler: this.toFavorites
							});
						}
						break;
				}
				this.addContextMenuItem(m);
				this.cm.showAt(e.xy);
			}
			e.stopEvent();
		},
		toFavorites: function () {
			var node = this.cm.activeNode,
				parent = node.parentNode;
			if (!parent.favChilds) parent.favChilds = 0;
			parent.favChilds++;

			Ext.Ajax.request({
				url: adminToolsSettings.config.connector_url
				, params: {
					action: 'mgr/favorites/add',
					type: node.attributes.type,
					id: node.attributes.pk
				}
				, success: function (r) {
					var res = Ext.decode(r.responseText);
					adminToolsSettings.favoriteElements.elements = res.object;
					node.ui.addClass('x-element-favorite');
					if (adminToolsSettings.favoriteElements.icon) node.ui.iconNode.className = adminToolsSettings.favoriteElements.icon;
				}
				, scope: this
			});
		},
		fromFavorites: function () {
			var node = this.cm.activeNode;
			var type = node.attributes.type,
				favoriteMode = adminToolsSettings.favoriteElements.states[type];

			if (favoriteMode) {
				node.getUI().hide();
			}
			Ext.Ajax.request({
				url: adminToolsSettings.config.connector_url
				, params: {
					action: 'mgr/favorites/remove',
					type: node.attributes.type,
					id: node.attributes.pk
				}
				, success: function (r) {
					var res = Ext.decode(r.responseText);
					adminToolsSettings.favoriteElements.elements = res.object;
					node.getUI().removeClass('x-element-favorite');
					//node.ui.iconNode.className = node.attributes.iconCls;
					if ( /\bstatic\b/.test(node.attributes.cls) ) {
						node.ui.iconNode.className = 'icon icon-file-text-o';
					} else {
						node.ui.iconNode.className = 'icon icon-file-o';
						//node.setIconCls('icon icon-file-o');
					}
				}
				, scope: this
			});
		},
		onLoad: function (ldr, node, resp) {
			Ext.each(node.childNodes, function (node) {
				if (node.attributes.selected) {
					node.ui.addClass('x-tree-selected');
				}
				if (node.attributes.favorite) {
					node.ui.addClass('x-element-favorite');
				}
			});

			var r = Ext.decode(resp.responseText);
			if (r.message) {
				var el = this.getTreeEl();
				el.addClass('modx-tree-load-msg');
				el.update(r.message);
				var w = 270;
				if (this.config.width > 150) {
					w = this.config.width;
				}
				el.setWidth(w);
				this.doLayout();
			}
		}
	});

	Ext.onReady(function () {
		var tree = Ext.getCmp('modx-tree-element');
		tree.config.url = adminToolsSettings.config.connector_url;
		tree.baseParams.action = 'mgr/element/getnodes';
		tree.config.sortAction = 'mgr/element/sort';
		tree.removeElement = function (itm, e) {
			var id = this.cm.activeNode.id.substr(2);
			var oar = id.split('_');

			MODx.msg.confirm({
				title: _('warning'),
				text: _('remove_this_confirm', {
					type: oar[0],
					name: this.cm.activeNode.attributes.name
				}),
				url: MODx.config.connector_url,
				params: {
					action: 'element/' + oar[0] + '/remove',
					id: oar[2]
				},
				listeners: {
					'success': {
						fn: function () {
							tree.cm.activeNode.remove();
							tree.fromFavorites();
							/* if editing the element being removed */
							if (MODx.request.a == 'element/' + oar[0] + '/update' && MODx.request.id == oar[2]) {
								MODx.loadPage('welcome');
							}
						}, scope: tree
					}
				}
			});
		};
	});
}
