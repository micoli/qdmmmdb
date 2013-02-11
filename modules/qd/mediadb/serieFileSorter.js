Ext.define('qd.mediadb.serieFileSorter', {
	extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.serieFileSorter',
	initComponent	: function() {
		var that = this;
		that.rootdriveid		= Ext.id();
		that.filetosortid		= Ext.id();
		that.filetorenameid		= Ext.id();
		that.fieldlblforlderid	= Ext.id();
		that.fieldforlderid		= Ext.id();

		that.fieldlangid		= Ext.id();
		that.fieldsaisonid		= Ext.id();

		that.fieldchklangid		= Ext.id();
		that.fieldchksaisonid	= Ext.id();
		that.fieldlbllangid		= Ext.id();
		that.fieldlblsaisonid	= Ext.id();
		that.currentDrive		= null;

		Ext.define('rootDrive', {
			extend	: 'Ext.data.Model',
			fields	: [
				'name'		,
				'path'		,
			]
		});

		that.reloadFiles = function(){
			if(that.currentDrive){
				that.fileToSortStore.load({
					params	: {
						name	: that.currentDrive
					}
				});
			}
		}
		that.rootDriveStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'rootDrive',
			autoLoad			: true,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDSeriesProxy.getFolderSeriesList/',
				reader				: {
					type				: 'json',
					root				: 'results'
				}
			},
			sorters		: [{
				property	: 'name',
				direction	: 'ASC'
			}]
		});

		Ext.define('fileToSort', {
			extend		: 'Ext.data.Model',
			idProperty	: 'fullfilename',
			fields		: [
				'selected'			,
				'found'				,
				'filename'			,
				'saison'			,
				'episode'			,
				'rgx'				,
				'fullfilename'		,
				'extension'			,
				'inFolder'			,
				'folder'			,
				'root'				,
				'subPath'			,
				'renamed'			,
				'clean_root_file'	,
				'root_file'
			]
		});

		that.localFolderStore = Ext.create('Ext.data.Store', {
			fields	: ['folder'],
			data	: []
		});

		that.fileToSortStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'fileToSort',
			autoLoad			: false,
			groupField			: 'folder',
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDSeriesProxy.getFileSorterList/',
				reader				: {
					type				: 'json',
					root				: 'results'
				}
			},
			sorters				: [{
				property			: 'fullfilename',
				direction			: 'ASC'
			}],
			listeners	: {
				load		: function( store, records, successful, eOpts ){
					//console.log(store , records, successful, eOpts);
					//console.log(store.proxy.reader.jsonData.folders);
					that.localFolderStore.removeAll();
					Ext.each(store.proxy.reader.jsonData.folders,function(v,k){
						that.localFolderStore.add({folder:v});
					});
					that.selectionChangedHolder();
					//console.log(that.localFolderStore);
				}
			}
		});

		that.saisonStore = Ext.create('Ext.data.ArrayStore', {
			fields	: ['saison'],
			data	: [[1],[2],[3],[4],[5],[6],[7],[8],[9],[10],[11],[12],[13],[14],[15]]
		});

		that.languageStore = Ext.create('Ext.data.ArrayStore', {
			fields	: ['lang'],
			data	: [['FR'],['VO']]
		});

		that.getSelectedItems = function(){
			var selected=[];
			that.fileToSortStore.each(function(v,k){
				if(v.get('selected')!=''){
					selected.push(v);
				}
			});
			return selected;
		}

		that.selectionChangedHolder = function (event,idx,value,checkbox){
			// /*selModel, selected, eOpts*/ ){
			if(idx){
				//that.fileToSortStore.getAt(idx).commit();
			}
			var selected=that.getSelectedItems();

			var pattern = '';
			var allInFolder = false;

			if(selected && selected.length>1){
				//console.log(+new Date() -time,"153");
				var clean_root_file = selected[0].get('clean_root_file');
				var allFolder		= selected[0].get('folder');
				allInFolder			= selected[0].get('inFolder');
				if(clean_root_file!=''){
					var allIdentical = true;
					for (j=1;j<selected.length;j++){
						if(clean_root_file != selected[j].get('clean_root_file')){
							allIdentical = false;
						}
						if(allInFolder && !selected[j].get('inFolder')){
							allIinFolder = false;
						}
					}
				}
				if(allInFolder){
					var allIdenticalInFolder = true;
					for (j=1;j<selected.length;j++){
						if(allFolder != selected[j].get('folder')){
							allIdenticalInFolder = false;
						}
						if(!selected[j].get('inFolder')){
							allIdenticalInFolder = false;
						}
					}
				}
				if(allIdentical){
					pattern = clean_root_file;
				}else if(allIdenticalInFolder){
					pattern = allFolder;
				}else{
					var length = selected[0].get('filename').length;
					for (var i=length;i>0;i--){
						var lastSub = selected[0].get('filename').substr(0,i);
						var identical = true;
						for (j=1;j<selected.length;j++){
							if(lastSub!=selected[j].get('filename').substr(0,i)){
								identical=false;
							}
						}
						if(identical){
							pattern = lastSub;
							break;
						}
					}
					//console.log(+new Date() -time,"181");
				}
			}else if(selected && selected.length==1){
				allInFolder		= selected[0].get('inFolder');
				pattern			= selected[0].get('clean_root_file')==''?selected[0].get('filename'):selected[0].get('clean_root_file');
			}else{
				pattern			= '';
			}

			if(pattern && selected && selected.length>=1){
				var saison=selected[0].get('saison');
				//console.log(+new Date() -time,"191");
				for (j=1;j<selected.length;j++){
					if(saison != selected[j].get('saison')){
						saison=-1;
					}
				}
				//console.log(+new Date() -time,"197");
			}

			if(!allInFolder && selected.length>0){
				Ext.getCmp(that.fieldforlderid).setValue(pattern);
				Ext.getCmp(that.fieldforlderid).setDisabled(false);
				Ext.getCmp(that.fieldlblforlderid).setDisabled(false);
			}else{
				Ext.getCmp(that.fieldforlderid).setValue('----');
				Ext.getCmp(that.fieldforlderid).setDisabled(true);
				Ext.getCmp(that.fieldlblforlderid).setDisabled(true);
			}

			if(saison!=-1 && saison !=''){
				Ext.getCmp(that.fieldsaisonid ).setValue(saison);
			}

			//console.log(+new Date() -time,"202");
			that.fileToSortStore.each(function(v,k){
				v.set('renamed','');
			});
			//console.log(+new Date() -time,"207");

			that.updateSelectedItems();
		}

		that.updateControls = function(){
			//console.log(Ext.getCmp(that.fieldforlderid).getValue());
			//console.log(Ext.getCmp(that.fieldlangid).getValue());
			//console.log(Ext.getCmp(that.fieldsaisonid).getValue());

			var disabled = !Ext.getCmp(that.fieldchklangid).getValue();
			Ext.getCmp(that.fieldlangid).setDisabled(disabled);
			Ext.getCmp(that.fieldlbllangid)[disabled?'addCls':'removeCls']('lbl-disabled');

			var disabled = !Ext.getCmp(that.fieldchksaisonid).getValue();
			Ext.getCmp(that.fieldsaisonid).setDisabled(disabled);
			Ext.getCmp(that.fieldlblsaisonid)[disabled?'addCls':'removeCls']('lbl-disabled');
		}

		that.updateSelectedItems = function (){
			that.updateControls();
			var selected=that.getSelectedItems();
			var foldername	= Ext.getCmp(that.fieldforlderid	).getValue();
			var withsaison	= Ext.getCmp(that.fieldchksaisonid	).getValue();
			var saison		= Ext.getCmp(that.fieldsaisonid		).getValue();
			var withlang	= Ext.getCmp(that.fieldchklangid	).getValue();
			var lang		= Ext.getCmp(that.fieldlangid		).getValue();
			for (j=0;j<selected.length;j++){
				var str = '';
				if(!selected[j].get('inFolder')){
					str = str + foldername +'/';
				}
				var withSeparator = false;
				if(withsaison && saison){
					str = str + 'S' + saison;
					withSeparator = true;
				}
				if(withlang && lang){
					str = str + ((withsaison && saison)?' ':'') + lang;
					withSeparator = true;
				}
				if(withSeparator){
					str = str + '/';
				}
				str = str + selected[j].get('filename');
				var rec = that.fileToSortStore.getById(selected[j].getId());
				rec.beginEdit();
				rec.set('renamed',str);
				rec.endEdit();
			}
		}

		that.setSelectedByFolderGroup = function (groupName,value){
			that.fileToSortStore.each(function(v,k){
				if(v.get('folder')==groupName){
					v.beginEdit();
					v.set('selected',value);
					v.endEdit();
				}
			});
		}

		that.fileToSortFeature = Ext.create('Ext.ux.SelectGrouping',{
			groupHeaderTpl: '{name} ({rows.length} item{[values.rows.length > 1 ? "s" : ""]})',
			listeners : {
				groupselectall : function(feature,groupname){
					that.setSelectedByFolderGroup(groupname,true);
					that.selectionChangedHolder();
				},
				groupselectnone : function(feature,groupname){
					that.setSelectedByFolderGroup(groupname,false);
					that.selectionChangedHolder();
				}
			}
		});

		Ext.apply(this,{
			layout		: 'border',
			border		: false,
			stateful	: false,
			items		: [{
				xtype		: 'grid',
				region		: 'west',
				id			: that.rootdriveid,
				layout		: 'fit',
				width		: 240,
				border		: false,
				split		: true,
				store		: that.rootDriveStore,
				loadMask	: true,
				autoFit		: true,
				listeners	: {
					'itemclick': function( grid, record, item, index, e, eOpts) {
						var record = grid.getStore().getAt(index);
						that.currentDrive = record.get('name');
						that.fileToSortStore.load({
							params	: {
								name	: that.currentDrive
							}
						});
					}
				},
				columns		: [{
					header: "Name"		, width:  40,flex : 0, dataIndex: 'name'	, sortable: true
				},{
					header: "Path"		, width: 200,flex : 1, dataIndex: 'path'	, sortable: true
				}]
			},{
				region	: 'center',
				layout	: 'border',
				items	:[{
					xtype		: 'grid',
					region		: 'center',
					layout		: 'fit',
					id			: that.filetosortid,
					store		: that.fileToSortStore,
					features	: [that.fileToSortFeature],
					border		: false,
					loadMask	: true,
					autoFit		: true,
					lines		: false,
					useArrows	: true,
					tbar		: [{
						xtype		: 'button',
						text		: 'Select All',
						handler		: function(){
							that.fileToSortStore.each(function(v,k){
								v.set('selected',true);
							});
						}
					},{
						xtype		: 'button',
						text		: 'unselect All',
						handler		: function(){
							that.fileToSortStore.each(function(v,k){
								v.set('selected',false);
							});
						}
					},{
						xtype		: 'button',
						text		: 'invert Select',
						handler		: function(){
							that.fileToSortStore.each(function(v,k){
								v.set('selected',!v.get('selected'));
							});
						}
					},{
						xtype		: 'tbseparator'
					},/*{
						xtype			: 'textfield',
						name			: 'folder',
						id				: that.fieldforlderid,
						labelWidth		: 90,
						width			: 300,
						allowBlank		: false,
						enableKeyEvents: true,
						listeners		: {
							keypress		: that.updateSelected
						}
					}*/{
						xtype			: 'label',
						text			: 'Folder Name',
						id				: that.fieldlblforlderid,
					},{
						xtype			: 'combobox',
						id				: that.fieldforlderid,
						queryMode		: 'local',
						valueField		: 'folder',
						displayField	: 'folder',
						width			: 200,
						typeAhead		: true,
						minChars		: 2,
						store			: that.localFolderStore,
						enableKeyEvents	: true,
						listeners		: {
							keypress		: that.updateSelectedItems
						},
						listeners: {
							buffer: 50,
							change: function() {
								var store = this.store;
								store.clearFilter();
								store.filter({
									property: 'folder',
									anyMatch: true,
									value	: this.getValue()
								});
								that.updateSelectedItems();
							}
						}
					},{
						xtype			: 'label',
						text			: 'Saison',
						id				: that.fieldlblsaisonid,
					},{
						xtype			: 'combobox',
						name			: 'saison',
						labelWidth		: 40,
						width			: 50,
						id				: that.fieldsaisonid,
						store			: that.saisonStore,
						valueField		: 'saison',
						displayField	: 'saison',
						typeAhead		: true,
						queryMode		: 'local',
						allowBlank		: true,
						forceSelection	: true,
						listeners		: {
							select			: that.updateSelectedItems
						}
					},{
						xtype			: 'checkbox',
						name			: 'withsaison',
						checked			: true,
						width			: 20,
						id				: that.fieldchksaisonid,
						listeners		: {
							change			: that.updateSelectedItems
						}
					},{
						xtype			: 'label',
						text			: 'Lang',
						id				: that.fieldlbllangid,
					},{
						xtype			: 'combobox',
						name			: 'lang',
						labelWidth		: 40,
						width			: 50,
						id				: that.fieldlangid,
						store			: that.languageStore,
						valueField		: 'lang',
						displayField	: 'lang',
						typeAhead		: true,
						queryMode		: 'local',
						allowBlank		: true,
						forceSelection	: true,
						listeners		: {
							select			: that.updateSelectedItems
						}
					},{
						xtype			: 'checkbox',
						name			: 'withlang',
						checked			: true,
						width			: 20,
						id				: that.fieldchklangid,
						listeners		: {
							change			: that.updateSelectedItems
						}
					},'->',{
						xtype			: 'button',
						text			: 'rename',
						handler			: function(){
							var selected=[];
							that.fileToSortStore.each(function(v,k){
								if(v.get('selected')!=''){
									selected.push(v);
								}
							});
							if(selected && selected.length>0){
								Ext.create('qd.mediadb.serieFileSorterConfirmation',{
									records	: selected,
									sorter	: that,
									onError	: function(error){
										Ext.create('widget.uxNotification', {
											title			: 'Renaming error',
											position		: 'br',
											manager			: 'demo1',
											iconCls			: 'ux-notification-icon-information',
											autoCloseDelay	: 9000,
											spacing			: 20,
											html			: error
										}).show();
									}
								}).show();
							}
						}
					}],
					listeners	: {
						itemclick	: function( grid, record, item, index, e, eOpts) {
							var record = grid.getStore().getAt(index);
							//Ext.getCmp(that.filetosortid).getSelectionModel().getSelection()
						},
					},
					columns		: [{
							header: "&nbsp"			, width:  30,flex : 0, dataIndex: 'selected'		, sortable: true,xtype: 'checkcolumn',listeners : {
									checkchange : function(event,idx,value,checkbox){
								//console.log(arguments);
								that.selectionChangedHolder.call(that,event,idx,value,checkbox);
							}
						}
					},{
						header: "Full Filename"	, width:  80,flex : 1, dataIndex: 'fullfilename'	, sortable: true,renderer : function(v,meta,record){
							if (record.get('found')){
								meta.style='color:green;'
							}else{
								meta.style='color:red;'
							}
							return v;
						}
					},{
						header: "&nbsp;"		, width:  30,flex : 0, dataIndex: 'inFolder'		, sortable: true,renderer :function(v,meta,record){
							var icon = v?'icon-folder-open':'icon-folder-close';
							return '<span class="genericIcon16 '+icon+'">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						}
					},{
						header: "S"				, width:  30,flex : 0, dataIndex: 'saison'			, sortable: true
					},{
						header: "E"				, width:  30,flex : 0, dataIndex: 'episode'			, sortable: true
					},{
						header: "Pattern"		, width: 130,flex : 0, dataIndex: 'clean_root_file'	, sortable: true
					},{
						header: "Renamed"		, width: 240,flex : 1, dataIndex: 'renamed'			, sortable: true, renderer:function(v,meta,record){
							var str = '';
							str = str + '<span class="sorter-folder"	>'+record.data.folder+'</span>';
							str = str + '<span class="sorter-separator"	>'+'/'+'</span>';
							str = str + '<span class="sorter-renamed"	>'+record.data.renamed+'</span>';
							return str;
						}
					}]
				},]
			}]
		});
		this.callParent(this);
		that.fileToSortStore.load({
			params	: {
				name	: 'F'
			}
		});
	}
});



//<tpl if="typeof rows !== 'undefined'"><tr id="{groupHeaderId}" class="x-grid-group-hd {hdCollapsedCls} {collapsibleClass}"><td class="x-grid-cell" colspan="7" {[this.indentByDepth(values)]}><div class="x-grid-cell-inner"><div class="x-grid-group-title">{collapsed}{[this.renderGroupHeaderTpl(values, parent)]}</div></div></td></tr><tr id="{groupBodyId}" class="x-grid-group-body {collapsedCls}"><td colspan="7">{[this.recurse(values)]}</td></tr></tpl>
//<tpl if="typeof rows !== 'undefined'"><tr                      class="x-grid-group-hd {hdCollapsedCls}"                   ><td class="x-grid-cell" colspan="7" {[this.indentByDepth(values)]}><div class="x-grid-cell-inner"><div class="x-grid-group-title">{collapsed}Type: {name} ({rows.length} item{[values.rows.length > 1 ? "s" : ""]})</div></div></td></tr><tr id="{viewId}-gp-{name}" class="x-grid-group-body  {collapsedCls}"><td colspan="7">{[this.recurse(values)]}</td></tr></tpl>