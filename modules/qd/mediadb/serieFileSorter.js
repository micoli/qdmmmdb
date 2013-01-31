Ext.define('qd.mediadb.serieFileSorter', {
	extend			: 'Ext.Panel',
	alias			: 'widget.qd.mediadb.serieFileSorter',
	initComponent	: function() {
		var that = this;
		that.rootdriveid		= Ext.id();
		that.filetosortid		= Ext.id();
		that.fieldforlderid		= Ext.id();

		Ext.define('rootDrive', {
			extend	: 'Ext.data.Model',
			fields	: [
				'name'		,
				'path'		,
			]
		});

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
			extend	: 'Ext.data.Model',
			fields	: [
				'found'				,
				'filename'			,
				'saison'			,
				'episode'			,
				'rgx'				,
				'fullfilename'		,
				'clean_root_file'	,
				'root_file'
			]
		});

		that.fileToSortStore = Ext.create('Ext.data.Store',{
			pruneModifiedRecords: true,
			model				: 'fileToSort',
			autoLoad			: false,
			proxy				: {
				type				: 'ajaxEx',
				url					: 'p/QDSeriesProxy.getFileSorterList/',
				reader				: {
					type				: 'json',
					root				: 'results'
				}
			},
			sorters		: [{
				property	: 'fullfilename',
				direction	: 'ASC'
			}]
		});

		that.saisonStore = Ext.create('Ext.data.ArrayStore', {
			fields	: ['saison'],
			data	: [['1'],['2'],['3'],['4'],['5'],['6'],['7'],['8'],['9'],['10'],['11'],['12'],['13'],['14'],['15']]
		});

		that.languageStore = Ext.create('Ext.data.ArrayStore', {
			fields	: ['lang'],
			data	: [['FR'],['VO']]
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
						that.fileToSortStore.load({
							params	: {
								name	: record.get('name')
							}
						})
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
					id			: that.filetosortid,
					layout		: 'fit',
					border		: false,
					store		: that.fileToSortStore,
					loadMask	: true,
					autoFit		: true,
					selModel	: Ext.create('Ext.selection.CheckboxModel',{
						listeners	: {
							selectionchange	: function ( selModel, selected, eOpts ){
								//console.log(selModel, selected, eOpts);
								var pattern = '';
								if(selected.length==0){
									return;
								}
								if(selected && selected.length>1){
									var clean_root_file = selected[0].get('clean_root_file');
									if(clean_root_file!=''){
										var allIdentical = true;
										for (j=1;j<selected.length;j++){
											if(clean_root_file != selected[j].get('clean_root_file')){
												allIdentical = false;
											}
										}
									}
									if(allIdentical){
										pattern = clean_root_file;
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
									}
								}else{
									pattern = selected[0].get('clean_root_file')==''?selected[0].get('filename'):selected[0].get('clean_root_file');
								}
								Ext.getCmp(that.fieldforlderid).setValue(pattern);
								console.log(pattern);
							}
						}
					}),
					listeners	: {
						'itemclick'	: function( grid, record, item, index, e, eOpts) {
							var record = grid.getStore().getAt(index);
							//Ext.getCmp(that.filetosortid).getSelectionModel().getSelection()
						}
					},
					/*
					'found'			,
					'filename'		,
					'saison'		,
					'episode'		,
					'rgx'			,
					'fullfilename'	,
					*/
					columns		: [{
						header: "fullfilename"	, width:  80,flex : 1, dataIndex: 'fullfilename'	, sortable: true,renderer : function(v,meta,record){
							if (record.get('found')){
								meta.style='color:green;'
							}else{
								meta.style='color:red;'
							}
							return v;
						}
					},{
						header: "S"				, width:  30,flex : 0, dataIndex: 'saison'			, sortable: true
					},{
						header: "E"				, width:  30,flex : 0, dataIndex: 'episode'			, sortable: true
					},{
						header: "Pattern"		, width:  80,flex : 1, dataIndex: 'clean_root_file'	, sortable: true
					},{
						header: "renamed"		, width:  80,flex : 1, dataIndex: 'renamed'			, sortable: true
					}]
				},{
					region	: 'south',
					height	: 150,
					xtype	: 'form',
					items	: [{
						xtype		: 'fieldset',
						title		: 'update datas',
						defaultType	: 'textfield',
						layout		: 'anchor',
						frame		: true,
						defaults	: {
							anchor: '100%'
						},
						items		: [{
							xtype			: 'fieldcontainer',
							fieldLabel		: 'dispatch',
							layout			: 'hbox',
							combineErrors	: true,
							defaultType		: 'textfield',
							defaults		: {
								hideLabel		: 'true'
							},
							items		: [{
								xtype		: 'textfield',
								fieldLabel	: 'Folder Name',
								name		: 'folder',
								id			: that.fieldforlderid,
								labelWidth	: 50,
								width		: 300,
								allowBlank	: false
							},{
								xtype			: 'combobox',
								name			: 'lang',
								fieldLabel		: 'lang',
								labelWidth		: 50,
								width			: 50,
								store			: that.languageStore,
								valueField		: 'lang',
								displayField	: 'lang',
								typeAhead		: true,
								queryMode		: 'local',
								allowBlank		: false,
								forceSelection	: true
							},{
								xtype			: 'combobox',
								name			: 'saison',
								fieldLabel		: 'saison',
								labelWidth		: 50,
								width			: 50,
								store			: that.saisonStore,
								valueField		: 'saison',
								displayField	: 'saison',
								typeAhead		: true,
								queryMode		: 'local',
								allowBlank		: false,
								forceSelection	: true
							}]
						}]
					}]
				}]
			}]
		});
		this.callParent(this);
	}
});