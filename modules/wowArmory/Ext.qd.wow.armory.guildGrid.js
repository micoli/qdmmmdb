Ext.qd.wow.armory.guildGrid = Ext.extend(Ext.grid.GridPanel, {
  initComponent : function() {
    this.cellActions = new Ext.ux.grid.CellActions({
      listeners:{
        action: function(grid, record, action, value) {
        }
        ,beforeaction:function() {
        }
      }
      ,callbacks:{
        'icon-view' : function(grid, record, action, value) {
          Ext.qd.wow.armory.OpenCharacter(Ext.qd.wow.armory,record.data.name);
        }  
      }
      ,align:'left'
    });
		Ext.apply(this, {		
        store: new Ext.data.GroupingStore({
            url: 'proxy.php?exw_action=QDWowProxy.infoGuilde&z='+Ext.qd.wow.armory.z+'&r='+Ext.qd.wow.armory.r+'&g='+Ext.qd.wow.armory.g,
            autoLoad : true,
            reader   : new Ext.data.XmlReader({
               record: 'character'
            }, [
               {name: 'name'   , mapping: '@name'},
               {name: 'classId', mapping: '@classId'},
               {name: 'level'  , mapping: '@level'},
               {name: 'race'   , mapping: '@race'},
               {name: 'rank'   , mapping: '@rank'}
               
           ]),
            sortInfo:{field: 'name', direction: "ASC"},
            groupField:'classId'
        }),
        view: new Ext.grid.GroupingView({
          forceFit:true,
          startCollapsed  : true,
          groupTextTpl: '{text}'
        }),
        plugins : [this.cellActions],
        columns: [
            {header: "Nom"   , width: 120, dataIndex: 'name'   , sortable: true,cellActions:[{
              iconCls:'icon-view',
              qtip:'voir'
            }]},
            {header: "Classe", width: 100, dataIndex: 'classId', sortable: true,renderer : Ext.qd.wow.armory.getClassFromClassId},
            {header: "Level" , width:  50, dataIndex: 'level'  , sortable: true},
            {header: "Race"  , width: 100, dataIndex: 'race'   , sortable: true},
            {header: "Grade" , width:  50, dataIndex: 'rank'   , sortable: true}
        ]        
		});
    this.on ({
           rowdblclick  : function ( grid, rowIndex, e ){
             var record = grid.getStore().getAt(rowIndex);
             Ext.qd.wow.armory.OpenCharacter(Ext.qd.wow.armory,record.data.name);
           } 
        });
    Ext.qd.wow.armory.guildGrid.superclass.initComponent.apply(this, arguments);
  }
});		
