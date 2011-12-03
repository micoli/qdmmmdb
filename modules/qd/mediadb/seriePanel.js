Ext.define('qd.mediadb.seriePanel', {
    extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.seriePanel',
	//requires		: ['Ext.ux.base64'],
	initComponent	: function() {
		var that = this;
		that.gridfilesid = Ext.id();
		that.treeserieid = Ext.id();
		that.pathname	 = null;

		that.loadFilesGrid	= function(pathname ){
			if (!pathname && that.pathname){
				pathname = that.pathname;
			}
			Ext.getCmp(that.gridfilesid).pathname = pathname ;
			Ext.getCmp(that.gridfilesid).store.removeAll()
			Ext.getCmp(that.gridfilesid).store.load({
				params :{
					fullpath	: pathname,
					only2Rename	: Ext.getCmp('only2Rename').pressed
				}
			});
			that.pathname = pathname;
		};
		that.clickOnTreeFolderNode	 = function(view, record, HTMLElement, index, evt, eOpts ){
			if (Ext.getCmp(that.treeserieid).getSelectionModel().getSelection()[0].data.id == record.data.id){
				if(record.data.fullname!=-1){
					that.loadFilesGrid(record.data.fullname);
					/*if (!node.attributes.tvdb){
						Ext.create('qd.mediadb.serieEditor',{
							record	: node
						}).show();
					}*/
				}
			}
		};

		Ext.define('serie', {
			extend: 'Ext.data.Model',
			fields: ['id', 'text', 'fullname','rootDrive','tvdb']
		});

		that.treeStore = Ext.create('Ext.data.TreeStore',  {
			model		: 'serie',
			folderSort	: true,
			root: {
				text	: 'Series',
				id		: 'SeriesRoot',
				expanded: true
			},
			proxy		: {
				type		: 'ajax',
				url			: 'p/QDSeriesProxy.getSeriesTree/'
			}
		});

		Ext.define('episodeModel', {
			extend	: 'Ext.data.Model',
			fields	: [
				'id'			,
				'Director'		,
				'EpisodeName'	,
				'EpisodeNumber'	,
				'FirstAired'	,
				'Overview'		,
				'SeasonNumber'	,
				'filename'		,
				'poster'
			]
		});

		that.episodeStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords	: true,
			groupField				: 'SeasonNumber',
			model					: 'episodeModel',
			sorters		: [{
				property	: 'SeasonNumber',
				direction	: 'ASC'
			},{
				property	: 'EpisodeNumber',
				direction	: 'ASC'
			}]
		});

		var episodeStoreFeature = Ext.create('Ext.grid.feature.Grouping',{
			groupHeaderTpl: 'Season: {name} ({rows.length} Episode{[values.rows.length > 1 ? "s" : ""]})'
		});

		Ext.define('fileModel', {
			extend	: 'Ext.data.Model',
			fields	: [
				'filename'		,
				'ext'			,
				'saison'		,
				'episode'		,
				'episodeName'	,
				'filesize'		,
				'Overview'		,
				'md5'
			]
		});

		that.fileStore = Ext.create('Ext.data.Store',{
			model				: 'fileModel',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				url					: 'p/QDSeriesProxy.getFiles/',
				reader				: {
					type				: 'json',
					root				: 'results',
					totalProperty		: 'count'
				}
			},
			listeners			: {
				load			: function(r,b){
					var str = "";
					var jsonData = r.proxy.reader.jsonData;
					if (r.proxy.reader.jsonData.bannerImg){
						str = str + '<img style="border: 1px solid #AAAAAA;float: left; margin: 2px; " src="p/QDMediaDBProxy.proxyImg/?u=http://thetvdb.com/banners/_cache/'+jsonData.bannerImg+'">';
					}
					if (r.proxy.reader.jsonData.bannerText){
						str = str + r.proxy.reader.jsonData.bannerText;
					}
					Ext.getCmp('bannertext' ).body.update(str);

					/*if(r.proxy.reader.jsonData.serieHTML){
						Ext.getCmp('serieHTML' ).body.update(Ext.ux.base64.decode(jsonData.serieHTML));
					}else{
						Ext.getCmp('serieHTML' ).body.update('');
					}*/
					Ext.getCmp(that.gridfilesid).SerieName = jsonData.serieName;

					if (jsonData && jsonData.arrSerie && jsonData.arrSerie.Episode){
						var records = [];
						Ext.each(jsonData.arrSerie.Episode,function(item,key){
							records[key]=new episodeModel(item);
						});
						that.episodeStore.removeAll();
						that.episodeStore.loadRecords(records);
					}
				}
			}
		});

		that.ctxMenu = Ext.create('Ext.menu.Menu', {
			items	: [{
				text	: 'set Serie',
				handler	: function(a,b){
					var node = Ext.getCmp(that.treeserieid).getSelectionModel().getSelection()[0];
					Ext.create('qd.mediadb.serieEditor',{
						record	: node
					}).show();
				}
			}]
		});

		var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 2
		});


		Ext.apply(this,{
			layout		: 'border',
			stateful	: false,
			items		: [{
				xtype		: 'tabpanel',
				region		: 'west',
				activeTab	: 0,
				width		: 300,
				items		: [{
					xtype			: 'treepanel',
					title			: 'folders',
					id				: that.treeserieid,
					lines			: true,
					store			: that.treeStore,
					loadMask		: true,
					split			: true,
					rootVisible		: true,
					multiSelect		: false,
					useArrows		: true,
					stateful		: false,
					listeners		: {
						itemclick		: that.clickOnTreeFolderNode,
						itemcontextmenu	: function(view, rec, node, index, e){
							e.stopEvent();
							that.ctxMenu.showAt(e.getXY());
							return false;
						}
					},
					tbar			: [{
						text			: 'refresh',
						handler			: function(){
							that.treeStore.proxy.extraParams.refresh = 1;
							that.treeStore.getRootNode().removeAll(false);
							that.treeStore.load({
								node: Ext.getCmp(that.treeserieid).getRootNode()
							});
							that.treeStore.sync();
						}
					}],
					columns			:[{
						xtype			: 'treecolumn',
						header			: 'folder',
						width			: 200,
						dataIndex		: 'text'
					},{
						header			: 'TVDB',
						width			: 32,
						dataIndex		: 'tvdb',
						renderer		: function (value,style,record){
							var rtn = '';
							switch (record.get('tvdb')){
								case 'serie' :
									rtn = '<img src="skins/resources/tvdb.jpg">';
								break;
								case 'season' :
									rtn = '<img src="skins/resources/tvdb-season.jpg">';
								break;
							}
							return rtn;
						}
					},{
						header			: 'action',
						width			: 100,
						cbIconCls		: 'tree-icon-edit',
						callback		: function(tree,node,action){
							Ext.create('qd.mediadb.movieEditor',{
								record	: node
							}).show();
							//console.log(tree,node,action);
						}
					}]
				}]
			},{
				layout		: 'border',
				region		: 'center',
				items		: [{
					height		: 100,
					region		: 'north',
					id			: 'bannertext',
					frame		: true,
					border		: false,
					autoScroll	: true
				},{
					xtype			: 'tabpanel',
					region			: 'center',
					activeTab		: 0,
					deferredRender	: false,
					items			: [{
						xtype			: 'grid',
						id				: that.gridfilesid,
						stateful		: false,
						title			: 'Files',
						loadMask		: true,
						selModel		: Ext.create('Ext.selection.CheckboxModel'),
						tbar			: [{
							text			: 'Select all',
							handler			: function(){
								Ext.getCmp(that.gridfilesid).getSelectionModel().selectAll();
							}
						},{
							text			: 'Rename',
							handler			: function(){
								var arrResult = {};
								var pathName = Ext.getCmp(that.gridfilesid).pathname;
								arrResult[Ext.ux.base64.encode(pathName)]={};
								arrResult[Ext.ux.base64.encode(pathName)].modified=[];
								Ext.each(Ext.getCmp(that.gridfilesid).getSelectionModel().getSelection(),function(v,k){
									arrResult[Ext.ux.base64.encode(pathName)].modified.push({
										//'old' : v.data.filename,
										//'new'    : v.data.episodeName,
										'new64'  : Ext.ux.base64.encode((v.get('episodeName')+' ').trim()),
										'saison' : v.get('saison'),
										'ext'    : v.get('ext'),
										'episode': v.get('episode'),
										'md5'    : v.get('md5'),
										serie    : Ext.ux.base64.encode(Ext.getCmp(that.gridfilesid).SerieName)
									});
								});
								console.log(arrResult);
								Ext.Ajax.request({
									url      : 'p/QDSeriesProxy.renameFiles/',
									params : {
										modified   : Ext.ux.base64.encode(Ext.JSON.encode(arrResult))
									},
									success : function(r){
										that.loadFilesGrid(pathName);
									}
								});
							}
						},{
							text			: 'Un-Select all',
							handler			: function(){
								Ext.getCmp(that.gridfilesid).getSelectionModel().deselectAll();
							}
						},{
							text			: 'Only to rename',
							id				: 'only2Rename',
							enableToggle	: true,
							pressed			: true,
							handler			: function(){
								that.loadFilesGrid(null);
							}
						},{
							text			: 'Invert Selection',
							handler			: function(){
								//Ext.getCmp(that.gridfilesid).getSelectionModel().clearSelections();
								alert('to do');
							}
						}],
						clicksToEdit		: 1,
						store				: that.fileStore,
						plugins				: [cellEditing],
						//plugins			: [this.expander],
						columns				: [
							{header: "Episode"	, width:   280,	dataIndex: 'episodeName'	, sortable: true,
								editor	:{
									xtype		: 'textfield',
									allowBlank	: false
								}
							},
							{header: "S"		, width:    30,	dataIndex: 'saison'			, sortable: true},
							{header: "E"		, width:    30,	dataIndex: 'episode'		, sortable: true},
							{header: "Name"		, width:   300,	dataIndex: 'filename'		, sortable: true},
							{header: "Type"		, width:    40,	dataIndex: 'ext'			, sortable: true},
							{header: "Size"		, width:    80,	dataIndex: 'filesize'		, sortable: true}
						]
					},/*{
						title		: 'Episodes',
						id			: 'serieHTML',
						html		: '',
						autoScroll	: true
					},*/{
						xtype			: 'grid',
						id				: 'gridEpisodes',
						title			: 'Episodes',
						store			: that.episodeStore,
						forceFit		: true,
						features		: [episodeStoreFeature],
						columns			: [
							{header	: "E"		, width	:  30,	dataIndex: 'EpisodeNumber'	, sortable: true	,flex	: 0,	resizable	: false},
							{header	: "Picture"	, width	:  80,	dataIndex: 'filename'		, sortable: true	,flex	: 0,	resizable	: false,
								renderer : function(val,style,record){
									style.style="height:50px";
									return (typeof(val)=='object'&&(val instanceof Array))?'':'<img width="78" src="p/QDMediaDBProxy.proxyImg/?u=http://thetvdb.com/banners/_cache/'+val+'" />';
								}
							},
							{header	: "Episode"	, width	: 280,	dataIndex: 'EpisodeName'	, sortable: true	,flex	: 1,	resizable	: true,
								renderer : function(val,style,record){
									var str = '<b>'+val+'</b>';
									var overview= record.get('Overview');
									if (overview){
										str += '<br /><i>'+overview+'</i>';
									}
									return str;
								}
							},
							{header	: "Date"	, width	: 120,	dataIndex: 'FirstAired'		, sortable: true	,flex	: 0	,	resizable	: false}
						]
					}]
				}]
			}]
		});
		this.callParent(this);
	}
});