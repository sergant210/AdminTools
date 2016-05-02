AdminTools.grid.TemplResources = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'admintools-templresources-grid';
	}
	Ext.applyIf(config, {
		url: adminToolsSettings.config.connector_url,
		fields: this.getFields(config),
		columns: this.getColumns(config),
		baseParams: {
			action: 'mgr/resources/getlist',
			tid: MODx.request.id
		},
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
			}
		},
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0,
			getRowClass: function (rec, ri, p) {
				return rec.data.deleted
					? 'admintools-grid-row-disabled'
					: '';
			}
		},
		paging: true,
		remoteSort: true,
		autoHeight: true
	});
	AdminTools.grid.TemplResources.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools.grid.TemplResources, MODx.grid.Grid, {
	windows: {},

	getFields: function (config) {
		return ['id', 'pagetitle', 'description', 'deleted' , 'published', 'context_key', 'uri'];
	},
	getColumns: function (config) {
		return [{
			header: "ID",
			dataIndex: 'id',
			fixed: true,
			sortable: true,
			width: 100
		}, {
			header: _('admintools_title'),
			dataIndex: 'pagetitle',
			sortable: true,
			width: 200
		}, {
			header: _('admintools_description'),
			dataIndex: 'description',
			sortable: false,
			width: 200
		}, {
			header: 'URI',
			dataIndex: 'uri',
			sortable: false,
			width: 100
		}, {
			header: _('admintools_context'),
			dataIndex: 'context_key',
			sortable: false,
			width: 70
		}, {
			header: _('admintools_published'),
			dataIndex: 'published',
			renderer: AdminTools.utils.renderBoolean,
			sortable: false,
			width: 70
		}, {
			header: _('admintools_deleted'),
			dataIndex: 'deleted',
			renderer: AdminTools.utils.renderBoolean,
			sortable: false,
			width: 70
		}];
	},

	getTopBar: function (config) {
		return [{
			text: '<i class="icon icon-plus"></i>&nbsp;' + _('admintools_create'),
			handler: this.createResource(),
			scope: this
		}];
	}

});
Ext.reg('admintools-templresources-grid', AdminTools.grid.TemplResources);


/** ******************************** **/

Ext.onReady(function () {
	MODx.addTab("modx-template-tabs",{
		id: "admintools-resources-tab",
		title: _('admintools_resources'),
		items: [{
			xtype: "admintools-templresources-grid",
			//html: "test",
			width: "100%"
		}]
	});
});
