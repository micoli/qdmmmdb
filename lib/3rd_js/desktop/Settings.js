/*
 * Ext JS Library 4.0
 * Copyright(c) 2006-2011 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */

Ext.define('MyDesktop.Settings', {
	extend	: 'Ext.window.Window',

	uses	: Ext.Array.merge([
			'Ext.tree.Panel',
			'Ext.tree.View',
			'Ext.layout.container.Anchor',
			'Ext.layout.container.Border',
			'MyDesktop.SettingsPanels',
			'MyDesktop.SettingsBarSubItem'
			],QD_GBL_SETTING_PANELS),

	layout	: 'anchor',
	title	: 'Change Settings',
	modal	: true,
	width	: 740,
	height	: 430,
	border	: false,

	initComponent : function () {
		var that = this;

		that.MainPanelsId = Ext.id();

		that.allMenus	=[];
		that.allMains	=[];

		for(var v in MyDesktop.SettingsPanels.panels){
			var settingCmp = Ext.create(MyDesktop.SettingsPanels.panels[v],{
				desktop: that.desktop
			});
			if(!settingCmp.disabled){
				that.allMenus.push(Ext.apply({
					xtype		: 'panel',
					title		: settingCmp.title,
					settingId	: settingCmp.settingId
				},settingCmp.bar));
				that.allMains.push(Ext.apply({
					xtype		: 'panel',
					layout		: 'fit',
					title		: settingCmp.title,
					settingId	: settingCmp.settingId
				},settingCmp.main));
			}
		}

		Ext.apply(this,{
			items : [{
				anchor	: '0 0',
				border	: false,
				layout	: 'border',
				items	: [{
					region	: 'west',
					layout: {
						type: 'accordion',
						titleCollapse: false,
						animate: true,
						activeOnTop: true
					},
					width	: 150,
					split	: true,
					activeItem :1,
					defaults:{
						listeners : {
							expand: function(panel,cb) {
								var panels = Ext.getCmp(that.MainPanelsId);
								panels.items.each(function(v,k){
									if(v.initialConfig.settingId==panel.initialConfig.settingId){
										panels.getLayout().setActiveItem(k);
										return false;
									}
								})
							}
						}
					},
					items	: that.allMenus
				},{
					region	: 'center',
					layout	: 'card',
					id		: that.MainPanelsId,
					items	: that.allMains
				}]
			}],
			buttons : [{
				text	: 'Close',
				scope	: that,
				handler	: that.close
			}]
		});
		that.callParent();
	}
});