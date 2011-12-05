Ext.define('qd.mediadb.movieEditor', {
		extend			: 'Ext.Window',
	alias			: 'widget.qd.mediadb.movieEditor',
	width			: 800,
	height			: 270,
	modal			: true,
	stateful		: false,
	maximizable		: true,
	maximized		: true,
	referenceGrid	: null,
	title			: 'Select Movie',
	tplPreview		: new Ext.XTemplate(
		'<tpl if="display">',
		'<div class="divPreview" style="width:100%;height:{mainHeight}px;background:url(p/QDMediaDBProxy.proxyImg/?u={backdrop})  center center no-repeat;border:2px solid #AAAAAA">',
			'<table class="heightPreviewBottom" style="width:100%;position:absolute;bottom:0;" cellspacing="0">',
				'<tr>',
					'<td style="width:200px;"></td>',
					'<td class="titlePreview" colspan="2">{title} <tpl if="year">({year})</tpl> <tpl if="originalTitle">{originalTitle}<br/></tpl></td>',
				'</tr>',
				'<tr>',
					'<td class="cellBottom">&nbsp;</td>',
					'<td class="cellBottom" style="padding-right: 20px;autoscroll:auto;vertical-align: top;">',
						'{summary}<br>',
					'</td>',
					'<td class="cellBottom castPreview" style="height:200px;autoscroll:auto;vertical-align: top;">',
						'<div><div class="titleCast">genre:</div>	<ul><tpl if="genre"><li>{genre}</li></tpl></ul><hr/></div>',
						'<div><div class="titleCast">director:</div><ul><tpl for="director"	><li>{.}</li></tpl></ul><hr/></div>',
						'<div><div class="titleCast">actors:</div>	<ul><tpl for="actors"		><li>{.}</li></tpl></ul><hr/></div>',
						'<div><div class="titleCast">country:</div>	<ul><tpl for="country"	><li>{.}</li></tpl></ul><hr/></div>',
				'</td>',
				'</tr>',
			'</table>',
			'<div style="position:absolute;bottom:20px;left:30px;width:160px;height:240px;">',
				'<img src="p/QDMediaDBProxy.proxyImg/?c=154x0&u={poster}" style="width:154px;border:2px solid #ffffff" />',
			'</div>',
		'</div>',
		'</tpl>'
	),
	tplList			: new Ext.XTemplate(
		'<b>{title}</b>',
		'<tpl if="directors"><div style="width:300px;word-wrap: break-word;white-space: normal;"><u>{directors}</u></div></tpl>',
		'<tpl if="actors"	><div style="width:300px;word-wrap: break-word;white-space: normal;"><i>{actors}</i></div></tpl>',
		'<tpl if="overview"	><div style="width:196px;word-wrap: break-word;white-space: normal;height:80px;overflow-y:auto;">{overview}</div></tpl>'
	),
	initComponent	: function() {
		var that = this;
		var detailpanelid		= Ext.id();
		var gridposterid		= Ext.id();
		var gridbackdropid		= Ext.id();
		var combomoviesearchid	= Ext.id();
		var textchoosemoviesid	= Ext.id();
		var tabpanelid			= Ext.id();
		var autoloadchoosegrid	= Ext.id();
		var gridchoosemoviesid	= Ext.id();
		var autoloadfirstingrid	= Ext.id();
		var previewid			= Ext.id();

		that.addEvents('ok','cancel');

		Ext.define('choosemovie', {
			extend	: 'Ext.data.Model',
			fields	: [
				'id'		,
				'title'		,
				'year'		,
				'actors'	,
				'directors'	,
				'overview'	,
				'poster'	,
				'engine'
			]
		});

		Ext.define('urls', {
			extend	: 'Ext.data.Model',
			fields	: [
				'url','w','h'
			]
		});

		var chooseMovieStore = Ext.create('Ext.data.Store',{
			model				: 'choosemovie',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				url					: 'p/QDMoviesProxy.chooseMovie/',
				reader				: {
					type				: 'json',
					root				: 'results',
					totalProperty		: 'count'
				}
			},
			listeners			:{
				load				: function (results){
					if (results.data.items && results.data.items.length>0){
						Ext.getCmp(gridchoosemoviesid).getSelectionModel().selectRange(0,0,false);
						if (Ext.getCmp(autoloadfirstingrid).getValue()){
							setEditor(chooseMovieStore.getAt(0));
						}
					}
				}
			}
		});

		var choosePosterStore = Ext.create('Ext.data.Store',{
			model				: 'urls',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				reader				: {
					type				: 'json'
				}
			}
		});

		var chooseBackdropStore = Ext.create('Ext.data.Store',{
			model				: 'urls',
			pruneModifiedRecords: true,
			proxy				: {
				type				: 'ajax',
				reader				: {
					type				: 'json'
				}
			}
		});

		var searchMovie = function(){
			Ext.getCmp(gridchoosemoviesid).store.load({
				params	: {
					m		: Ext.getCmp(textchoosemoviesid).getValue(),
					e		: Ext.getCmp(combomoviesearchid).getValue()
				}
			});
		};

		var updatePreview = function (res){
			if(res){
				that.currentRecord = res;
			}
			var detailPanel = Ext.getCmp(detailpanelid);
			if (typeof that.currentRecord.constructor.prototype=='object' && isEmpty(that.currentRecord)){
				that.tplPreview.overwrite(detailPanel.body,{display:false});
			}else{
				that.tplPreview.overwrite(detailPanel.body,Ext.apply({
					mainWidth	:detailPanel.getWidth() ,
					mainHeight	:detailPanel.getHeight()?detailPanel.getHeight():500,
					display		: true
				},
					that.currentRecord
				));
			}
		};

		var setEditor = function (record){
			disableSave();
			var loading = Ext.getCmp(tabpanelid).setLoading(true);
			Ext.Ajax.request({
				url		: 'p/QDMoviesProxy.chooseMoviesDetail/',
				params	: {
					i		: record.get('id'),
					e		: record.get('engine')
				},
				success : function(rawRes){
					loading.hide();
					//loading.destroy();
					try{
						var res = Ext.JSON.decode(rawRes.responseText);
						//Ext.getCmp(textchoosemoviesid).setValue(res.results.name);
						//Ext.getCmp('butchoosemovies').events.click.listeners[0].fn();
						//node.setText(res.results.title);
						updatePreview(res.data);

						choosePosterStore.removeAll();
						var allPosters = Ext.apply([],res.data.posters);
						choosePosterStore.add(allPosters);
						Ext.getCmp(gridposterid).selectByUrl(res.data.poster);

						chooseBackdropStore.removeAll();
						var allBackdrops = Ext.apply([],res.data.backdrops);
						chooseBackdropStore.add(allBackdrops);
						Ext.getCmp(gridbackdropid).selectByUrl(res.data.backdrop);

						Ext.getCmp(tabpanelid).setActiveTab(1);
						disableSave();
					}catch(E){
						console.log('exce',E);
					}
					//w.hide();
				},
				failure : function(res){
					loading.hide();
					loading.destroy();
				}
			});
		};

		var setCurrentRecord = function (rec){
			choosePosterStore.removeAll();
			chooseBackdropStore.removeAll();
			Ext.getCmp(textchoosemoviesid).setValue(rec.get('title'));
			var title = '';
			if (rec.get('inFolder')=='file'){
				title = rec.get('fullpath')+'/'+rec.get('folder')+'.'+rec.get('ext');;
			}else{
				title = rec.get('fullpath')+'/'+rec.get('filename')+'.'+rec.get('ext');;
			}
			that.filename = title;
			that.setTitle('Select Movie : '+title);
			updatePreview({});
			if (Ext.getCmp(autoloadchoosegrid).getValue()){
				Ext.getCmp(gridchoosemoviesid).getStore().load({
					params	: {
						m		: Ext.getCmp(textchoosemoviesid).getValue(),
						e		: Ext.getCmp(combomoviesearchid).getValue()
					}
				});
			}else{
				Ext.getCmp(gridchoosemoviesid).getStore().removeAll();
			}
			var t = that.referenceGrid.getSelectionModel().getLastSelected();
			var idx = that.referenceGrid.getStore().indexOf(t);
			Ext.getCmp('butprev').setDisabled(!(idx>0));
			Ext.getCmp('butnext').setDisabled(!(idx<that.referenceGrid.getStore().getCount()-1));
		};

		var saveDetails = function (){
			var w = Ext.MessageBox.wait('Updating....');
			Ext.Ajax.request({
				url		: 'p/QDMoviesProxy.setMoviesFromPath/',
				params	: {
					ref		: Ext.JSON.encode(that.referenceRecord.data),
					engine	: Ext.getCmp(combomoviesearchid).getValue(),
					record	: Ext.JSON.encode(that.currentRecord)
				},
				success : function(res){
					var data = Ext.JSON.decode(res.responseText);
					if(data.corrupted){
						console.log('corrupted');
						Ext.MessageBox.alert('error','Filename empty or invalid ...');
						return;
					}
					that.referenceRecord.beginEdit();
					for(var k in data){
						if (k && that.referenceRecord.fields.map[k]){
							that.referenceRecord.set(k,data[k]);
						}
					}
					that.referenceRecord.endEdit();
					that.referenceRecord.commit();
					if(that.afterSave){
						that.afterSave();
						that.afterSave=null;
					}
					//Ext.getCmp(textchoosemoviesid).setValue(r.results.name);
					//Ext.getCmp('butchoosemovies').events.click.listeners[0].fn();
					//node.setText(r.results.title);
					//Ext.ux.Toast.msg('mise à jour','fichier traité')
					//that.hide();
					/*that.fireEvent('ok',{
						id		: recordSelected.data.id,
						engine	: Ext.getCmp(combomoviesearchid).getValue(),
						record	: that.referenceRecord
					});*/
					w.hide();
				}
			});
		}

		var goPrevious	= function(){
			var t = that.referenceGrid.getSelectionModel().getLastSelected();
			var idx = that.referenceGrid.getStore().indexOf(t);
			that.referenceGrid.getSelectionModel().select(idx-1);
			that.referenceRecord = that.referenceGrid.getSelectionModel().getLastSelected();
			setCurrentRecord(that.referenceRecord);
		}

		var goNext		= function(){
			var t = that.referenceGrid.getSelectionModel().getLastSelected();
			var idx = that.referenceGrid.getStore().indexOf(t);
			that.referenceGrid.getSelectionModel().select(idx+1);
			that.referenceRecord = that.referenceGrid.getSelectionModel().getLastSelected();
			setCurrentRecord(that.referenceRecord);
		}

		var disableSave= function (){
			Ext.getCmp('buttsave'	).setDisabled(true);
			Ext.getCmp('butsaveprev').setDisabled(true);
			Ext.getCmp('butsavenext').setDisabled(true);
		}

		var enableSave= function (){
			Ext.getCmp('buttsave').setDisabled(true);

			Ext.getCmp('buttsave'	).setDisabled(false);
			Ext.getCmp('butsaveprev').setDisabled(false);
			Ext.getCmp('butsavenext').setDisabled(false);
		}

		Ext.apply(this,{
			layout			: 'border',
			tbar			: [{
				xtype			: 'tbtext',
				text			: 'Movies :&nbsp;'
			},{
				xtype			: 'textfield' ,
				id				: textchoosemoviesid,
				label			: 'serie',
				width			: 250,
				stateful		: false,
				enableKeyEvents	: true,
				listeners		: {
					keypress: function(ob,e){
						if (e.getCharCode()==13){
							searchMovie();
						}
					}
				}
			},{
				xtype			: 'combo',
				stateful		: true,
				width			: 100,
				id				: combomoviesearchid,
				store			: {
					xtype			: 'store',
					fields			: ['it'],
					data			: [{it : 'allocineapi'},{it : 'themoviedb'}]
				},
				queryMode		: 'local',
				displayField	: 'it',
				valueField		: 'it',
				value			: 'themoviedb'
			},{
				xtype		: 'button',
				text		: 'search',
				id			: 'butchoosemovies',
				listeners	: {
					click		:  searchMovie
				}
			},{
				xtype		: 'tbseparator'
			},{
				xtype		: 'button',
				id			: 'butprev',
				iconCls		: Ext.baseCSSPrefix + 'tbar-page-prev',
				handler		: goPrevious
			},{
				xtype		: 'button',
				id			: 'butnext',
				iconCls		: Ext.baseCSSPrefix + 'tbar-page-next',
				handler		: goNext
			},{
				xtype		: 'tbseparator'
			},{
				xtype		: 'button',
				id			: 'butsaveprev',
				iconCls		: 'icon-save-and-prev',
				disabled	: true,
				handler		: function(){
					that.afterSave = goPrevious;
					saveDetails();
				}
			},{
				xtype		: 'button',
				iconCls		: 'icon-save',
				id			: 'buttsave',
				disabled	: true,
				handler		: saveDetails
			},{
				xtype		: 'button',
				id			: 'butsavenext',
				iconCls		: 'icon-save-and-next',
				disabled	: true,
				handler		: function(){
					that.afterSave = goNext;
					saveDetails();
				}
			},{
				xtype: 'tbseparator'
			},{
				xtype	: 'tbtext',
				text	: 'autoLoad'
			},'->',{
				xtype		: 'checkbox',
				id			: autoloadchoosegrid,
				labelWidth	: 100,
				width		: 20,
				checked		: true,
				stateful	: false,
				listeners	:{
					change		: function (field,newValue,oldValue,eOpts){
						Ext.getCmp(autoloadfirstingrid).setDisabled(!newValue);
						Ext.getCmp(autoloadfirstingrid).setValue(newValue);
					}
				}
			},{
				xtype		: 'checkbox',
				id			: autoloadfirstingrid,
				labelWidth	: 0,
				width		: 20,
				label		: '',
				stateful	: false,
				checked		: true
			}],
			items	: [{
				xtype			: 'grid',
				region			: 'west',
				width			: 400,
				split			: true,
				id				: gridchoosemoviesid,
				store			: chooseMovieStore,
				stateful		: false,
				selType			: 'checkboxmodel',
				autoFit			: true,
				listeners		: {
					itemclick : function( view,record,item,index,e,eOpts){
						setEditor(record)
					}
				},
				columns		: [
					{header: "poster"	, dataIndex: 'poster'	, sortable: true, width :  95, flex : 0,
						renderer : function(val,style,record){
							style.style.height=(val)?90:45;
							return (val)?'<img src="p/QDMediaDBProxy.proxyImg/?c=90x90&u='+val+'" />':'';
						}
					},
					{header: "title"	              , dataIndex: 'title'	, sortable: true, width : 200,flex : 0,
						renderer : function(val,style,record){
							return that.tplList.applyTemplate(record.data);
						}
					},
					{header: "year"		, dataIndex: 'year'		, sortable: true, width :  50, flex : 0}
				]
			},{
				xtype		: 'tabpanel',
				id			: tabpanelid,
				region		: 'center',
				frame		: true,
				listeners	: {
					tabchange	: function(tabpanel,newCard){
						if(newCard.xtype=='QTPreview' && that.filename){
							console.log(that.filename);
							Ext.getCmp(previewid).loadPreview(that.filename)
						}
					}
				},
				items	:[{
					title		: 'detail',
					frame		: true,
					id			: detailpanelid
				},{
					title		: 'Posters',
					xtype		: 'ImageSelector',
					id			: gridposterid,
					store		: choosePosterStore,
					imgPrefix	: 'p/QDMediaDBProxy.proxyImg/?c=100x150&u=',
					listeners	:{
						selectimg : function(node){
							that.currentRecord.poster=node[0].get('url');
							enableSave();
							Ext.getCmp(tabpanelid).setActiveTab(2);
							updatePreview();
						}
					}
				},{
					title		: 'Fanarts/Backdrops',
					xtype		: 'ImageSelector',
					id			: gridbackdropid,
					store		: chooseBackdropStore,
					imgPrefix	: 'p/QDMediaDBProxy.proxyImg/?c=150x100&u=',
					listeners	:{
						selectimg : function(node){
							that.currentRecord.backdrop=node[0].get('url');
							enableSave();
							Ext.getCmp(tabpanelid).setActiveTab(0);
							updatePreview();
						}
					}
				},{
					region			: 'center',
					xtype			: 'QTPreview',
					title			: 'Preview',
					id				: previewid
				}]
			}],
			buttons :[{
				text	: 'close',
				handler	: function(){
					that.close();
					that.fireEvent('cancel');
				}
			}],
			listeners : {
				show	: function(){
					setCurrentRecord(that.referenceRecord);
				}
			}
		});
		this.callParent(arguments);
	}
});