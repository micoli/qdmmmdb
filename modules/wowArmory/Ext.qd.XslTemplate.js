/**
 * @author o.michaud
 */
Ext.qd.XslTemplate = function (xslType,xsl,funcCB){
  this.XslDoc={};
  if (xslType == "string"){
    this.XslDoc = Ext.qd.loadXMLString(xsl);
  }else if (xslType == "array"){
    this.XslDoc = Ext.qd.loadXMLString(Ext.join(xsl,"\n"));
  }else if (xslType == "uri"){
    this.XslDoc = Ext.qd.loadXMLUri(xsl);
  }
  if (funcCB) this.funcCB = funcCB;
}

Ext.qd.XslTemplate.prototype = {
  transform  : function (xmlType,xml){
    //string,array,uri,dom
    if (xmlType == "string"){
      var xml=Ext.qd.loadXMLString(xml);
    }else if (xmlType == "array"){
      var xml=Ext.qd.loadXMLString(Ext.join(xml,"\n"));
    }else if (xmlType == "uri"){
      var xml=Ext.qd.loadXMLUri(xml);
    }
    
    var t = document.createElement('div');
    if (window.ActiveXObject)  {
      // code for IE
      t.innerHTML = xml.transformNode(this.XslDoc);
    }else if (document.implementation && document.implementation.createDocument)  {
      // code for Mozilla, Firefox, Opera, etc.
      var xsltProcessor = new XSLTProcessor();
      xsltProcessor.importStylesheet(this.XslDoc);
      t.appendChild(xsltProcessor.transformToFragment(xml,document));
    }
    var v = t.innerHTML;
    if (this.funcCB) {
      v = this.funcCB(v);
    }
    return v;
  } 
}
