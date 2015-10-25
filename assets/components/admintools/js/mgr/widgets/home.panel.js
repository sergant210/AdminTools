AdminTools.panel.Home = function (config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		/*
		 stateful: true,
		 stateId: 'admintools-panel-home',
		 stateEvents: ['tabchange'],
		 getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
		 */
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('admintools') + '</h2>',
			cls: '',
			style: {margin: '15px 0'}
		}, {
			xtype: 'modx-tabs',
			defaults: {border: false, autoHeight: true},
			border: true,
			hideMode: 'offsets',
			items: [{
				title: _('admintools_items'),
				layout: 'anchor',
				items: [{
					html: _('admintools_intro_msg'),
					cls: 'panel-desc',
				}, {
					xtype: 'admintools-grid-items',
					cls: 'main-wrapper',
				}]
			}]
		}]
	});
	AdminTools.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.panel.Home, MODx.Panel);
Ext.reg('admintools-panel-home', AdminTools.panel.Home);
