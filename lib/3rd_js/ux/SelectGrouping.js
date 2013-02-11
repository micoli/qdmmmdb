Ext.define('Ext.ux.SelectGrouping', {
	extend: 'Ext.grid.feature.Grouping',
	alias: 'feature.selectGrouping',

	constructor: function() {
		var me = this;
		me.addEvents(['groupselectall','groupselectnone']);
		me.collapsedState = {};
		me.callParent(arguments);
	},

	getFeatureTpl: function(values, parent, x, xcount) {
		var me = this;

		return [
			'<tpl if="typeof rows !== \'undefined\'">',
				// group row tpl
				'<tr id="{groupHeaderId}" class="' + Ext.baseCSSPrefix + 'grid-group-hd {hdCollapsedCls} {collapsibleClass}">',
					'<td class="' + Ext.baseCSSPrefix + 'grid-cell" colspan="' + parent.columns.length + '" {[this.indentByDepth(values)]}>',
						'<div class="' + Ext.baseCSSPrefix + 'grid-cell-inner">',
							'<div class="' + Ext.baseCSSPrefix + 'grid-group-title">{collapsed}',
								'<span class="grid-group-select-all icon-check-all"></span>',
								'<span class="grid-group-select-none icon-check-none"></span>',
								'{[this.renderGroupHeaderTpl(values, parent)]} ',
							'</div>',
						'</div>',
					'</td>',
				'</tr>',
				// this is the rowbody
				'<tr id="{groupBodyId}" class="' + Ext.baseCSSPrefix + 'grid-group-body {collapsedCls}">',
					'<td colspan="' + parent.columns.length + '">{[this.recurse(values)]}</td>',
				'</tr>',
			'</tpl>'
		].join('');
	},
	onGroupClick: function(view, rowElement, groupName, e) {
		var me = this;
		if(new RegExp('grid-group-select-all').test(e.getTarget().className)){
			me.fireEvent('groupselectall', me,groupName);
			return;
		}
		if(new RegExp('grid-group-select-none').test(e.getTarget().className)){
			me.fireEvent('groupselectnone', me,groupName)
			return;
		}

		if (me.collapsible) {
			if (me.collapsedState[groupName]) {
				me.expand(groupName);
			} else {
				me.collapse(groupName);
			}
		}
	},

});