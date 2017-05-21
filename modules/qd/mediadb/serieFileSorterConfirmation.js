Ext.define('qd.mediadb.serieFileSorterConfirmation', {
	extend			: 'Ext.Window',
	alias			: 'widget.qd.mediadb.serieFileSorterConfirmation',
	initComponent	: function() {
		var that = this;
		that.gridoriginaltorenamedid = Ext.id();
		Ext.define('originaltorenamed', {
			extend	: 'Ext.data.Model',
			fields	: [
				'fullfilename'	,
				'folder'		,
				'renamed'		,
				'extension'		,
			]
		});

		that.originaltorenamedStore = Ext.create('Ext.data.Store',{
			model	: 'originaltorenamed',
		});

		that.originaltorenamedStore.loadData(that.records);

		Ext.apply(this,{
			width	: 900,
			height	: 400,
			modal	: true,
			title	: 'Confirmation',
			layout	: 'border',
			items	: [{
				xtype			: 'grid',
				region			: 'center',
				id				: that.gridoriginaltorenamedid,
				store			: that.originaltorenamedStore,
				columns			: [{
					header: "Original"	,width: 200,	dataIndex: 'fullfilename'	, flex	:1	,sortable: true
				},{
					header: "Renamed"	,width: 200,	dataIndex: 'renamed'		, flex	:1	,sortable: true, renderer:function(v,meta,record){
						var str = '';
						str = str + '<span class="sorter-folder"	>'+record.data.folder+'</span>';
						str = str + '<span class="sorter-separator"	>'+'/'+'</span>';
						str = str + '<span class="sorter-renamed"	>'+record.data.renamed+'</span>';
						return str;
					}
				}]
			}],
			buttons :[{
				text	: 'Ok',
				handler	: function(){
					var toRename =[];
					that.originaltorenamedStore.each(function(v,k){
						toRename.push({
							'fullfilename'	: Ext.ux.base64.encode(v.data.fullfilename	),
							'folder'		: Ext.ux.base64.encode(v.data.folder		),
							'renamed'		: Ext.ux.base64.encode(v.data.renamed		),
							'extension'		: Ext.ux.base64.encode(v.data.extension		)
						});
					});
					if(toRename.length>0){
						var w = Ext.MessageBox.wait('mise à jour');
						Ext.AjaxEx.request({
							url		: 'api/Series/serieBulkRename',
							method	: 'POST',
							params	: {
								d		: Ext.JSON.encode(toRename)
							},
							success : function(res){
								res = Ext.JSON.decode(res.responseText);
								if(!res.ok){
									var str='';
									for(k in res.details){
										k=parseInt(k);
										if(!res.details[k].ok){
											str = str+res.details[k].error+'<br/>';
										}
									}
									that.onError.call(that.sorter,str);
								}
								w.hide();
								that.hide();
								that.sorter.reloadFiles();
							}
						});
					}else{
						Ext.MessageBox.alert('Error','List empty');
						that.hide();
					}
				}
			},{
				text : 'Cancel',
				handler : function(){
					that.close();
				}
			}]
		});
		this.callParent(arguments);
	}
});