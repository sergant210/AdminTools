var AdminTools = function (config) {
	config = config || {};
	AdminTools.superclass.constructor.call(this, config);
};
Ext.extend(AdminTools, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}, toolbar: {}
});
Ext.reg('admintools', AdminTools);

AdminTools = new AdminTools();


AdminTools.utils.renderBoolean = function (value, props, row) {

	return value
		? String.format('<span class="green">{0}</span>', _('yes'))
		: String.format('<span class="red">{0}</span>', _('no'));
};

AdminTools.utils.getMenu = function (actions, grid, selected) {
	var menu = [];
	var cls, icon, title, action = '';

	for (var i in actions) {
		if (!actions.hasOwnProperty(i)) {
			continue;
		}

		var a = actions[i];
		if (!a['menu']) {
			if (a == '-') {
				menu.push('-');
			}
			continue;
		}
		else if (menu.length > 0 && /^remove/i.test(a['action'])) {
			menu.push('-');
		}

		if (selected.length > 1) {
			if (!a['multiple']) {
				continue;
			}
			else if (typeof(a['multiple']) == 'string') {
				a['title'] = a['multiple'];
			}
		}

		cls = a['cls'] ? a['cls'] : '';
		icon = a['icon'] ? a['icon'] : '';
		title = a['title'] ? a['title'] : a['title'];
		action = a['action'] ? grid[a['action']] : '';

		menu.push({
			handler: action,
			text: String.format(
				'<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
				cls, icon, title
			),
		});
	}

	return menu;
};


AdminTools.utils.renderActions = function (value, props, row) {
	var res = [];
	var cls, icon, title, action, item = '';
	for (var i in row.data.actions) {
		if (!row.data.actions.hasOwnProperty(i)) {
			continue;
		}
		var a = row.data.actions[i];
		if (!a['button']) {
			continue;
		}

		cls = a['cls'] ? a['cls'] : '';
		icon = a['icon'] ? a['icon'] : '';
		action = a['action'] ? a['action'] : '';
		title = a['title'] ? a['title'] : '';

		item = String.format(
			'<li class="{0}"><button class="btn btn-default {1}" action="{2}" title="{3}"></button></li>',
			cls, icon, action, title
		);

		res.push(item);
	}

	return String.format(
		'<ul class="admintools-row-actions">{0}</ul>',
		res.join('')
	);
};

// Status
AdminTools.combo.SearchTypes = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		triggerAction: 'all',
		typeAhead: true,
		mode: 'local',
		hideMode: 'offsets',
		autoScroll: true,
		maxHeight: 200,
		store: [[1,_('admintools_search_everywhere')],[2,_('admintools_search_in_titles')],[3,_('admintools_search_in_text')],[4,_('admintools_search_in_tags')]],
		hiddenName: 'wheresearch',
		editable: true
	});
	AdminTools.combo.SearchTypes.superclass.constructor.call(this,config);
};
Ext.extend(AdminTools.combo.SearchTypes,MODx.combo.ComboBox);
Ext.reg('admintools-combo-wheresearch',AdminTools.combo.SearchTypes);

/** ***************************************** **/
/*

Ext.onReady(function () {
	setTimeout(function(){
		var tmpl = Ext.getCmp('modx-resource-template');
		tmpl.label.update(' <a href="#">' + tmpl.label.dom.innerText + '</a>');
console.log(tmpl.label);
	}, 200);
});
*/
