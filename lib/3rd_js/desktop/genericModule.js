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
			iconCls	: 'bogus',
			handler	: this.createWindow,
			scope	: this
		}
	},

	createWindow : function(){
		var desktop = this.app.getDesktop();
		var win = desktop.getWindow(this.contentItem.windowId);
		this.contentItem.region = 'center';
		this.contentItem.title = '';
		if(this.contentItem.xtype=='simpleiframe'){
			delete this.contentItem.iconCls;
			//delete this.contentItem.title;
		}
		if(!win){
			win = desktop.createWindow({
				id				: this.windowId,
				title			: this.text,
				width			: 640,
				height			: 480,
				iconCls			: this.contentItem.iconCls,
				layout			: 'border',
				items			: [this.contentItem],
				animCollapse	: false,
				constrainHeader	: true
			});
		}
		//win.show();
		return win;
	}
});
