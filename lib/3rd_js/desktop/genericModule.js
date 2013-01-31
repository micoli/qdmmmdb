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

var windowIndex = 0;

Ext.define('MyDesktop.genericModule', {
	extend: 'Ext.ux.desktop.Module',

	init : function(contentItem){
		this.contentItem = contentItem;
		this.launcher = {
			text	: contentItem.title,
			iconCls	: 'icon-app-win-'+contentItem.xtype.replace(/\./g,'-'),
			handler	: this.createWindow,
			scope	: this
		}
	},

	createWindow : function(){
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.contentItem.windowId);

		this.contentItem.region = 'center';
		delete this.contentItem.title;

		if(this.contentItem.xtype=='simpleiframe'){
			delete this.contentItem.iconCls;
		}
		if(this.contentItem.iconCls=='auto'){
			delete this.contentItem.iconCls;
		}
		if(!win){
			win = desktop.createWindow({
				id				: this.windowId,
				title			: this.text,
				width			: desktop.getWidth(),
				height			: desktop.getHeight()-40,
				iconCls			: 'icon-app-win-'+this.contentItem.xtype.replace(/\./g,'-'),
				layout			: 'border',
				items			: [this.contentItem],
				animCollapse	: false,
				constrainHeader	: true
			},MyDesktop.genericWindow);
			win;
		}
		win.show();
		return win;
	}
});
