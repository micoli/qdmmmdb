Ext.qd.wow.armory.OpenCharacter = function (armory,charName){
  // turn on validation errors beside the field globally
  var charID = Ext.id();
  var win = new Ext.Window({
    layout      : 'fit',
    title:'Perso',
    width       : 800,
    height      : 450,
    closeAction :'close',
    plain       : true,
    modal : false,
    items       : {
      xtype : 'form',
      id    :'charForm'+charID,
      frame: true,
      layout : 'border',
      labelAlign: 'right',
      
      labelWidth: 85,
      width:340,
      model:{},
      equipList:{},
      waitMsgTarget: true,
      reader : new Ext.data.XmlReader({
        record : 'characterInfo'
      }, [
        {name: 'name'    , mapping:'character/@name'},
        {name: 'race'    , mapping:'character/@race'},
        {name: 'gender'  , mapping:'character/@gender'},
        {name: 'raceId'  , mapping:'character/@raceId'},
        {name: 'genderId', mapping:'character/@genderId'},
        {name: 'level'   , mapping:'character/@level'},
        {name: 'class'   , mapping:'character/@class'},
        {name: 'items'   , mapping:'character/@items'}
      ]),
      characterTemplate :  new Ext.qd.XslTemplate("uri","modules/extjswowarmory/xsl/character.xsl"),
      listeners : {
        actioncomplete: function(form, action){
          if(action.type == 'load'){
            var charXmlDoc = form.reader.xmlData;
            form.model = {
              raceId   : action.result.data['raceId'],
              genderId : action.result.data['genderId'],
              name     : action.result.data['name']
            };
            win.setTitle(action.result.data['name']+ ' ('+charXmlDoc.getElementsByTagName('character')[0].getAttribute('class')+', '+action.result.data['level']+')');
            var gridItems = Ext.getCmp('items'+charID+charName);
            var arecords = gridItems.store.reader.readRecords(charXmlDoc);
            gridItems.store.loadRecords(arecords,{});
            
            var q = Ext.DomQuery;
            var ns = q.select('item[@onList=1]', charXmlDoc);
            form.model.equipList=''; 
            var sepa=''; 
            for (i = 0; i < ns.length; i++) {
              var slot = ns[i].getAttribute('slot');
              form.model.equipList = form.model.equipList+sepa+slot+','+ns[i].getAttribute('id');
              sepa='|'
            }
            var reputationTree = Ext.getCmp('reputationTree'+charID);
            reputationTree.setRootNode(loadXmlReputation(charXmlDoc.getElementsByTagName('reputationTab')[0]));
            Ext.getCmp('panelChar'+charID).el.dom.innerHTML = form.characterTemplate.transform("dom",charXmlDoc,"uri","modules/extjswowarmory/xsl/character.xsl");
          }
        }
      },
      items: [{
          xtype       : 'fieldset',
          region      : 'west',
          collapsible : true,
          width       : 300,
          title       : '&nbsp;',
          height      : 'auto',
          autoHeight  : true,
          defaultType : 'textfield',
          items       : [{
            xtype       : 'button',
            text        : 'view 3D',
            listeners   : {
              click       : function(){
                Ext.qd.wow.armory.characterModelViewer(Ext.qd.wow.armory,Ext.getCmp('charForm'+charID).form.model); 
              }
            }
          },{
            xtype  : 'panel',
            height : 3,
            id     : 'panelChar'+charID
          }]
        },{
          region    : 'center',
          xtype     : 'tabpanel',
          activeTab : 0,
          items:[{
            xtype     : 'wowCharacterItemGrid',
            armory    : armory,
            charName  : charName,
            id        : 'items'+charID+charName
          },{
            title : 'Reputations',
            xtype : 'wowReputationTree',
            id    : 'reputationTree'+charID
          }]            
        
      }]
    },
    buttons: [{
      text     : 'Close',
      handler  : function(){
        win.close();
      }
    }]
  });
  win.show();
  Ext.getCmp('charForm'+charID).load({url: 'proxy.php?exw_action=QDWowProxy.infoCharacter&z='+armory.z+'&r='+armory.r+'&c='+charName,waitMsg:'chargement'});
}
