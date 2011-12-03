<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE xc:content [
<!ENTITY % xhtml PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
%xhtml;
]>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="iso-8859-1" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"/>
  <xsl:output indent="yes" method="html" />
  <xsl:template match="/page/guildInfo/guild/members">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <style>
    .r1        { background:#ccc; padding:3px; }  
    .r2       { background:#eee; padding:3px; }  
  </style>
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
  <title>GU</title>
    <script type="text/javascript" src="js/mootools.js"></script>
    <script type="text/javascript" src="js/AddictTable.js"></script>
    <script type="text/javascript" src="js/swfobject.js"></script>
    <link href="css/slimbox_ex.css" rel="stylesheet" type="text/css"></link>
    <script type="text/javascript" src="js/slimbox_ex.js"></script>
    <script type="text/javascript">
      window.addEvent('domready', function() {
        //Lightbox.open('http://www.yahoo.com', 'Spheres in construction');
        var pagination = new AddictTable($('characters'), {
          currentPage  : 0,
          lines        : 20,
          sort         : {
            col          : 1,
            way          : 'asc'
          },
          filter       : {
            col          : 2,
            value        : 'Druide',
            field        : $('selectClass')
          },
          buttons : {
            'premiere'  : 'firstPage'  ,
            'precedente': 'previousPage',
            'suivante'  : 'nextPage'   ,
            'derniere'  : 'lastPage'     
          }

        });
      });
    </script>
  </head>
  <body>  
  
  <select id="selectClass"></select>
  <table id="characters">
    <thead>
      <tr>
        <th>Nom</th>
        <th>Niveau</th>
        <th>Class</th>
        <th>Race</th>
        <th>Rang</th>
      </tr>
    </thead>
    <tbody>
      <xsl:for-each select="character">
        <tr>          
          <td><a rel="lightbox[site_sample]"><xsl:attribute name="title">Character</xsl:attribute><xsl:attribute name="href">?r=eitrigg&amp;z=eu&amp;c=<xsl:value-of select="@name" /></xsl:attribute><xsl:value-of select="@name"/></a></td>
          <td><xsl:value-of select="@level"/></td>
          <td><xsl:value-of select="@class"/></td>
          <td><xsl:value-of select="@race"/></td>
          <td><xsl:value-of select="@rank"/></td>
        </tr>      
      </xsl:for-each>
      </tbody>
    </table>
    <div id="actions">
      <a href="javascript:void();" id="premiere">|&lt;</a>&nbsp;
      <a href="javascript:void();" id="precedente">&lt;&lt;</a>&nbsp;
      <a href="javascript:void();" id="suivante">&gt;&gt;</a>&nbsp;
      <a href="javascript:void();" id="derniere">&gt;|</a>
    </div>
    <div id="myContent1"></div>
    <div id="myContent2"></div>
  </body>
  </html>
</xsl:template>
</xsl:stylesheet>