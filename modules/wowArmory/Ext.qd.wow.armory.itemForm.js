
Ext.qd.wow.armory.OpenItem = function (itemID,slot,displayID){
  var modele ={
      model       : displayID,
      modelType   : 1,
      contentPath : 'http://static.wowhead.com/modelviewer/',
      blur        : 1
    };
  var win = new Ext.Window({
    layout      : 'fit',
    title       :'Item '+itemID + ' en debuggage',
    width       : 650,
    height      : 480,
    closeAction :'close',
    plain       : true,
    modal : false,
    items: [{
      xtype : 'mediapanel',
      mediaCfg :{
        mediaType : 'SWF',
        url       : 'http://static.wowhead.com/modelviewer/modelviewer_scale.swf?5',
        style     : {
          display   :'inline', 
          width     :'600px',
          height    :'400px'
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
    }], 
    buttons: [{
      text     : 'Close',
      handler  : function(){
        win.close();
      }
    }]
  });
  win.show();
}
