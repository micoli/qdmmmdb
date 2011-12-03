/**
* @class Ext.ux.thumbnailSelector
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


Ext.define('Ext.ux.thumbnailSelector', {
	extend			: 'Ext.Panel',
	alias			: 'widget.thumbnailSelector',
	requires		: ['Ext.panel.*'],
	imgPrefix		: 'p/QDMediaDBProxy.proxyImg/?c=100x100&u=',
	image_width		: 130,
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
		
		var evtResize = function(){
			Ext.getCmp(that.viewid).setWidth(that.store.data.items.length * that.image_width) ;
		}

		that.store.on('load', function(options){
			 evtResize();
		});

		that.addEvents('selectimg');
		Ext.apply(this,{
			bodyCls		: 'thumbnail-view overFlow-H',
			border		: true,
			style		: "background: white;font: 11px Arial, Helvetica, sans-serif;",
			layout		: 'fit',
			flex		: 0,
			height		: 80,
			//autoScroll	: true,
			items	: Ext.create('Ext.view.View', {
				store			: that.store,
				id				: that.viewid,
				width			: 2000,
				flex			: 0,
				height			: 80,
				trackOver		: true,
				selModel		: 'SINGLE',
				overItemCls		: 'thumbnail-view-x-item-over',
				selectedItemCls	: 'thumbnail-view-x-item-selected',
				itemSelector	: 'div.thumbnail-view-thumb-wrap',
				emptyText		: 'No images to display',
				tpl				: [
					'<tpl for=".">',
						'<div class="thumbnail-view-thumb-wrap" id="{url}">',
						'<div class="thumbnail-view-thumb"><img height="50" src="{imgPrefix}{url}" title="{url}"></div>',
						'<span class="x-editable">{ts}</span></div>',
					'</tpl>',
					'<div class="x-clear"></div>'
				],
				prepareData		: function(data) {
					Ext.apply(data, {
						imgPrefix	: that.imgPrefix
					});
					return data;
				},
				listeners		: {
					resize			: function(){
						evtResize();
					},
					selectionchange	: function(dv, nodes ){
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