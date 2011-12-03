Ext.qd.wow.armory.characterItemGrid = Ext.extend(Ext.grid.GridPanel, {
  initComponent : function() {
    this.cellActions = new Ext.ux.grid.CellActions({
      listeners:{
        action: function(grid, record, action, value) {
        }
        ,beforeaction:function() {
        }
      }
      ,callbacks:{
        'icon-better' : function(grid, record, action, value) {
           Ext.qd.wow.armory.OpenBetterItem(grid.armory,grid.charName,record.data.id);
        },  
        'icon-view' : function(grid, record, action, value) {
          eval ("var t={"+record.data.jsonEquip+"}")
          Ext.qd.wow.armory.OpenItem(record.data.id,t.displayid);
        },
        'icon-addbasket' : function(grid, record, action, value) {
          Ext.qd.wow.armory.addToBasket(record.data,record);
        }  
      }
      ,align:'left'
    });
    this.expander = new Ext.qd.XslRowExpander({
      tpl : Ext.qd.wow.armory.itemTemplate
    });
   
    Ext.apply(this, {
      title : 'Items',
      store : new Ext.data.GroupingStore({
        reader   : new Ext.data.XmlReader({
           record: 'item[@onList=1]'
         },[
           {name: 'quality'      , mapping: 'datas/overallQualityId'},
           {name: 'id'           , mapping: '@id'},
           {name: 'name'         , mapping: 'datas/name'},
           {name: 'level'        , mapping: '@itemLevel'},
           {name: 'minlevel'     , mapping: 'datas/requiredLevel'},           
           {name: 'jsonEquip'    , mapping: 'subsite/jsonEquip'},
           {name: 'slot'         , mapping: '@slot'},
           {name: 'itemsubclass' , mapping: 'datas/equipData/subclassName'},
           {name: 'icon'         , mapping: '@icon'},
           {name: 'htmlTooltip'  , mapping: 'subsite/display_html_frFR'},
           {name: 'gem0'         , mapping: 'improvments/itemTooltip[@improvmentRank=0]/icon'},
           {name: 'gem1'         , mapping: 'improvments/itemTooltip[@improvmentRank=1]/icon'},
           {name: 'gem2'         , mapping: 'improvments/itemTooltip[@improvmentRank=2]/icon'}
        ])              
      }),
      plugins:[
        this.cellActions,
        this.expander
      ],
      columns: [
        this.expander,        
        {header: "Item"       , width:  38, dataIndex: 'icon'         , menuDisabled : true,renderer : Ext.qd.wow.armory.getIconImg},
        {header: "Empl."      , width:  35, dataIndex: 'slot'         , menuDisabled : true},
        {header: "Nom"        , width: 240, dataIndex: 'name'         , sortable: true,renderer : Ext.qd.wow.armory.colorizeIconName,
          cellActions:[{
            iconCls:'icon-better',
            qtip:'Trouver mieux'
          },{
            iconCls:'icon-view',
            qtip:'voir'
          },{
            iconCls:'icon-addbasket',
            qtip:'Ajouter au panier'
          }]},
        {header: "Type"       , width:  50, dataIndex: 'itemsubclass' , sortable: true},
        {header: "Niv."       , width:  35, dataIndex: 'level'        , sortable: true},
        {header: "G1"         , width:  25, dataIndex: 'gem0'         , menuDisabled : true,renderer : Ext.qd.wow.armory.getIconImg},
        {header: "G2"         , width:  25, dataIndex: 'gem1'         , menuDisabled : true,renderer : Ext.qd.wow.armory.getIconImg},
        {header: "G3"         , width:  25, dataIndex: 'gem2'         , menuDisabled : true,renderer : Ext.qd.wow.armory.getIconImg}
      ]});
    
    Ext.qd.wow.armory.characterItemGrid.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowCharacterItemGrid',Ext.qd.wow.armory.characterItemGrid)