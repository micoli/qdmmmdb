Ext.qd.wow.armory.betterItemGrid = Ext.extend(Ext.Panel, {
  initComponent : function() {
    this.cellActions = new Ext.ux.grid.CellActions({
      listeners:{
        action: function(grid, record, action, value) {
        }
        ,beforeaction:function() {
        }
      }
      ,callbacks:{
        'icon-addbasket' : function(grid, record, action, value) {
          Ext.qd.wow.armory.addToBasket(record.data,record);
        }  
      }
      ,align:'left'
    });
    this.expander = new Ext.qd.XslRowExpander({
      tpl : Ext.qd.wow.armory.itemTemplate
    });
    var id = Ext.id();
    Ext.apply(this, {
        xtype : 'panel',
        items : [{
          xtype     : 'panel',
          html      : '<div id="subitemdetail'+id+'" />',
          height    : 100,
          id        : 'itemdetail'+id,
          autoScroll : 'auto'
        },{
        xtype         : 'editorgrid',
        height        : 300,
        id            : 'itemsBetter'+this.itemID,
        loadMask      : {msg: 'Chargement...'},
        store         : new Ext.data.GroupingStore({
          autoLoad      : true,
          listeners : {
            add : function (store,record){
              /*for (var i=0;i<store.data.items.length;i++){
                store.data.items[i]['source']=Ext.qd.wow.armory.getItemSource('','',store.data.items[i]);
              }*/
            },
            load: function(store,records){
               var detailPanel = Ext.get('subitemdetail'+id).dom;
               detailPanel.innerHTML = Ext.qd.wow.armory.itemTemplate.transform("dom",store.reader.xmlData.getElementsByTagName('searchItemDatas')[0]);
            },
            beforeload : function (store, options){
              options.timeout=130000;
            }
          }, 
          url           : 'proxy.php?exw_action=QDWowProxy.searchItem&z='+this.armory.z+'&r='+this.armory.r+'&c='+this.charName+'&i='+this.itemID,
          reader        : new Ext.data.XmlReader({
             record       : 'items/item'
           }, [
             {name: 'name'        , mapping: '@name'},
             {name: 'id'          , mapping: '@id'},
             {name: 'icon'        , mapping: '@icon'},
             {name: 'quality'     , mapping: 'overallQualityId'},
             {name: 'dropRate'    , mapping: 'creature@dropRate'},           
             {name: 'htmlTooltip' , mapping: 'subsite/display_html_frFR'},
             {name: 'area'        , mapping: 'creature@area'},           
             {name: 'creatureName', mapping: 'creature@name'},           
             {name: 'heroic'      , mapping: 'creature@heroic'},         
             {name: 'title'       , mapping: 'creature@title'},
             {name: 'source'      , mapping: 'creature@name'},
             {name: 'itemSource'  , mapping: 'datas/itemSource@value'}
         ]),
          sortInfo:{field: 'name', direction: "ASC"},
          groupField:'source'
      }),
      plugins:[this.expander,this.cellActions],
      view: new Ext.grid.GroupingView({
        forceFit:true,
        startCollapsed  : true,
        groupTextTpl: '{text}'
      }),
      columns: [
          this.expander,
          {header: "Nom"     , width: 180, dataIndex: 'name'        , sortable: true ,editor : new Ext.form.TextField({allowBlank: false}),renderer : Ext.qd.wow.armory.getIconImgAndText,cellActions:[{
            iconCls : 'icon-addbasket',
            qtip    : 'Ajouter au panier'
          }]},
          {header: "Sur"     , width: 100, dataIndex: 'source'      , sortable: true ,editor : new Ext.form.TextField({allowBlank: false}),renderer : Ext.qd.wow.armory.getItemSource},
          {header: "DropRate", width:  50, dataIndex: 'dropRate'    , sortable: false, resizable : false }
      ]}],
      autoExpandColumn : 'source'
    });
    
    Ext.qd.wow.armory.betterItemGrid.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowBetterItemGrid',Ext.qd.wow.armory.betterItemGrid)