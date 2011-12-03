Ext.qd.wow.armory.dungeonLoader = Ext.extend(Ext.ux.XmlTreeLoader, {
  processAttributes : function(attr){
      if(attr.tagName=="dungeons"){ // is it an author node?
          attr.text = "donjons";
          attr.loaded = true;
      }
      else if(attr.tagName=="dungeon"){ // is it an author node?
        attr.text = attr.name;
        attr.loaded = true;
      }
      else if(attr.tagName="boss"){ // is it a book node?
          attr.text = attr.name + ' (' + attr.id + ')';
          attr.iconCls = 'book';
          attr.leaf = true;
      }
  }
});
Ext.qd.wow.armory.dungeonTree = Ext.extend(Ext.tree.TreePanel, {
  title: 'Reputations',
  loader: new Ext.qd.wow.armory.dungeonLoader({
    dataUrl : 'proxy.php?exw_action=QDWowProxy.dungeonTree',
    rootNodeCB : function (root){
      return root.getElementsByTagName('dungeons');
    }  
  }),
  rootVisible: false,
  border: false,
  autoScroll : true,
  initComponent: function(){
    //reputationTree.setRootNode(loadXmlReputation(charXmlDoc.getElementsByTagName('reputationTab')[0]));

    Ext.apply(this, {
      root: new Ext.tree.AsyncTreeNode()
    });
    Ext.qd.wow.armory.dungeonTree.superclass.initComponent.apply(this, arguments);
  }
});
Ext.reg('wowDungeonTree', Ext.qd.wow.armory.dungeonTree);


