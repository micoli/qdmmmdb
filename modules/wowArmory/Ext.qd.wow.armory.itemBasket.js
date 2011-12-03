Ext.qd.wow.armory.itemBasket = Ext.extend(Ext.grid.GridPanel, {
    initComponent : function() {
      var id = this.id;
      this.cellActions = new Ext.ux.grid.CellActions({
        callbacks:{
          'icon-remove' : function(grid, record, action, value) {
            grid.store.remove(record);
          }  
        }
        ,align:'left'
      });
      this.expander = new Ext.qd.XslRowExpander({
        tpl : Ext.qd.wow.armory.itemTemplate
      });
     
      Ext.apply(this, {
        id : id,
        store : new Ext.data.GroupingStore({
          reader   : new Ext.data.ArrayReader({
           },[
             {name: 'name'         , mapping: 'datas/name'}
          ])              
        }),
        plugins:[
          this.expander,
          this.cellActions
        ],
        columns: [
          this.expander,        
          {header: "Nom"        , width: 300, dataIndex: 'name'         , sortable: true,renderer : Ext.qd.wow.armory.getIconImgAndText,cellActions:[{
            iconCls : 'icon-remove',
            qtip    : 'Retirer du panier'
          }]}
        ],
        header  : false,
        tbar : [{
          text : 'Voir la liste',
          listeners : {
            click : function(a,b,c,d,e){
              var BigStr = '';
              var grid = Ext.getCmp(id);
              var items = grid.store.data.items;
              var sepa = '';
              for (var i = 0; i<items.length;i++){
                BigStr = BigStr + sepa + '<div>';//<u><b>'+(Ext.qd.wow.armory.getIconImgAndText('','',items[i]))+'</b></u><br />';
                BigStr = BigStr + (grid.expander.tpl.transform("dom",items[i].node));+'</div>';
                sepa='<br /><br /><hr />';
              }
              var winClip = new Ext.Window({
                layout      : 'fit',
                width       : 400,
                height      : 450,
                title       : 'Liste',
                closeAction :'close',
                plain       : true,
                modal       : false,
                items       :  {
                  xtype       : 'panel',
                  autoScroll  : 'auto',
                  html        : BigStr
                }
                ,
                buttons: [{
                  text     : 'Close',
                  handler  : function(){
                  winClip.close();
                  }
                }]
              });
              winClip.show();  
            }
          }
        }]});
      
      Ext.qd.wow.armory.itemBasket.superclass.initComponent.apply(this, arguments);
    }
  });
  Ext.reg('wowItemBasket',Ext.qd.wow.armory.itemBasket)