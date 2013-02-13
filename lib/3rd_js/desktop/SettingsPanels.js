Ext.define('MyDesktop.SettingsPanels', {
	uses: [
		'Ext.tree.Panel',
		'Ext.tree.View',
		'MyDesktop.SettingsBarSubItem'
	],
	extend		: 'Ext.Base',
	settingId	: null,
	title		: null,
	main		: null,

	statics		: {
		panels			: [],
		registerPanel	: function(panelName){
			this.panels.push(panelName);
		}
	},

	setBarSubItemsInitialSelection	: function(){
	},

	onBarSubItemsSelect				: function(tree, record){
	},

	onOk							: function(){
	},

	createConfigStore : function(model){
		var that = this;
		var mdl = Ext.ModelManager.getModel('config.'+model);
		return Ext.create('Ext.data.Store',{
			model		: 'config.'+model,
			proxy		: {
				type		: 'ajaxEx',
				url			: 'p/QDSettingPanels.getConfig/',
				extraParams	: {
					root		: model,
					configType	: mdl.prototype.config.configType
				},
				reader		: {
					type		: 'json',
				}
			}
		});
	},

	constructor						: function(cfg){
		var that = this;
		that.bar = {
			xtype		: 'panel',
			layout		: 'fit',
			items		: [{
				xtype		: 'treepanel',
				id			: that.treeid,
				rootVisible	: false,
				lines		: false,
				autoScroll	: true,
				minWidth	: 100,
				listeners	: {
					afterrender	: {
						fn		: that.setBarSubItemsInitialSelection,
						delay	: 100
					},
					select		: that.onBarSubItemsSelect,
					scope		: that
				},
				store		: new Ext.data.TreeStore({
					model		: 'MyDesktop.SettingsBarSubItem',
					root		: that.barSubItems
				})
			}]
		}
	}
});