<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<!-- NOTE: taken from p. 41-42 of XSLT Cookbook (c) 2006 O'Reilly Media, Inc. -->
<xsl:template name="search-and-replace">
	<xsl:param name="input" />
	<xsl:param name="search-string" />
	<xsl:param name="replace-string" />
	<xsl:choose>
		<!-- See if the input contains the search string -->
		<xsl:when test="$search-string and contains($input, $search-string)">
		<!-- If so, then concatenate the substring before the search
		    string to the replacement string and to the result of
		    recursively applying this template to the remaining substring.
		-->
			<xsl:value-of select="substring-before($input, $search-string)" />
			<xsl:value-of select="$replace-string"/>
			<xsl:call-template name="search-and-replace">
				<xsl:with-param name="input" select="substring-after($input, $search-string)" />
				<xsl:with-param name="search-string" select="$search-string" />
				<xsl:with-param name="replace-string" select="$replace-string" />
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<!-- There are no more occurrences of the search string so
			    just return the current input string -->
			<xsl:value-of select="$input" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="dropdownMenu">
    <xsl:param name="defaultValue" />
    <xsl:param name="hiddenId" />
    <xsl:param name="divClass" />
    <xsl:param name="anchorClass" />    
    <xsl:param name="dropdownList" />

<script type="text/javascript">

    var varOver<xsl:value-of select ="$hiddenId" /> = 0;


</script>
    
 <div class="{$divClass}" onMouseOver="javascript: varOver{$hiddenId} = 1;" onMouseOut="javascript: varOver{$hiddenId} = 0;">
  <a class="{$anchorClass}" id = "display{$hiddenId}" href = "javascript: document.formDropdown{$hiddenId}.dummy{$hiddenId}.focus();"><xsl:value-of select = "$defaultValue" /></a>
  </div>
<div style="position: relative;"><div style="position: absolute;"><form name = "formDropdown{$hiddenId}" id = "formDropdown{$hiddenId}" style="height: 0px;"><input type="button" id="dummy{$hiddenId}" onFocus = "javascript: dropdownMenuToggle('dropdownHidden{$hiddenId}');" onBlur = "javascript: if(!varOver{$hiddenId}) document.getElementById('dropdownHidden{$hiddenId}').style.display='none';" size="2" style = "position: relative; left: -5000px;"/></form></div></div>
</xsl:template>

<xsl:template name="positionSuffix">
   <xsl:param name="pos"/>
   <xsl:choose>
     <xsl:when test="($pos = 1) "><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.1st']"/></xsl:when>
     <xsl:when test="($pos mod 10 &gt; 3) or ($pos mod 10 = 0) or (($pos mod 100 &gt;= 11) and ($pos mod 100 &lt;= 13)) "><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.th']"/></xsl:when>
     <xsl:when test="($pos mod 10 = 1) "><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.st']"/></xsl:when>
     <xsl:when test="($pos mod 10 = 2) "><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.nd']"/></xsl:when>
     <xsl:when test="($pos mod 10 = 3) "><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.rd']"/></xsl:when>
     <xsl:otherwise><xsl:value-of select="$loc/strs/arena/arenaLadderData/str[@id='armory.arena-ladder-data.th']"/></xsl:otherwise>
   </xsl:choose>
</xsl:template>



<!-- ######## Flash Template (used for flash objects) ################################################ -->
<xsl:template match="flash" >
    <xsl:call-template name="flash">
            <xsl:with-param name="id" select="@id"></xsl:with-param>
            <xsl:with-param name="width" select="@width"></xsl:with-param>
            <xsl:with-param name="height" select="@height"></xsl:with-param>    
            <xsl:with-param name="src" select="@src"></xsl:with-param>
            <xsl:with-param name="quality" select="@quality"></xsl:with-param>
            <xsl:with-param name="base" select="@base"></xsl:with-param>
            <xsl:with-param name="flashvars" select="@flashvars"></xsl:with-param>
            <xsl:with-param name="bgcolor" select="@bgcolor"></xsl:with-param>
            <xsl:with-param name="menu" select="@menu"></xsl:with-param>
            <xsl:with-param name="wmode" select="@wmode"></xsl:with-param>
    </xsl:call-template>
</xsl:template>



<xsl:template name="flash">
    <xsl:param name="id" />
    <xsl:param name="width" />
    <xsl:param name="height" />
    <xsl:param name="src" />
    <xsl:param name="quality" />
    <xsl:param name="base" />
    <xsl:param name="flashvars" />
    <xsl:param name="bgcolor" />
    <xsl:param name="menu" />
    <xsl:param name="wmode" />
    <xsl:param name="noflash" />
    

		<div id="{$id}" style="display:none;"></div>
		<script type="text/javascript">
		var flashId="<xsl:value-of select='$id'/>";
		if ((Browser.safari &amp;&amp; flashId=="flashback") || (Browser.linux &amp;&amp; flashId=="flashback")){//kill the searchbox flash for safari or linux
		   document.getElementById("searchFlash").innerHTML = '<div class="search-noflash"></div>';
		}else
			printFlash("<xsl:value-of select='$id'/>", "<xsl:value-of select='$src'/>", "<xsl:value-of select='$wmode'/>", "<xsl:value-of select='$menu'/>", "<xsl:value-of select='$bgcolor'/>", "<xsl:value-of select='$width'/>", "<xsl:value-of select='$height'/>", "<xsl:value-of select='$quality'/>", "<xsl:value-of select='$base'/>", "<xsl:value-of select='$flashvars'/>", "<xsl:value-of select='$noflash'/>")
		
		</script>	
	
</xsl:template>


</xsl:stylesheet>
