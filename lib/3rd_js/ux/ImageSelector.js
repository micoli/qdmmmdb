/**
* @class Ext.ux.ImageSelector
* @extends Ext.Panel
*

* @license Ext.ux.SimpleIFrame.js is licensed under the terms of the Open Source
* LGPL 3.0 license. Commercial use is permitted to the extent that the
* code/component(s) do NOT become part of another Open Source or Commercially
* licensed development library or toolkit without explicit permission.
*
* <p>License details: <a href="http://www.gnu.org/licenses/lgpl.html"
* target="_blank">http://www.gnu.org/licenses/lgpl.html</a></p>
*
*/


Ext.define('Ext.ux.ImageSelector', {
	extend			: 'Ext.Panel',
	alias			: 'widget.ImageSelector',
	collapsible		: true,
	requires		: ['Ext.panel.*'],
	imgPrefix		: 'p/QDMediaDBProxy.proxyImg/?c=100x100&u=',
    //onSelectImg		: Ext.emptyFn,
	initComponent	: function(){
		var that = this;
		that.viewid = Ext.id();

		that.selectByUrl= function (url){
			var select = that.store.find('url',url);
			if(select){
				try{
					Ext.getCmp(that.viewid).getSelectionModel().select(that.store.getAt(select));
				}catch(E){
					//console.log(E);
				}
			}
		}
		that.addEvents('selectimg');
		Ext.apply(this,{
			bodyCls : 'image-view',
			border	: true,
			style	: "background: white;font: 11px Arial, Helvetica, sans-serif;",
			layout	: 'fit',
			items	: Ext.create('Ext.view.View', {
				store			: that.store,
				id				: that.viewid,
				autoScroll		: true,
				trackOver		: true,
				selModel		: 'SINGLE',
				overItemCls		: 'images-view-x-item-over',
				selectedItemCls	: 'images-view-x-item-selected',
				itemSelector	: 'div.images-view-thumb-wrap',
				emptyText		: 'No images to display',
				tpl				: [
					'<tpl for=".">',
						'<div class="images-view-thumb-wrap" id="{url}">',
						'<div class="images-view-thumb"><img src="{imgPrefix}{url}" title="{url}"></div>',
						'<span class="x-editable">{w}x{h}</span></div>',
					'</tpl>',
					'<div class="x-clear"></div>'
				],
				prepareData		: function(data) {
					Ext.apply(data, {
						shortName	: Ext.util.Format.ellipsis(data.name, 15),
						sizeString	: Ext.util.Format.fileSize(data.size),
						dateString	: Ext.util.Format.date(data.lastmod, "m/d/Y g:i a"),
						imgPrefix	: that.imgPrefix
					});
					return data;
				},
				listeners		: {
					selectionchange: function(dv, nodes ){
						var l = nodes.length,
							s = l !== 1 ? 's' : '';
                        if(nodes.length>0){
                            that.fireEvent('selectimg',nodes);
                        }
						//this.up('panel').setTitle('Simple DataView (' + l + ' item' + s + ' selected)');
					}
				}
			})
		});
		this.callParent(arguments);
	}
});


