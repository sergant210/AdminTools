if (typeof MODx.grid.SystemSettings != 'undefined') {
	Ext.apply(MODx.grid.SystemSettings.prototype, {
		filterByNamespace: function (cb, rec, ri) {
			var init = typeof ri == 'undefined';
			var ns = init ? adminToolsSettings.systemSettings.namespace : rec.data.name;
			var area = init ? adminToolsSettings.systemSettings.area : '';
			Ext.getCmp('modx-filter-namespace').setValue(ns);
			this.getStore().baseParams['namespace'] = ns;
			this.getStore().baseParams['area'] = area;
			this.getBottomToolbar().changePage(1);
			//this.refresh();
			var acb = Ext.getCmp('modx-filter-area');
			if (acb) {
				var s = acb.store;
				s.baseParams['namespace'] = ns;
				s.removeAll();
				s.load();
				acb.setValue(area);
			}
			if (!init) {
				Ext.Ajax.request({
					url: adminToolsSettings.config.connector_url
					, params: {
						action: 'mgr/systemsettings/savestate',
						namespace: ns,
						area: ''
					}
					, success: function (r) {
						var res = Ext.decode(r.responseText);
						adminToolsSettings.systemSettings = res.object;
					}
					, scope: this
				});
			}
		},
		filterByArea: function (cb, rec, ri) {
			this.getStore().baseParams['area'] = rec.data['v'];
			this.getBottomToolbar().changePage(1);
			Ext.Ajax.request({
				url: adminToolsSettings.config.connector_url
				, params: {
					action: 'mgr/systemsettings/savestate',
					namespace: adminToolsSettings.systemSettings.namespace,
					area: rec.data['v']
				}
				, success: function (r) {
					var res = Ext.decode(r.responseText);
					adminToolsSettings.systemSettings = res.object;
				}
				, scope: this
			});
		}
	});
}