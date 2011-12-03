<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:import href="wow/item-tooltip.xsl"/>
  <xsl:output indent="yes" method="html" />
  <xsl:variable name="loc" select="document('wow/strings.xml')"/>
  <xsl:template match="item/data">
    <div>
      <xsl:call-template name="itemTooltipTemplate" />
    </div>
  </xsl:template>
</xsl:stylesheet>