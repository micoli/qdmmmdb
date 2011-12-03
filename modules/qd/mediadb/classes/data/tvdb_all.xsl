<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:key name="regrouper" match="SeasonNumber" use="." />
<xsl:template match="/">

  <html>
  <body>
    <xsl:apply-templates select="Data/Series" />
  
      <xsl:apply-templates select="Data/Episode/SeasonNumber[generate-id(.)=generate-id(key('regrouper',.)[1])]"/>
  </body>
  </html>
</xsl:template>

<xsl:template match="Series">
  <img><xsl:attribute name="src">proxy.php?exw_action=QDNzbProxy.proxyImg&amp;u=http://thetvdb.com/banners/_cache/<xsl:value-of select="banner"/></xsl:attribute></img>
  <h1><xsl:value-of select='SeriesName'/></h1>
  <xsl:value-of select='Overview'/>
  Acteurs : <xsl:value-of select='translate(Actors,"|",",")'/><br />
  Le <xsl:value-of select='Airs_DayOfWeek'/>@<xsl:value-of select='Airs_Time'/> depuis <xsl:value-of select='FirstAired'/><br />
  rating : <xsl:value-of select='ContentRating'/>
</xsl:template>


<xsl:template match="SeasonNumber">
      <h2>Saison <xsl:value-of select='.'/></h2>
      <ul>
        <xsl:apply-templates select="//EpisodeName[../SeasonNumber=current()]/.."/>
      </ul>
</xsl:template>

<xsl:template match="Episode">
    <li>[<xsl:value-of select="SeasonNumber"/>x<xsl:value-of select="format-number(EpisodeNumber, '00')"/>] : <strong><xsl:value-of select="EpisodeName"/></strong></li>    
    <table border="0">
        <tr>
          <td>
	          <ul>
              <li>Director : <xsl:value-of select="Director" /></li>
              <li>Writer : <xsl:value-of select="Writer" /></li>
	          </ul>
          </td>
        </tr>
        <tr>
          <td><xsl:value-of select="Overview"/></td>
          <td>
            <xsl:if test="filename != ''">
              <img><xsl:attribute name="src">proxy.php?exw_action=QDNzbProxy.proxyImg&amp;u=http://thetvdb.com/banners/_cache/<xsl:value-of select="filename"/></xsl:attribute></img>
            </xsl:if>
          </td>
        </tr>
    </table>
</xsl:template>
</xsl:stylesheet>