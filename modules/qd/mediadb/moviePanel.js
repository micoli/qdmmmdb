Ext.define('qd.mediadb.moviePanel', {
    extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.moviePanel',
	initComponent	: function() {
		var that = this;
		that.gridmoviesfilesid	= Ext.id();
		that.treemoviesPathid	= Ext.id();

		that.tbfilternfoTrueid	= Ext.id();
		that.tbfilternfoFalsid	= Ext.id();
		that.tbfilternfoDisaid	= Ext.id();

		that.tbfilterpostersTrueid	= Ext.id();
		that.tbfilterpostersFalsid	= Ext.id();
		that.tbfilterpostersDisaid	= Ext.id();

		that.groupFilter={};

		
		that.loadMoviesFilesGrid = function(pathname ){
			Ext.getCmp(that.gridmoviesfilesid).pathname = pathname ;
			Ext.getCmp(that.gridmoviesfilesid).store.removeAll()
			Ext.getCmp(that.gridmoviesfilesid).store.load({
				params :{
					fullpath	: pathname,
					only2Rename	: Ext.getCmp('only2RenameMovies').pressed
				}
			});
		};

		/*this.cellActions = new Ext.ux.grid.CellActions({
			callbacks:{
				'tree-icon-application-go':function(grid, record, action, value) {
						//Ext.ux.Toast.msg('Callback: icon-undo', 'You have clicked: <b>{0}</b>, action: <b>{1}</b>', value, action);
						Ext.create('qd.mediadb.movieEditor',{
							record	: record
						}).show();
				},
				'tree-icon-view-movie': function(grid, record, action, value){
					//console.log(grid, record, action, value);
					VLCOpenVideo(Ext.util.base64.decode(record.data.uncfilename64),record.data.title);
				}
			},
			align:'left'
		});*/
		Ext.define('movie', {
			extend: 'Ext.data.Model',
			fields: ['id', 'text', 'fullname','rootDrive','tvdb']
		});
		that.treeStore = Ext.create('Ext.data.TreeStore',  {
			model		: 'movie',
			folderSort	: true,
			root: {
				text	: 'Movies',
				id		: 'MoviesRoot',
				expanded: true
			},
			proxy		: {
				type		: 'ajax',
				url			: 'p/QDMoviesProxy.getMoviesTree/'
			},
            listeners : {
                load : function (store){
                    //debugChoosePanel
                    //Ext.getCmp(that.gridmoviesfilesid).store.load({	params :{name	: 'F1'}});
                }
            }
		});
		
		Ext.define('moviefile', {
			extend	: 'Ext.data.Model',
			fields	: [
				'rootPath'		,
				'fullpath'		,
				'newfilename'	,
				'title'			,
				'folder'		,
				'filename'		,
				'ext'			,
				'filesize'		,
				'pathfilename64',
				'md5'			,
				'srt'			,
				'poster'		,
				'fanart'		,
				'qdmmmdb'		,
				'nfo'			,
				'extrathumbs'	,
				'backdrop'		,
				'inFolder'
			]
		});

		that.moviefilesStore = Ext.create('Ext.data.Store',{
			model				: 'moviefile',
			groupField			: 'inFolder',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				url					: 'p/QDMoviesProxy.getMoviesFiles/',
				params				:{
					name				: 'M'
				},
				reader				: {
					type				: 'json',
					root				: 'results',
					totalProperty		: 'count'
				}
			},
            listeners : {
				load : function (store){
					//debugChoosePanel
					tbfilterhandler();
					setTimeout(function(){
						//openMovieEditor(store.getAt(0))
					},500);
                }
            }
		});

		var movieStoreFeature = Ext.create('Ext.grid.feature.Grouping',{
			groupHeaderTpl: 'Type: {name} ({rows.length} items{[values.rows.length > 1 ? "s" : ""]})'
		});

		that.ctxMenu = Ext.create('Ext.menu.Menu', {
			items	: [{
				text	: 'set Movie',
				handler	: function(a,b){
					openMovieEditor(that.ctxMenu.record);
				}
			}]
		});

		var openMovieEditor = function(record){
			Ext.getCmp(that.gridmoviesfilesid).getSelectionModel().select(record,true,true);
			Ext.create('qd.mediadb.movieEditor',{
				referenceRecord	: record,
				referenceGrid	: Ext.getCmp(that.gridmoviesfilesid),
				listeners		: {
					ok	:	function(result){
						console.log(result);
					}
				}
			}).show();
		}
		var tbfilterhandler = function(){
			Ext.each(Ext.getCmp(that.gridmoviesfilesid).getDockedItems()[0].items.items,function(v,k){
				if(v.toggleGroup && v.pressed){
					that.groupFilter[v.toggleGroup] = v.qdfilter;
				};
			})
			that.moviefilesStore.clearFilter();
			that.moviefilesStore.filter([
				{filterFn: function(item) {
					return (that.groupFilter.nfo==-1?true:item.get("nfo")==that.groupFilter.nfo)&&(that.groupFilter.poster==-1?true:item.get("poster")==that.groupFilter.poster);
				}}				
			]);
		}

		Ext.apply(this,{
			layout    : 'border',
			items     : [{
				xtype		: 'tabpanel',
				region		: 'west',
				activeTab	: 0,
				width		: 300,
				items		: [{
					xtype			: 'treepanel',
					title			: 'folders',
					id				: that.treemoviesPathid,
					lines			: true,
					store			: that.treeStore,
					loadMask		: true,
					split			: true,
					rootVisible		: true,
					multiSelect		: false,
					useArrows		: true,
					stateful		: false,
					listeners		: {
						itemclick		: function(node,record,el,idx,event){
							Ext.getCmp(that.gridmoviesfilesid).store.load({
								params :{
									name		: record.get('text')

								}
							});
						}
					},
					tbar			: [{
						text			: 'refresh',
						handler			: function(){
							that.treeStore.proxy.extraParams.refresh = 1;
							that.treeStore.getRootNode().removeAll(false);
							that.treeStore.load({
								node: Ext.getCmp(that.treemoviesPathid).getRootNode()
							});
							that.treeStore.sync();
						}
					}],
					columns:[{
						xtype		: 'treecolumn',
						header		: 'folder',
						width		: 200,
						dataIndex	: 'text'
					},{
						header		: 'OK',
						width		: 32
					}]
				}]
			},{
				xtype			: 'tabpanel',
				region			: 'center',
				activeTab		: 0,
				deferredRender	: false,
				items			: [{
					xtype			: 'grid',
					id				: that.gridmoviesfilesid,
					title			: 'Files',
					selType			: 'checkboxmodel',
					clicksToEdit	: 1,
					store			: that.moviefilesStore,
					rootVisible		: true,
					loadMask		: true,
					lines			: false,
					useArrows		: true,
					stateful		: false,
					tbar			: [/*{
						text			: 'Select all',
						handler			: function(){
							return;
							Ext.getCmp(that.gridmoviesfilesid).getSelectionModel().selectAll();
						}
					},{
						text			: 'Only to rename',
						id				: 'only2RenameMovies',
						enableToggle	: true,
						pressed			: false
					},{
						text			: 'Rename',
						handler			: function(){
							return;
							//var arrModified = Ext.getCmp(that.gridmoviesfilesid).getStore().data.items;
							var arrModified = Ext.getCmp(that.gridmoviesfilesid).getSelectionModel().selections.items;
							var pathname    = Ext.util.base64.encode(Ext.getCmp(that.gridmoviesfilesid).pathname);
							var arrResult = {
								modified : {}
							};
							Ext.each(arrModified,function(v,k){
								if (!arrResult.modified[pathname]) arrResult.modified[pathname]=[];
								arrResult.modified[pathname].push({
									'new64'  : Ext.util.base64.encode((v.data.rename+' ').trim()),
									'ext'    : v.data.ext,
									'md5'    : v.data.md5
								});
							});
							console.log(arrResult);
							Ext.Ajax.request({
								url      : 'p/QDMoviesProxy.renameMoviesFiles/',
								params : {
									modified   : Ext.util.base64.encode(Ext.util.JSON.encode(arrResult))
								},
								success : function(r){
									that.loadMoviesFilesGrid(Ext.getCmp(that.gridmoviesfilesid).pathname);
								}
							});
						}
					},{
						text		: 'Un-Select all',
						handler		: function(){
							return;
							Ext.getCmp(that.gridmoviesfilesid).getSelectionModel().clearSelections();
						}
					},{
						text		: 'Invert Selection',
						handler		: function(){
							return;
							alert('to do');
						}
					},{
						xtype		: 'tbseparator'
					},*/{
						xtype		: 'tbtext',
						text		: 'Filters'
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'nfo',
						qdfilter	: true,
						width		: 30,
						iconCls		: 'moviegrid-filter-nfoTrue',
						stateful	: false,
						handler		: tbfilterhandler
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'nfo',
						qdfilter	: false,
						width		: 30,
						iconCls		: 'moviegrid-filter-nfoFalse',
						pressed		: true,
						stateful	: false,
						handler		: tbfilterhandler
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'nfo',
						qdfilter	: -1,
						width		: 30,
						iconCls		: 'moviegrid-filter-nfoDisabled',
						stateful	: false,
						handler		: tbfilterhandler
					},{
						xtype		: 'tbseparator'
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'poster',
						qdfilter	: true,
						width		: 30,
						iconCls		: 'moviegrid-filter-posterTrue',
						stateful	: false,
						handler		: tbfilterhandler
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'poster',
						qdfilter	: false,
						width		: 30,
						iconCls		: 'moviegrid-filter-posterFalse',
						stateful	: false,
						handler		: tbfilterhandler
					},{
						xtype		: 'button',
						enableToggle:true,
						toggleGroup	: 'poster',
						qdfilter	: -1,
						width		: 30,
						iconCls		: 'moviegrid-filter-posterDisabled',
						stateful	: false,
						pressed		: true,
						handler		: tbfilterhandler
					},{
						xtype		: 'tbseparator'
					}],
					listeners	: {
						rowclick : function(grid,HTMLElement,rowIndex,columnIndex){
							var record = grid.getStore().getAt(rowIndex);
							console.log(record);
						}
					},
					viewConfig: {
						trackOver: false,
						listeners: {
						//itemclick		: that.clickOnTreeFolderNode,
							cellcontextmenu: function(view, cell, cellIndex,record, row, rowIndex, e){
								e.stopEvent();
								that.ctxMenu.record = record;
								that.ctxMenu.showAt(e.getXY());
								return false;
							}
						}
					},
					features	: [movieStoreFeature],
					autoFit		: true,
					columns		: [
						{header	: "&nbsp;"	, width:  20,	dataIndex: 'inFolder'		,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('inFolder')=='inFolder'?"moviegrid-icon-folder":"moviegrid-icon-avi";
								return "&nbsp;";
							}
						},{
							header			: 'action',
							xtype			: 'actioncolumn',
							width			: 30,
							items			: [{
								icon	: 'skins/resources/application_edit.png',
								handler	: function(grid, rowIndex, colIndex) {
									var record = grid.getStore().getAt(rowIndex);
									openMovieEditor(record);
								}
							}]
						},
						{header	: "folder/filename"	, width: 280,	dataIndex: 'folder'			,	sortable: true,	flex : 1},
						{header	: "filename"		, width: 280,	dataIndex: 'filename'		,	sortable: true,	flex : 1},
						{header	: "ext"				, width:  40,	dataIndex: 'ext'			,	sortable: true,	flex : 0},
						{header	: "size"			, width:  80,	dataIndex: 'filesize'		,	sortable: true,	flex : 0},
						{header	: "mmmdb"			, width:  30,	dataIndex: 'qdmmmdb'		,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('qdmmmdb')?"moviegrid-icon-nfo":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "nfo"				, width:  30,	dataIndex: 'nfo'			,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('nfo')?"moviegrid-icon-nfo":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "poster"			, width:  30,	dataIndex: 'poster'			,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('poster')?"moviegrid-icon-poster":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "fanart"			, width:  30,	dataIndex: 'fanart'			,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('fanart')?"moviegrid-icon-star":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "extra"			, width:  30,	dataIndex: 'extrathumbs'	,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('extra')?"moviegrid-icon-extra":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "backdrop"		, width:  30,	dataIndex: 'backdrop'		,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('backdrop')?"moviegrid-icon-backdrop":"moviegrid-icon-none";
								return "&nbsp;";
							}
						},
						{header	: "srt"				, width:  30,	dataIndex: 'srt'			,	sortable: true,	flex : 0,
							renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
								metaData.tdCls = record.get('srt')?"moviegrid-icon-srt":"moviegrid-icon-none";
								return "&nbsp;";
							}
						}
					]
				}]
			}]
		});
		this.callParent(arguments);
	}
});
/*
renderer : function(value, metaData, record, rowIndex, colIndex, store, view){
	if(record.get('poster')){
		metaData.style = "height:50px;";
		return '<img style="height:50px" src="p/QDMoviesProxy.proxyPosterImg/?i64='+record.get('poster')+'">';
	}else{
		return "&nbsp;";
	}
}
*/