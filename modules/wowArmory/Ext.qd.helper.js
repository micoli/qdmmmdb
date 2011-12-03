Ext.qd.LoadXmlString = function (stri){
  try {
    //Internet Explorer
    var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
    xmlDoc.async="false";
    xmlDoc.loadXML(text);
  }catch(e) {
  try {
    //Firefox, Mozilla, Opera, etc.
    parser=new DOMParser();
    var xmlDoc=parser.parseFromString(text,"text/xml");
  }catch(e) {
    alert(e.message)}
  } 
  return xmlDoc;
}

Ext.qd.loadXMLUri = function (fname){
  var xmlDoc;
  if (window.ActiveXObject)  {
    // code for IE
    xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
  }else if (document.implementation && document.implementation.createDocument) {
    // code for Mozilla, Firefox, Opera, etc.
    xmlDoc=document.implementation.createDocument("","",null);
  }else{
    alert('Your browser cannot handle this script');
  }
  xmlDoc.async=false;
  xmlDoc.load(fname);
  return(xmlDoc);
}

Ext.qd.getItemBySubStr = function (coll,propertyName,propertyValue){
  var idx = coll.findIndex(propertyName,propertyValue);
  if (idx>=0){
    return coll.itemAt(idx);
  }else{
    return null;
  }
}
