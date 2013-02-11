/*!
 * Ext JS Library 4.0
 * Copyright(c) 2006-2011 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */


Ext.define('MyDesktop.SettingsPanels', {
	extend		: 'Ext.Base',
	settingId	: null,
	title		: null,
	main		: null,
	uses: [
		'Ext.tree.Panel',
		'Ext.tree.View',
		'MyDesktop.settingsBarSubItem'
	],
	setBarSubItemsInitialSelection	: function(){
	},
	onBarSubItemsSelect				: function(tree, record){
	},
	onOk							: function(){
	},
	constructor : function(cfg){
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
					model		: 'MyDesktop.settingsBarSubItem',
					root		: that.barSubItems
				})
			}]
		}
	}
});

Ext.define('MyDesktop.SettingsPanels.Components.Wallpaper', {
	extend		: 'MyDesktop.SettingsPanels',
	settingId	: 'wallpaper',
	title		: 'WallPaper',
	uses: [
		'Ext.ux.desktop.Wallpaper',
		'Ext.form.field.Checkbox'
	],

	getTextOfWallpaper: function (path) {
		var text = path, slash = path.lastIndexOf('/');
		if (slash >= 0) {
			text = text.substring(slash+1);
		}
		var dot = text.lastIndexOf('.');
		text = Ext.String.capitalize(text.substring(0, dot));
		text = text.replace(/[-]/g, ' ');
		return text;
	},

	onOK: function () {
		var that = this;
		if (that.selected) {
			that.desktop.setWallpaper(that.selected, that.stretch);
		}
	},

	onBarSubItemsSelect:  function(tree, record) {
		var that = this;
		that.selected = (record.data.params)?('lib/3rd_js/desktop/wallpapers/' + record.data.params):Ext.BLANK_IMAGE_URL;
		Ext.getCmp(that.previewid).setWallpaper(that.selected);
	},

	setBarSubItemsInitialSelection: function () {
		var that = this;
		var s = that.desktop.getWallpaper();
		if (s) {
			var path = '/Wallpaper/' + this.getTextOfWallpaper(s);
			Ext.getCmp(that.treeid).selectPath(path, 'text');
		}
	},

	constructor : function(cfg) {
		var that		= this;

		that.treeid		= Ext.id();
		that.previewid	= Ext.id();

		Ext.apply(that,cfg);

		that.selected	= that.desktop.getWallpaper();
		that.stretch	= that.desktop.wallpaper.stretch;

		function child (img) {
			return {
				params	: img,
				text	: that.getTextOfWallpaper(img),
				iconCls	: '',
				leaf	: true
			};
		}

		that.barSubItems = {
			text		: 'Wallpaper',
			expanded	: true,
			children	: [{
				text		: "None",
				iconCls		: '',
				leaf		: true
			},
			child('Blue-Sencha.jpg'	),
			child('Dark-Sencha.jpg'	),
			child('Wood-Sencha.jpg'	),
			child('blue.jpg'		),
			child('desk.jpg'		),
			child('desktop.jpg'		),
			child('desktop2.jpg'	),
			child('sky.jpg'			)
			]
		}

		that.main = {
			layout		: 'border',
			items		: [{
				xtype		: 'wallpaper',
				region		: 'center',
				id			: that.previewid,
				listeners	: {
					afterrender : function(){
						Ext.getCmp(that.previewid).setWallpaper(that.selected);
					}
				}
			},{
				xtype		: 'checkbox',
				region		: 'south',
				height		: 40,
				frame		: true,
				boxLabel	: 'Stretch to fit',
				checked		: that.stretch,
				listeners	: {
					change		: function (comp) {
						that.stretch = comp.checked;
					}
				}
			}],
			bbar : ['->',{
				xtype	: 'button',text: 'OK', handler: that.onOK, scope: that
			},{
				xtype	: 'button',text: 'Cancel', handler: that.setBarSubItemsInitialSelection, scope: that
			}]
		}
		that.callParent(that);
	}
});

Ext.define('MyDesktop.SettingsPanels.Components.Wallpaper2', {
	extend		: 'MyDesktop.SettingsPanels',
	settingId	: 'wallpaper2',
	title		: 'aa2',

	onBarSubItemsSelect:  function(tree, record) {
		var that = this;
		Ext.getCmp(that.mainid).update(record.get('params'));
	},

	constructor	:	function(cfg){
		var that	= this;
		that.mainid	= Ext.id();

		that.barSubItems = {
			text		: '',
			expanded	: true,
			children	: [{
				text		: "text1",
				iconCls		: '',
				leaf		: true,
				params		: 1
			},{
				text		: "text2",
				iconCls		: '',
				leaf		: true,
				params		: 2
			}]
		}
		that.main = {
			id		: that.mainid,
			html	: 'aa2',
			bbar : ['->',{
				xtype	: 'button',text: 'OK', handler: that.onOK, scope: that
			},{
				xtype	: 'button',text: 'Cancel', handler: that.setBarSubItemsInitialSelection, scope: that
			}]
		}
		that.callParent(that);
	}
});

Ext.define('MyDesktop.Settings', {
	extend	: 'Ext.window.Window',

	uses	: [
		'Ext.tree.Panel',
		'Ext.tree.View',
		'Ext.layout.container.Anchor',
		'Ext.layout.container.Border',
		'MyDesktop.settingsBarSubItem'
	],

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

		for(var v in MyDesktop.SettingsPanels.Components){
			var settingCmp = new MyDesktop.SettingsPanels.Components[v]({
				desktop: that.desktop
			});
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

		Ext.apply(this,{
			items : [{
				anchor	: '0 0',
				border	: false,
				layout	: 'border',
				items	: [{
					region	: 'west',
					layout	: 'accordion',
					width	: 150,
					split	: true,
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