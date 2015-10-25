Ext.apply(MODx.tree.Element.prototype,{
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
								title: favorElements.states[type[1]] ? _('admintools_show_all') : _('admintools_show_favorites'),
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
									favorElements.states[type[1]] = true;
								} else {
									btn.getEl().removeClass('icon-star').addClass('icon-star-o');
									btn.pressed = false;
									btn.tooltip.setTitle(_('admintools_show_favorites'));
									favorElements.states[type[1]] = false;
								}
								node.getOwnerTree().handleFavoritesClick(node,btn.pressed);
								node.getOwnerTree().saveFavoritesState(node.attributes.type,btn.pressed);
							},
							iconCls: favorElements.states[type[1]] ? 'icon-star' : 'icon-star-o',
							pressed: favorElements.states[type[1]],
							renderTo: elId,
							listeners: {
								mouseover: function (button, e) {
									button.tooltip.onTargetOver(e);
								}
								, mouseout: function (button, e) {
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
							title: inlineButtonsLang.add
							, target: this
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
							}
							, mouseout: function (button, e) {
								button.tooltip.onTargetOut(e);
							}
						}
					});
					var btn3 = MODx.load({
						xtype: 'modx-button',
						text: '',
						scope: this,
						tooltip: new Ext.ToolTip({
							title: inlineButtonsLang.refresh
							, target: this
						}),
						node: node,
						handler: function (btn, evt) {
							evt.stopPropagation(evt);
							tree.un('remove',tree.onRemove,tree);
							node.reload();
						},
						iconCls: 'icon-refresh',
						renderTo: elId,
						listeners: {
							mouseover: function (button, e) {
								button.tooltip.onTargetOver(e);
							}
							, mouseout: function (button, e) {
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
	saveFavoritesState: function(type,state){
		favorElements.states[type] = state;
		Ext.Ajax.request({
			url: favorElements.config.connector_url
			,params: {
				action: 'mgr/favorites/savestate',
				type: type,
				state: state
			}
			,success: function(r) {}
			,scope:this
		});
	},
	_showContextMenu: function(n,e) {
		this.cm.activeNode = n;
		this.cm.removeAll();
		if (n.attributes.menu && n.attributes.menu.items) {
			this.addContextMenuItem(n.attributes.menu.items);
			this.cm.show(n.getUI().getEl(),'t?');
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
	onRemove: function(t, cat, n) {
		if (!n.isMoving && n.ui.hasClass('x-element-favorite')) this.fromFavorites(n);
		n.isMoving = false;
	},
	onMoveNode: function(t,n){
		n.isMoving = true;
	},
	toFavorites: function(){
		var node = this.cm.activeNode,
			parent = node.parentNode;
		if (!parent.favChilds) parent.favChilds=0;
		parent.favChilds++;

		Ext.Ajax.request({
			url: favorElements.config.connector_url
			,params: {
				action: 'mgr/favorites/add',
				type: node.attributes.type,
				id: node.attributes.pk
			}
			,success: function(r) {
				var res = Ext.decode(r.responseText);
				favorElements.elements = res.object;
			}
			,scope:this
		});
		node.ui.addClass('x-element-favorite');
		//node.ui.iconNode.className = 'icon icon-star';
	},
	fromFavorites: function(n){
		var node = this.cm.activeNode || n;
		var	parent = node.parentNode,
			type = node.attributes.type,
			favoriteMode = favorElements.states[type];

		if (favoriteMode) {
			node.getUI().hide();
			parent.favChilds--;
			if (parent.attributes.classKey == 'modCategory') {
				if (parent.favChilds == 0) {
					parent.getUI().hide();
				} else {
					parent.setText(parent.attributes.data.category + ' (' + parent.favChilds + ')');
				}
			}
		}
		Ext.Ajax.request({
			url: favorElements.config.connector_url
			,params: {
				action: 'mgr/favorites/remove',
				type: node.attributes.type,
				id: node.attributes.pk
			}
			,success: function(r) {
				var res = Ext.decode(r.responseText);
				favorElements.elements = res.object;
			}
			,scope:this
		});
		node.getUI().removeClass('x-element-favorite');
		node.ui.iconNode.className = 'icon icon-file-o';
	},
	onLoad: function(ldr,node,resp) {
		Ext.each(node.childNodes, function(node){
			if (node.attributes.selected) {
				node.ui.addClass('x-tree-selected');
			}
			var id = node.id.substr(2);
			var type = id.split('_');
			if (node.leaf && MODx.in_array(type[0],['template','tv','chunk','snippet','plugin'])) {
				if ( MODx.in_array(type[2],favorElements.elements[type[0]+'s'])) {
					node.ui.addClass('x-element-favorite');
					//node.ui.iconNode.className = 'icon icon-star';
				}
			}
		});
		//
		if (!node.isRoot) {
			if (favorElements.states[node.attributes.type]) {
				this.handleFavoritesClick(node, true, true);
			}
		} else {
			this.on('remove',this.onRemove,this);
			this.on('beforemovenode',this.onMoveNode,this);
		}
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
	},
	handleFavoritesClick: function(parent, showFavorites, init){
		init = init || false;
		var that = this,
			count = parent.childNodes.length;
		Ext.each(parent.childNodes, function(node){
			if (showFavorites) {
				if (node.leaf && !node.getUI().hasClass('x-element-favorite')) {
					node.getUI().hide();
					count--;
				} else if (!node.leaf && node.hasChildNodes()) {
					if (!init && node.childrenRendered) {
						count = that.handleFavoritesClick(node, showFavorites);
						if (count == 0) {
							node.getUI().hide();
						} else {
							node.setText(node.attributes.data.category + ' (' + count + ')');
						}
					}
					if (!node.childrenRendered) {
						node.setText(node.attributes.data.category);
					}
				}
			} else {
				node.getUI().show();
				if (!node.leaf && node.hasChildNodes()) {
					that.handleFavoritesClick(node, false);
					//if (node.childrenRendered)
					node.setText(node.attributes.data.category+' ('+node.attributes.data.elementCount+')');
				}
			}
		});
		if (showFavorites) {
			if (parent.childrenRendered && !parent.attributes.pseudoroot) {
				parent.favChilds = count;
				if (count == 0) {
					parent.getUI().hide();
				} else {
					parent.setText(parent.attributes.data.category + ' (' + count + ')');
				}
			}
		}
		return count;
	}
});
MODx.in_array = function(o,arr){
	if (arr && arr.length > 0) {
		for (var i = 0; i < arr.length; i++) {
			if (arr[i] == o) return true;
		}
	}
	return false;
};