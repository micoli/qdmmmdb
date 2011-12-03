/*
 * Ext JS Library 2.2
 * Copyright(c) 2006-2008, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.namespace("Ext.qd");
Ext.namespace("Ext.qd.wow");
Ext.namespace("Ext.qd.wow.armory");
Ext.namespace("Ext.qd.wow.combatLog");

// TODO : resync dans l'url
// TODO : redim de la fenetre personnage en fonction des ouvertures
// TODO : recuperation des gemmes et enchants
// TODO : comparateur d'item


Ext.qd.wow.armory.getClassFromClassId =  function (a){
  var res = ['Guerrier','Paladin','Chasseur','Voleur','Pretre','Chevalier de la Mort','Chaman','Mage','Demoniste','','Druide'];
  return res [a-1];   
}

Ext.qd.wow.armory.colorizeIconName = function (v,e,r){
  var qual = r.data.quality;
  return '<span class="wow-quality-'+qual+'">'+v+'</span>';
}

Ext.qd.wow.armory.getLevelFromJson = function (v){
  eval("v = {"+v+"}");
  return v.level || '';
}

Ext.qd.wow.armory.getIconImg = function(v){
  if (v){
    return '<img style="width:16px;height:16px" src="/WoW_Icons/Icons/'+v.toLowerCase()+'.jpg" />';
  }else{
    return '';
  }
}

Ext.qd.wow.armory.replaceSocketImg = function(v){
  //var rgx = new RegExp('/shared/global/tooltip/images/icons/(.*?)\.png','g');
  //var arrFound = v.match(rgx);
  //for (var i=0;i<arrFound.length;i++){
    return  v.replace(/\/shared\/global\/tooltip\/images\/icons/g,'skins/interface');
    //}
  //return v;
}

Ext.qd.wow.armory.getIconImgAndText = function(v,e,r){
  var qual = r.data.quality;
  return '<img style="width:16px;height:16px" src="/WoW_Icons/Icons/'+r.data.icon.toLowerCase()+'.jpg" />'+
         '<span class="wow-quality-'+qual+'">'+r.data.name+'</span>' ;
}

Ext.qd.wow.armory.addToBasket = function (data,record){
  globalItemBasketStore.store.add(record);
}

Ext.qd.wow.armory.getItemSource = function (v,e,r){
  //console.log(r.node.getElementsByTagName('itemSource')[0]);
  //console.log(r.node);
  var str = '';
  switch(r.data.itemSource){
    case 'sourceType.createdBySpell':
      str = 'Craft';
    break;
    case 'sourceType.questReward':
      str = 'Quete';
    break;
    //sourceType.factionReward
    case 'sourceType.vendor':
    case 'sourceType.vendorPvP':
    case 'sourceType.pvpReward':
      str = 'Vendeur ';
    break;
    case 'sourceType.none':
      str = '';
    break;
    case 'sourceType.creatureDrop':
      str = '';
    break;
    case 'sourceType.creatureDrop':
      str = '';
    break;
    case 'sourceType.gameObjectDrop':
      return 'Coffre ';
    break;
    case 'sourceType.factionReward':
      str='';
    break;
    default : 
      str = '';
    break;    
  }
  if (r.data.title!=''){
    str = str +' '+ r.data.title;
  }
  if (r.data.creatureName!=''){
    str = str +' '+ r.data.creatureName;
  }
  if (r.data.area!=''){
    str = str +' ('+ r.data.area;
    if (r.data.heroic=='1'){
      str = str + ' heroic';
    }
    str = str +')';
  }
  return str;
}

Ext.qd.wow.armory.getArmorText = function(v){
  return Ext.qd.wow.armory.cons.armor[v];
}

Ext.qd.wow.armory.OpenBetterItem = function (armory,charName,itemID){
  var win2 = new Ext.Window({
    layout      : 'fit',
    width       : 600,
    height      : 450,
    title       : 'Meilleur equipement',
    closeAction :'close',
    plain       : true,
    modal       : false,
    items       :  {
      xtype       : 'wowBetterItemGrid',
        itemID    : itemID,
        charName  : charName,
        armory    : armory
    }
    ,
    buttons: [{
      text     : 'Close',
      handler  : function(){
        win2.close();
      }
    }]
  });
  win2.show();  
}

Ext.qd.wow.armory.characterModelViewer = function (armory,modele){
  var win = new Ext.Window({
    layout      : 'fit',
    width       : 500,
    height      : 450,
    title       : 'Personnage '+modele.name,
    closeAction : 'close',
    plain       : true,
    modal       : false,
    items       :  {
      xtype       : 'wowCharacter3DModelViewer',
        itemList  : modele.itemList,
        model     : modele,
        armory    : armory
    }
    ,
    buttons: [{
      text     : 'Close',
      handler  : function(){
        win.close();
      }
    }]
  });
  win.show();  
}
function getObjInnerText(obj){
  if (window.IE) {
   // IE;
    return obj.text;
  }else{
    if (obj.textContent)  {
      return obj.textContent;
    }else{
      return "";//alert("Error: This application does not support your browser. Try again using IE or Firefox.");
    }
  }
} 

Ext.qd.wow.armory.createGuildTab = function(divId){
  var tabs = new Ext.TabPanel({
    renderTo : divId,
    width    : 540,
    height   : 350,
    activeTab: 0,
    items: [new Ext.qd.wow.armory.guildGrid ({
      title : 'Guilde'
    }), new Ext.qd.wow.armory.dungeonTree ({
      title : 'Donjons'
    })]
  });
}
Ext.qd.wow.armory.createItembasket = function(divId){ 
  globalItemBasketStore = new Ext.qd.wow.armory.itemBasket ({
    renderTo : divId,
    width    : 540,
    height   : 200
  });
}

Ext.onReady(function(){
    //window.loadFirebugConsole(); 
    Ext.QuickTips.init();
    Ext.form.Field.prototype.msgTarget = 'side';
    Ext.qd.wow.armory.z = 'eu';
    Ext.qd.wow.armory.g = 'Addict';
    Ext.qd.wow.armory.r = 'Eitrigg';
    Ext.qd.wow.armory.itemTemplate = new Ext.qd.XslTemplate("uri","modules/extjswowarmory/xsl/item.xsl",Ext.qd.wow.armory.replaceSocketImg); 
    
    // turn on validation errors beside the field globally
    Ext.form.Field.prototype.msgTarget = 'side';
    //Ext.qd.wow.armory.OpenItem(300,'35573');
    //Ext.qd.wow.armory.characterModelViewer(Ext.qd.wow.armory,'Tinmarok','0,29081|1,34679|3,14617|5,33280|6,33527|7,33878|8,28454|9,29085|10,34887|11,34075|12,38287|13,28034|14,33484|15,28524|16,28768|17,34892|18,35221');
    //Ext.qd.wow.armory.OpenBetterItem(Ext.qd.wow.armory,'Azn',41386);
    
    /*var globalgridFights = new Ext.qd.wow.combatLog.gridFights ({
      url      : './cache/wowlogparser/1/BossFightsIndex.xml',
      renderTo : 'itemBasket',
      width    : 540,
      height   : 500
    });
    var globalgridFights = new Ext.qd.wow.combatLog.gridDps ({
      url      : './cache/wowlogparser/1/0_1806856_1395.xml',
      renderTo : 'divGuild',
      width    : 540,
      height   : 500
    });
    //globalgridFights.load({url: './cache/wowlogparser/1/BossFightsIndex.xml',waitMsg:'chargement'});
   */
    
    Ext.qd.wow.armory.createItembasket('itemBasket');
    Ext.qd.wow.armory.OpenCharacter(Ext.qd.wow.armory,'Aminia');
    Ext.qd.wow.armory.createGuildTab('divGuild');  
    
});
