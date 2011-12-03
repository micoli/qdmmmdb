<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:template name="printMoneyGold">
    <xsl:param name="money" />
    <xsl:if test="$money &gt;= 10000"><xsl:value-of select="floor($money div 10000)" /></xsl:if>
</xsl:template>

<xsl:template name="printMoneySilver">
    <xsl:param name="money" />
    <xsl:if test="($money &gt;= 100) and floor(($money div 100) mod 100) != 0"><xsl:value-of select="floor(($money div 100) mod 100)" /></xsl:if>
</xsl:template>

<xsl:template name="printMoneyCopper">
    <xsl:param name="money" />
    <xsl:if test="($money &gt;= 0) and ($money mod 100 != 0)"><xsl:value-of select="$money mod 100" /></xsl:if>
</xsl:template>


</xsl:stylesheet>
