<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:include href="includes.xsl"/>

<xsl:include href="item-info-data.xsl"/>

<xsl:template match="page/itemInfo">

<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
<script type="text/javascript" src="/js/mini-search-ajax.js"></script>
<script type="text/javascript" src="/js/item-info-ajax.js"></script>


<div id="dataElement">
    <xsl:apply-templates />
</div>


</xsl:template>

</xsl:stylesheet>
