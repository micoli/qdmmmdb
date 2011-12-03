Ext.qd.wow.combatLog.rendererFightDate=function (r,e,v){
	return v.data.day+'/'+v.data.month+' '+v.data.hour+':'+v.data.minute+':'+v.data.second;
}
Ext.qd.wow.combatLog.gridFights = Ext.extend(Ext.grid.GridPanel, {
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
           //Ext.qd.wow.armory.OpenBetterItem(grid.armory,grid.charName,record.data.id);
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
   
    Ext.apply(this, {
      title : 'Fights',
      store : new Ext.data.Store({
      	url      : this.url,
      	autoLoad : true,
        reader   : new Ext.data.XmlReader({
           record  : 'FightReference'
         },[
           {name : 'day'          ,mapping:'@day'},
           {name : 'duration'     ,mapping:'@duration'},
           {name : 'filename'     ,mapping:'@filename'},
           {name : 'hour'         ,mapping:'@hour'},
           {name : 'minute'       ,mapping:'@minute'},           
           {name : 'month'        ,mapping:'@month'},           
           {name : 'name'         ,mapping:'@name'},
           {name : 'numMobs'      ,mapping:'@numMobs'},
           {name : 'second'       ,mapping:'@second'}
        ])              
      }),
      plugins:[
        this.cellActions
      ],
      columns : [        
        {header: "name"       , width: 230, dataIndex: 'name'         },
        {header: "filename"   , width: 150, dataIndex: 'filename'     }, 
        {header: "date"       , width: 100, dataIndex: 'day'          ,renderer : Ext.qd.wow.combatLog.rendererFightDate}, 
        {header: "duration"   , width:  60, dataIndex: 'duration'     }
      ]
    });
    
    Ext.qd.wow.combatLog.gridFights.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowFightsGrid',Ext.qd.wow.combatLog.gridFights );

Ext.qd.wow.combatLog.gridDps = Ext.extend(Ext.grid.GridPanel, {
  initComponent : function() {
    Ext.apply(this, {
      title : 'Fights',
      store : new Ext.data.Store({
        url      : this.url,
        autoLoad : true,
        reader   : new Ext.data.XmlReader({
           record  : 'Par'
         },[
           {name : 'who'          ,mapping:'AcDaDu'},
           {name : 'cri'          ,mapping:'ToDa/Cri'},
           {name : 'DPS'          ,mapping:'@filename'},
           {name : 'Dtotaux'      ,mapping:'@hour'},
           {name : 'Dmoyen'       ,mapping:'@minute'},           
           {name : 'touche'       ,mapping:'@month'},           
           {name : 'Crit'         ,mapping:'@name'},
           {name : 'Miss'         ,mapping:'@numMobs'}
        ])              
      }),
      columns : [        
        {header: "who"       , width: 230, dataIndex: 'who'       },
        {header: "type"      , width: 150, dataIndex: 'cri'       , 
          renderer : function (r,e,v){
          	console.log(r,e,v);
          	return v.node.data;
          }
        }, 
        {header: "DPS"       , width: 100, dataIndex: 'day'       }, 
        {header: "Dtotaux"   , width:  60, dataIndex: 'Dtotaux'   },
        {header: "Dmoyen"    , width:  60, dataIndex: 'Dmoyen'    },
        {header: "touche"    , width:  60, dataIndex: 'touche'    },
        {header: "crit"      , width:  60, dataIndex: 'crit'      },
        {header: "miss"      , width:  60, dataIndex: 'miss'      },
        {header: "touche"    , width:  60, dataIndex: 'touche'    }
      ]
    });
    
    Ext.qd.wow.combatLog.gridDps.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowDpsGrid',Ext.qd.wow.combatLog.gridDps )
