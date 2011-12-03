Ext.define('ux.QTPreview', {
    extend			: 'Ext.Panel',
	alias			: 'widget.QTPreview',
	image_width		: 130,
	stateful		: true,
    imgPrefix		: '/cache/videothumb/',
	initComponent	: function() {
		var that = this;
		var qtembedid			= Ext.id();;
		var thumbnailid			= Ext.id();
		var previewid			= Ext.id();
		var combopreviewlengthid= Ext.id();
		var volumesliderid		= Ext.id();
		var buttonplayid		= Ext.id();
		var buttonpauseid		= Ext.id();
		var qtembeded			= null;

		var embed = true;
		console.log('id',qtembedid);
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
			Ext.getCmp(that.viewid).setWidth(that.thumbnailsStore.data.items.length * that.image_width) ;
		}

		that.addEvents('selectimg');

		Ext.define('thumbnails', {
			extend	: 'Ext.data.Model',
			fields	: [
				'url','ts','tss'
			]
		});

		that.thumbnailsStore = Ext.create('Ext.data.Store',{
			model				: 'thumbnails',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				url					: 'p/QDVideoFileHelper.thumbnails/',
				extraParams			:	{
					nbFrames			: 30,
					forceThumb			: true
				},
				reader				: {
					type				: 'json',
					root				: 'thumbs'
				}
			}
		});

		that.thumbnailsStore.on('load', function(options){
			 evtResize();
		});

		that.loadPreview = function(filename){
			that.filename = filename;
			that.thumbnailsStore.load({
				params	: {
					videoFile	: filename
				}
			});
			//if(embed) document[qtembedid].SetURL('/cache/videothumb/38e5823a0a46afb6fcc998834516e3c4/sample.avi');
			//document[qtembedid].Play();
		}
		var getQT = function(){
			if(!qtembeded){
				qtembeded = document.getElementsByName(qtembedid)[0];;
				//console.log('document.getElementsByName("'+qtembedid+'")[0]');
			}
			return qtembeded;
		}
		var qtStop = function (){
			try{
				getQT().Stop();
			}catch(E){}
		}
		var qtPlay = function (){
			try{
				getQT().Play();
			}catch(E){}
		}
		var qtMute = function (){
			qtVol(0);
			Ext.getCmp(volumesliderid).setValue(0);
		}
		var qtVol = function (vol){
			try{
				getQT().SetVolume(vol);
			}catch(E){}
		}
		var qtUrl = function (url){
			console.log(url);
			try{
				getQT().SetURL(url);
			}catch(E){}
		}

		Ext.apply(this,{
			layout			: 'border',
			listeners		: {
				resize			: evtResize
			},
			items			:	[{
				region			: 'center',
				xtype			: 'panel',
				monitorResize	: true,
				style			: "background: black",
				id				: previewid,
				bbar			: [{
					xtype			: 'button',
					text			: 'play',
					id				: buttonplayid,
					disabled		: true,
					stateful		: false,
					iconCls			: 'iconPlay',
					handler			: qtPlay
				},{
					xtype			: 'button',
					text			: 'pause',
					id				: buttonpauseid,
					disabled		: true,
					stateful		: false,
					iconCls			: 'iconPause',
					handler			: qtStop
				},{
					xtype			: 'slider',
					id				: volumesliderid,
					increment		:  10,
					minValue		:   0,
					stateful		: false,
					maxValue		: 255,
					width			: 60,
					listeners		:{
						change			: function(slider,newValue){
							qtVol(newValue);
						}
					}
				},{
					xtype			: 'button',
					text			: 'mute',
					iconCls			: 'iconMute',
					handler			: qtMute
				},'->',{
					xtype			: 'combo',
					stateful		: false,
					width			: 40,
					id				: combopreviewlengthid,
					store			: {
						xtype			: 'store',
						fields			: ['d'],
						data			: [{d : '5'},{d : '10'},{d : '15'},{d : '20'},{d : '25'},{d : '35'}]
					},
					queryMode		: 'local',
					displayField	: 'd',
					valueField		: 'd',
					value			: '15'
				}],
				html			:
					'<embed name="'+qtembedid+'" '+
						' autoplay			="false" '+
						' src				="skins/resources/blank.mov"	'+
						' type				="video/quicktime"				'+
						' cache				="false"						'+
						' width				="100%"							'+
						' height			="100%"							'+
						' scale				="aspect"						'+
						' controller		="true"							'+
						' bgcolor			="black"						'+
						' enablejavascript	="true"							'+
						' postdomevents		="true"							'+
						' pluginspace		="http://www.apple.com/quicktime/download/" '+
					'></embed>',
				listeners		: {
					afterrender	: function(){
						getQT().addEventListener('qt_volumechange',function(a,b,c){
							Ext.getCmp(volumesliderid).setValue(getQT().getVolume())
						});
						getQT().addEventListener('qt_play',function(a,b,c){
							Ext.getCmp(buttonplayid ).setDisabled(true);
							Ext.getCmp(buttonpauseid).setDisabled(false);
						});
						getQT().addEventListener('qt_pause',function(a,b,c){
							Ext.getCmp(buttonplayid ).setDisabled(false);
							Ext.getCmp(buttonpauseid).setDisabled(true);
						});
						getQT().addEventListener('qt_load',function(a,b,c){
							Ext.getCmp(buttonplayid ).setDisabled(true);
							Ext.getCmp(buttonpauseid).setDisabled(false);
						});
						getQT().addEventListener('qt_ended',function(a,b,c){
							Ext.getCmp(buttonplayid ).setDisabled(false);
							Ext.getCmp(buttonpauseid).setDisabled(true);
						});
					},
					resize : function(e, HTMLElement, eOpts){
						if(embed) {
							getQT().style.width = e.getWidth();//eeee
							getQT().style.height = e.getHeight()-30;//eeee
						}
					}
				}
			},{
				region		: 'south',
				bodyCls		: 'thumbnail-view overFlow-H',
				border		: true,
				style		: "background: white;font: 11px Arial, Helvetica, sans-serif;",
				layout		: 'fit',
				flex		: 0,
				height		: 85,
				stateful	: false,
				id			: thumbnailid,
				//autoScroll	: true,
				items		: Ext.create('Ext.view.View', {
					store			: that.thumbnailsStore,
					id				: that.viewid,
					width			: 2000,
					flex			: 0,
					height			: 85,
					trackOver		: true,
					stateful		: false,
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
						selectionchange	: function(dv, nodes ){
							var l = nodes.length,
								s = l !== 1 ? 's' : '';
							if(nodes.length>0){
								var loadingPreview = Ext.getCmp(that.id).setLoading(true);
								qtStop();
								Ext.Ajax.request({
									url		: 'p/QDVideoFileHelper.makeVideoPreview/',
									params	: {
										videoFile	: that.filename,
										start		: nodes[0].get('tss'),
										duration	: Ext.getCmp(combopreviewlengthid).getValue()
									},
									success : function(rawRes){
										loadingPreview.hide();
										try{
											var res = Ext.JSON.decode(rawRes.responseText);
											qtStop();
											qtUrl(that.imgPrefix + res.folderMD5 +'/'+res.preview+'?ts='+nodes[0].get('tss'));
											qtMute();
										}catch(E){
											console.log('erreur',E);
										}
									}
								});
							}
						}
					}
				})
			}]
		});
		this.callParent(arguments);
	}
});