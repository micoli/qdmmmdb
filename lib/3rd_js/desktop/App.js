	/*

	This file is part of Ext JS 4

	Copyright (c) 2011 Sencha Inc

	Contact:  http://www.sencha.com/contact

	GNU General Public License Usage
	This file may be used under the terms of the GNU General Public License version 3.0 as published by the Free Software Foundation and appearing in the file LICENSE included in the packaging of this file.  Please review the following information to ensure the GNU General Public License version 3.0 requirements will be met: http://www.gnu.org/copyleft/gpl.html.

	If you are unsure which license is appropriate for your use, please contact the sales department at http://www.sencha.com/contact.

	*/
	/*!
	* Ext JS Library 4.0
	* Copyright(c) 2006-2011 Sencha Inc.
	* licensing@sencha.com
	* http://www.sencha.com/license
	*/
	/*requires	: [
		'Ext.ux.SimpleIFrame',
		'qd.nzb.NZBPanel',
		'qd.sabnzbd.sabnzbdPanel',
		'qd.mediadb.seriePanel',
		'qd.mediadb.moviePanel'
	],*/
	Ext.define('MyDesktop.App', {
	extend: 'Ext.ux.desktop.App',

	requires: [
		'Ext.window.MessageBox',
		'Ext.ux.desktop.ShortcutModel',
		'MyDesktop.SystemStatus',
		'MyDesktop.genericModule',
		'MyDesktop.Settings'
	],

	init: function() {
		// custom logic before getXYZ methods get called...
		var that = this;
		that.appItems = [
			{title		: 'NZB'			,xtype	: 'qd.nzb.NZBPanel'			,id		: 'mainNZBPanel'	},
			{title		: 'SabNzbd'		,xtype	: 'qd.sabnzbd.sabnzbdPanel'	,autoTitle	: true			,iconCls:'icon-sabnzbd'},
			{title		: 'Series'		,xtype	: 'qd.mediadb.seriePanel'								},
			{title		: 'Movies'		,xtype	: 'qd.mediadb.moviePanel'								}
		];
		Ext.each(QD_GBL_CONF.mediadb.helperSite,function(item){
			that.appItems.push({
				xtype		: 'simpleiframe',
				title		: item.title	,
				bodyBorder	: false			,
				src			: item.url		,
				closable	: true			,
				iconCls		: item.iconCls?item.iconCls:''
			});
		});
		Ext.each(this.appItems,function(v,k){
			v.text = v.title;
			v.windowId=v.xtype+'-'+v.title;
			v.appType = v.windowId;
		});
		this.callParent();
		// now ready...
	},

	getModules : function(){
		var modules = [
			new MyDesktop.SystemStatus()
		];
		Ext.each(this.appItems,function(v,k){
			modules.push(new MyDesktop.genericModule(v));
		});

		return modules;
	},

	getDesktopConfig: function () {
		var me = this, ret = me.callParent();
		var data = [{
			name	: 'System Status',
			iconCls	: 'cpu-shortcut',
			module	: 'systemstatus'
		}];
		Ext.each(this.appItems,function(v,k){
			data.push({
				name	: v.title,
				iconCls	: v.iconCls?v.iconCls:'cpu-shortcut',
				module	: v.windowId,
				appType	: v.windowId
			});
		});

		return Ext.apply(ret, {
			//cls: 'ux-desktop-black',
			contextMenuItems: [{
				text		: 'Change Settings', handler: me.onSettings, scope: me
			}],
			shortcuts	: Ext.create('Ext.data.Store', {
				model		: 'Ext.ux.desktop.ShortcutModel',
				data		: data
			}),
			wallpaper		: './modules/desktop/wallpapers/desktop2.jpg',
			wallpaperStretch: false
		});
	},

	// config for the start menu
	getStartConfig : function() {
		var me	= this;
		var ret	= me.callParent();
		return Ext.apply(ret, {
			title		: 'Media manager',
			iconCls		: 'user',
			height		: 300,
			toolConfig	: {
				width		: 100,
				items		: [{
					text:'Settings',
					iconCls:'settings',
					handler: me.onSettings,
					scope: me
				}]
			}
		});
	},
	getTaskbarConfig: function () {
		var ret = this.callParent();
		return Ext.apply(ret, {
			quickStart: [/*{
				name: 'Accordion Window',
				iconCls: 'accordion',
				module: 'acc-win'
			},{
				name: 'Grid Window',
				iconCls: 'icon-grid',
				module: 'grid-win'
			}*/],
			trayItems: [{
				xtype: 'trayclock', flex: 1
			}]
		});
	},
	onSettings: function () {
		var dlg = new MyDesktop.Settings({
			desktop: this.desktop
		});
		dlg.show();
	}
});