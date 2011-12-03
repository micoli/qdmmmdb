Ext.qd.wow.armory.character3DModelViewer = Ext.extend(Ext.Panel, {  
  initComponent : function() {
    this.id =Ext.id();
    Ext.apply(this, {
      items : {
        xtype : 'panel',
        width:this.width,
        height : 380,
        id : '3DModel'+this.id 
        }
          });  
    Ext.qd.wow.armory.character3DModelViewer.superclass.initComponent.apply(this, arguments);

  },
  onRender:function(ct, position) {
    Ext.qd.wow.armory.character3DModelViewer.superclass.onRender.call(this, ct, position);
    Ext.Ajax.request({
      url           : 'proxy.php',
      params        : { 
        exw_action    :'QDWowProxy.infoCharacterModelViewer',
        itemList      : this.model.equipList,
        z             : this.armory.z,
        r             : this.armory.r,
        c             : this.model.name
      },
      panel         : this,
      method        : 'GET',
      success       : function ( result, request ) { 
        var q = Ext.DomQuery;
        var jsonEquips = result.responseXML.getElementsByTagName('jsonEquip');
        var character  = result.responseXML.getElementsByTagName('character');
        var equipList=''; 
        var sepa=''; 
        arrRace = ['Human','Orc,','Dwarf','Night Elf','Undead','Tauren','Gnome','Troll','Blood Elf','Draenei'];
        arrSexe = ['male','female'];
        var modele ={
          model       : (''+arrRace[parseInt(request.panel.model.raceId)-1]+''+arrSexe[parseInt(request.panel.model.genderId)]).replace(' ','').toLowerCase(),
          modelType   : 16,
          contentPath : 'http://static.wowhead.com/modelviewer/',
          blur        : 1
        };
        modele.equipList='';
        var sepa='';
        for (i = 0; i < jsonEquips.length; i++) {     
          if (jsonEquips[i]){
              var txtContent = jsonEquips[i].text || jsonEquips[i].textContent ;
              if (txtContent){
              eval('equip={'+txtContent+'};');
              //console.log(jsonEquips[i]);
              if ((equip['slotbak']) && 
                 (equip['slotbak']<=25) &&
                 (equip['slotbak']!=2) &&
                 (equip['slotbak']!=4) &&
                 (equip['slotbak']!=-1)){
                modele.equipList = modele.equipList+sepa+equip['slotbak']+','+equip['displayid'];
                sepa=',';
              }  
            }  
          }  
        }  
        var panel = Ext.getCmp('3DModel'+request.panel.id);
        var t = String(new Ext.ux.Media({ 
              mediaCfg :{
                mediaType : 'SWF',
                url       : 'http://static.wowhead.com/modelviewer/modelviewer_scale.swf?5',
                style     : {
                  display   : 'inline'/*, 
                  width     : panel.el.getWidth()+'px',
                  height    : panel.el.getHeight()+'px'*/
                },
                start     : true,
                loop      : true,
                controls  : false,
                params    : {
                  wmode     :'opaque',
                  scale     :'exactfit',
                  salign    :'t',
                  flashVars : modele
                }
              }
            }));

           panel.el.dom.innerHTML = t;           
      },
      failure       : function ( result, request) { 
        Ext.MessageBox.alert('Erreur', result.responseText); 
      } 
    });
  }
});
Ext.reg('wowCharacter3DModelViewer',Ext.qd.wow.armory.character3DModelViewer);