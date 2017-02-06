Ext.define('qd.mediadb.serieEditor', {
	extend			: 'Ext.Window',
	alias			: 'widget.qd.mediadb.serieEditor',
	initComponent	: function() {
		var that = this;
		that.textchooseserieid = Ext.id();
		that.gridchooseserieid = Ext.id();
		that.butchooseserieid = Ext.id();

		Ext.define('chooseserie', {
			extend	: 'Ext.data.Model',
			fields	: [
				'name'		,
				'lang'		,
				'Overview'	,
				'seriesid'	,
				'year',
				'banner'
			]
		});

		that.chooseserieStore = Ext.create('Ext.data.Store',{
			model				: 'chooseserie',
			//pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDSeriesProxy.chooseSerie/',
				reader				: {
					type				: 'json',
					root				: 'results'
				}
			},
			sortOnLoad  : true,
			sorters: [{
				sorterFn: function(o1, o2){
					var rank1 = o1.get('year');
					var rank2 = o2.get('year');

					if (rank1 === rank2) {
							return 0;
					}

					return rank1 > rank2 ? -1 : 1;
				}
			}],
			listeners :{
				load : function (r){
					if (this.proxy.reader.jsonData.seriesid){
						var resQuery = Ext.getCmp(that.gridchooseserieid).store.queryBy(function(record,id){
							return record.get('seriesid') == this.proxy.reader.jsonData.seriesid;
						});
						if(resQuery.length>0){
							Ext.getCmp(that.gridchooseserieid).getSelectionModel().select(resQuery.items)
						}
					}else{
						if(that.chooseserieStore.getCount()>0 && that.chooseserieStore.getAt(0)){
							Ext.getCmp(that.gridchooseserieid).getSelectionModel().doSelect([that.chooseserieStore.getAt(0)]);
						}
					}
				}
			}
		})

		var searchSerie = function(){
			Ext.getCmp(that.gridchooseserieid).store.load({
				params	: {
					s		: Ext.getCmp(that.textchooseserieid).getValue(),
					p		: that.record.get('fullname')
				}
			});
		}

		Ext.apply(this,{
			width	: 620,
			height	: 400,
			modal	: true,
			layout	: 'border',
			items	: [{
				xtype			: 'grid',
				region			: 'center',
				id				: that.gridchooseserieid,
				store			: that.chooseserieStore,
				selType			: 'checkboxmodel',
				tbar			: [{
					xtype			: 'tbtext',
					text			: 'Serie : ',
				},{
					xtype			: 'textfield',
					id				: that.textchooseserieid,
					label			: 'serie',
					width			: 250,
					value			: '',
					enableKeyEvents	: true,
					listeners		: {
						keypress: function(ob,e){
							if (e.getCharCode()==13){
								searchSerie();
							}
						}
					}
				},{
					xtype		: 'button',
					text		: 'search',
					id			: that.butchooseserieid,
					listeners	: {
						click		:  searchSerie
					}
				}],
				columns			: [
					{header: "name",	width: 200,	dataIndex: 'name',	sortable: true},
					{header: "lang",	width:  80,	dataIndex: 'lang',	sortable: true},
					{header: "year",	width:  80,	dataIndex: 'year',	sortable: true},
					{header: "banner",	width:  205,	dataIndex: 'banner',	sortable: false,
						renderer:function(v){
							if(v!='') return '<img  style="height:35px;width:190px;border: 1px solid #AAAAAA;float: left; margin: 2px; " src="p/QDMediaDBProxy.proxyImg/?u=http://thetvdb.com/banners/'+v+'">'
						}
					}
				]
			}],
			buttons :[{
				text	: 'ok',
				handler	: function(){
					var recordSelected = Ext.getCmp(that.gridchooseserieid).getSelectionModel().getSelection();
					if (recordSelected && recordSelected[0]){
						var w = Ext.MessageBox.wait('mise à jour');
						Ext.AjaxEx.request({
							url		: 'p/QDSeriesProxy.setSerieFromPath/',
							params	: {
								m		: that.displayMode,
								p		: that.record.get('fullname'),
								i		: recordSelected[0].get('seriesid')
							},
							success : function(res){
								var r = Ext.JSON.decode(res.responseText)
								if(that.displayMode=='create'){
									if(r.ok==false){
										return;
									}
									that.record = that.record.appendChild({
										text : r.results.name,
										tvdb : 'series',
										leaf : false
									})
								}
								Ext.getCmp(that.textchooseserieid).setValue(r.results.name);
								that.record.set('tvdb','serie');
								that.record.expand();
								Ext.each(that.record.childNodes,function(v){
									v.set('tvdb','season');
								});
								w.hide();
								//refreshNodeColumns(that.record);
							}
						});
						that.hide();
					}else{
						Ext.MessageBox.alert('Error','rien de selectionné');
					}
				}
			},{
				text : 'cancel',
				handler : function(){
					that.close();
				}
			},{
				text	: 'manual',
				handler	: function(){
					Ext.MessageBox.prompt('id', 'Please enter the TVDB ID:', function(btn,text){
						if(btn=='ok'){
							var w = Ext.MessageBox.wait('mise à jour');
							Ext.AjaxEx.request({
								url		: 'p/QDSeriesProxy.setSerieFromPath/',
								params	: {
									m		: that.displayMode,
									p		: that.record.get('fullname'),
									i		: text
								},
								success : function(res){
									var r = Ext.JSON.decode(res.responseText)
									Ext.getCmp(that.textchooseserieid).setValue(r.results.name);
									that.record.set('tvdb','serie');
									that.record.expand();
									Ext.each(that.record.childNodes,function(v){
										v.set('tvdb','season');
									});
									w.hide();
									//refreshNodeColumns(that.record);
								}
							});
							that.hide();
						}
					});
				}

			}],
			listeners	:{
				show : function(){
					if(that.displayMode=='edit'){
						Ext.AjaxEx.request({
							url		: 'p/QDSeriesProxy.getSerieFromPath/',
							params	: {
								p		: that.record.get('fullname')
							},
							success : function(res){
								var r = Ext.JSON.decode(res.responseText)
								Ext.getCmp(that.textchooseserieid).setValue(r.results.name);
								searchSerie();
							}
						});
					}
				}
			}
		});
		this.callParent(arguments);
	}
});