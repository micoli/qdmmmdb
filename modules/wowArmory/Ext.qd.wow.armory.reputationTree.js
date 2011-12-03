Ext.qd.wow.armory.reputationTree = Ext.extend(Ext.tree.TreePanel, {
  title: 'Reputations',
  loader: new Ext.tree.TreeLoader(),
  rootVisible: false,
  border: false,
  initComponent: function(){
    Ext.apply(this, {
      root: new Ext.tree.AsyncTreeNode()
    });
    Ext.qd.wow.armory.reputationTree.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowReputationTree', Ext.qd.wow.armory.reputationTree);

 
Ext.qd.wow.armory.factionText = function (nb){
  //todo reputation
  if       (nb>=-43000 && nb< -42000) {
    return "D&eacute;test&eacute; "+ (nb)+'/-999'
  }else if  (nb>=-42000 && nb<  -6000) {
    return "Hostile "+ (nb) +'/-42000';
  }else if  (nb>= -6000 && nb<  -3000) {
    return "Inamical "+ (nb)+'/-3000';
  }else if  (nb>= -3000 && nb<      0) {
    return "Neutre "+ (nb)+'/-3000';
  }else if (nb>=  3000 && nb<   9000) {
    return "Amical "+ (nb-3000)+'/6000';
  }else if (nb>=  9000 &&  nb< 21000) {
    return "Honor&eacute; "+ (nb-9000)+'/12000';
  }else if (nb>= 21000 &&  nb< 42000) {
    return "R&eacute;v&eacute;r&eacute; "+ (nb-21000)+'/21000';
  }else if (nb>= 42000 &&  nb< 43000) {
    return "Exalt&eacute; "+ (nb-42000)+'/999';
  }  
    
  return nb;
  /*
    * Exalté
      Palier supérieur : 1000
      
    * Révéré
      Palier supérieur : 21000
      
    * Honoré
      Palier supérieur : 12000
      
    * Amical
      Palier supérieur : 6000
      
    * Neutre
      Palier supérieur : 3000
      
    * Inamical
      Palier supérieur : 3000
      
    * Hostile
      Palier supérieur : 3000
      
    * Détesté
      Palier supérieur : 36000
 */
}
/**
  Create a TreeNode from an XML node
*/
function loadXmlReputation(XmlEl) {
  //Text is nodeValue to text node, otherwise it's the tag name
  var t = ((XmlEl.nodeType == 3) ? XmlEl.nodeValue : XmlEl.tagName);

  //For Elements, process attributes and children
  if (XmlEl.nodeType == 1) {
      if (XmlEl.tagName=="factionCategory"){
        var txt = XmlEl.getAttribute('name')+"";
      }else{
        var txt = XmlEl.getAttribute('name')+""+' ['+ Ext.qd.wow.armory.factionText(XmlEl.getAttribute('reputation'))+']';
      }
      var result = new Ext.tree.TreeNode({
        text: txt
      });
      //result.appendChild(c);
      Ext.each(XmlEl.childNodes, function(el) {
        //Only process Elements and TextNodes
        if ((el.nodeType == 1) || (el.nodeType == 3)) {
          var c = loadXmlReputation(el);
          if (c) {
            result.appendChild(c);
          }
        }
      });
  }
  return result;
}