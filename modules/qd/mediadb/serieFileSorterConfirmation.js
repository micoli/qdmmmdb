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
				'renamed'
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
						toRename.push(v.data);
					});

					if(toRename.length>0){
						var w = Ext.MessageBox.wait('mise à jour');
						Ext.AjaxEx.request({
							url		: 'p/QDSeriesProxy.serieBulkRename/',
							params	: {
								d		: Ext.JSON.encode(toRename)
							},
							success : function(res){
								w.hide();
								that.hide();
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