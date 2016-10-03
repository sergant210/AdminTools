AdminTools.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'admintools-panel-home', renderTo: 'admintools-panel-home-div'
		}]
	});
	AdminTools.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.page.Home, MODx.Component);
Ext.reg('admintools-page-home', AdminTools.page.Home);

Ext.onReady(function() {
	MODx.load({ xtype: "admintools-page-home"});
});