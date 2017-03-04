var CurrentLayout = Ext.ComponentMgr.types['modx-layout'];

MODx.Layout.AdminTools = function(config,getStore) {
	config = config || {};

	MODx.Layout.AdminTools.superclass.constructor.call(this,config);

	return this;
};
Ext.extend(MODx.Layout.AdminTools, CurrentLayout, {

	getWest: function(config) {
		var tabs = [];
		if (MODx.perm.resource_tree) {
			tabs.push({
				title: _('resources')
				,xtype: 'modx-tree-resource'
				,id: 'modx-resource-tree'
			});
			config.showTree = true;
		}
		if (MODx.perm.element_tree) {
			tabs.push({
				title: _('elements')
				,xtype: 'modx-tree-element'
				,id: 'modx-tree-element'
			});
			config.showTree = true;
		}
		if (MODx.perm.file_tree) {
			tabs.push({
				title: _('files')
				,xtype: 'modx-panel-filetree'
				,id: 'modx-file-tree'
			});
			config.showTree = true;
		}
		var activeTab = 0,
			region = sideBarRegion || 'west';

		return {
			region: region
			,applyTo: 'modx-leftbar'
			,id: 'modx-leftbar-tabs'
			,split: true
			,width: 310
			,minSize: 288
			,maxSize: 800
			,autoScroll: true
			,unstyled: true
			,collapseMode: 'mini'
			,useSplitTips: true
			,monitorResize: true
			,layout: 'anchor'
			,items: [{
				xtype: 'modx-tabs'
				,plain: true
				,defaults: {
					autoScroll: true
					,fitToFrame: true
				}
				,id: 'modx-leftbar-tabpanel'
				,border: false
				,anchor: '100%'
				,activeTab: activeTab
				,stateful: true
				//,stateId: 'modx-leftbar-tabs'
				,stateEvents: ['tabchange']
				,getState:function() {
					return {
						activeTab: this.items.indexOf(this.getActiveTab())
					};
				}
				,items: tabs
			}]
			,getState: function() {
				// The region's attributes we want to save/restore
				return {
					collapsed: this.collapsed
					,width: this.width
				};
			}
			,listeners:{
				beforestatesave: this.onBeforeSaveState
				,scope: this
			}
		};
	}
});
Ext.reg('modx-layout',MODx.Layout.AdminTools);