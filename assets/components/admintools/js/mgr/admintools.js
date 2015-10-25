var AdminTools = function (config) {
	config = config || {};
	AdminTools.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('admintools', AdminTools);

AdminTools = new AdminTools();