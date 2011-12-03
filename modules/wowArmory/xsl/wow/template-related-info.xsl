<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
<xsl:variable name="pageType" select="/page/@type"/>

<xsl:variable name="lcletters">abcdefghijklmnopqrstuvwxyzбвгдежзиклмнопсуфхцчшщъыьэюя</xsl:variable>
<xsl:variable name="ucletters">ABCDEFGHIJKLMNOPQRSTUVWXYZБВГДЕЖЗИКЛМНОПСУФХЦЧШЩЪЫЬЭЮЯ</xsl:variable>

<xsl:template name="replace-string-news">
    <xsl:param name="text"/>
    <xsl:param name="replace"/>
    <xsl:param name="with"/>
    <xsl:choose>
      <xsl:when test="contains($text,$replace)">
        <xsl:value-of select="substring-before($text,$replace)"/>
        <xsl:value-of select="$with"/>
        <xsl:call-template name="replace-string-news">
          <xsl:with-param name="text"
select="substring-after($text,$replace)"/>
          <xsl:with-param name="replace" select="$replace"/>
          <xsl:with-param name="with" select="$with"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$text"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>


<xsl:template match="news/achievement">
<xsl:param name="feedtxt"/>
<xsl:param name="lvl80pos"/>
     <xsl:if test="../@icon = 'regionFirst'">
         <div><b style="color:white">
        <xsl:call-template name="replace-string-news">
        <xsl:with-param name="text" select="@title"/>
        <xsl:with-param name="replace" select="$loc/strs/achievements/str[@id='realmfirst']"/>
        <xsl:with-param name="with" select="$loc/strs/achievements/str[@id='achievementfirst']"/>
        </xsl:call-template>
        </b> - <span class="timestamp-news" style="display:none"><xsl:value-of select="../@posted"/></span></div>
    </xsl:if>
    <div>
    <xsl:variable name="skey">
        <xsl:choose>
        <xsl:when test="contains(@key,'.grand.master')">realm.first.grand.master.profession</xsl:when>
        <xsl:when test="contains(@key,'.level.80.')">realm.first.level.80.classrace</xsl:when>
        <xsl:when test="count(guild) = 1">
        <xsl:value-of select="@key"/>.single</xsl:when>
        <xsl:otherwise><xsl:value-of select="@key"/></xsl:otherwise>
        </xsl:choose>
    </xsl:variable>

    <xsl:variable name="textkey" select="concat(../@icon,'.',$skey)"/>
    <!--<xsl:value-of select="$textkey"/>-->
    <xsl:apply-templates select="$feedtxt/strs/str[@id=$textkey]" mode="printf">
    <xsl:with-param name="param1">
        <xsl:choose>
            <xsl:when test="character"><a href="character-sheet.xml?{character/@url}"><xsl:value-of select="character/@name"/></a></xsl:when>
            <xsl:otherwise>
            <xsl:for-each select="guild">

            <xsl:choose>
                <xsl:when test="@name=''"><xsl:value-of select="$loc/strs/achievements/str[@id='solo']"/></xsl:when>
                <xsl:otherwise><a href="guild-info.xml?{@guildUrl}">&lt;<xsl:value-of select="@name"/>&gt;</a></xsl:otherwise>
                </xsl:choose>
                (<xsl:value-of select="@count"/>)<xsl:choose><xsl:when test="position() &lt; last() - 1">, </xsl:when><xsl:when test="position()=last()"></xsl:when><xsl:otherwise> <xsl:text> </xsl:text> <xsl:value-of select="$loc/strs/achievements/str[@id='achv.and']"/><xsl:text> </xsl:text> </xsl:otherwise>
            </xsl:choose>
            </xsl:for-each>

            </xsl:otherwise>
        </xsl:choose>
    </xsl:with-param>
    <xsl:with-param name="param2">
        <a href="achievement-firsts.xml?r={@realm}"><xsl:value-of select="@realm"/></a>
    </xsl:with-param>
    <xsl:with-param name="param3">
      <xsl:choose>
       <xsl:when test="contains(@key,'.grand.master')">
        <xsl:variable name="typekey" select="concat(substring-after(substring-after(@key,'.'),'.'),'.type')"/>
        <!--<xsl:value-of select="translate(substring(@desc,50),'!?.','')"/>-->
        <xsl:value-of select="$feedtxt/strs/str[@id=$typekey]"/>
       </xsl:when>
       <xsl:when test="contains(@key,'.level.80.')">
        <xsl:variable name="classracekey" select="concat(substring(@key,22),'.name')"/>
        <xsl:value-of select="$feedtxt/strs/str[@id=$classracekey]"/>
        <!--<xsl:value-of select="translate(substring(@title,$lvl80pos),$ucletters,$lcletters)"/>-->
       </xsl:when>

       </xsl:choose>
    </xsl:with-param>

    </xsl:apply-templates>
    <xsl:if test="../@icon != 'regionFirst'"> - <span class="timestamp-news" style="display:none"><xsl:value-of select="../@posted"/></span></xsl:if>
    </div>
</xsl:template>

<xsl:template match="news/story">
	<div>
		<b style="color:white"><xsl:value-of select="@title"/></b> -
		<span class="timestamp-news" style="display:none"><xsl:value-of select="../@posted"/></span>
	</div>
	<div>
		<xsl:apply-templates />
	</div>
</xsl:template>


<!--related info start-->
<xsl:template name="related-info">
	<xsl:param name="src" />
<xsl:for-each select="document($src)/relatedinfo">
<div class="module-block-left">
<xsl:if test="$pageType='front'">
	<xsl:if test="$loc/strs/news/str">
<!--    <div class="armory-news" id="featured_module">
		<div class="module">
		<xsl:if test="$loc/strs/news/@title != ''"><h1><xsl:value-of select="$loc/strs/news/@title"/></h1></xsl:if>
				<div class="module-lite">
				<xsl:for-each select="$loc/strs/news/str">
					<div class="news_mod_entry"><xsl:apply-templates/></div>
				</xsl:for-each>
				</div>
		</div>
	</div>-->
	</xsl:if>
    <xsl:variable name="nfeed" select="document('/newsfeed.xml')"/>
    <xsl:variable name="feedtext" select="document(concat('/strings/',$lang,'/achievements_feed.xml'))"/>
    <xsl:variable name="level80pos" select="$feedtext/strs/str[@id='level80pos']"></xsl:variable>
	<xsl:if test="$nfeed/page/news">
        <div class="armory-firsts">
            <div class="module">
                <h1><xsl:value-of select="$loc/strs/common/str[@id='related-info.header.updates']"/></h1>
                    <div class="module-lite news_feed">

                    <xsl:for-each select="$nfeed/page/news">
                        <div class="news_upd">
                        <img src="images/news_{@icon}.png" class="p news_icon"/>

                        <xsl:apply-templates>
                            <xsl:with-param name="feedtxt" select="$feedtext"/>
                                <xsl:with-param name="lvl80pos" select="$level80pos"/>
                        </xsl:apply-templates>
                        </div>
                    </xsl:for-each>
                    </div>
            </div>
        </div>
		<script type="text/javascript">
			L10n.formatTimestamps("span.timestamp-news", <xsl:value-of select="$loc/strs/achievements/str[@id='date.format']"/>);
		</script>
	</xsl:if>
</xsl:if>

<div class="rinfo">
	<div class="module">
		<h1><xsl:value-of select="$loc/strs/common/str[@id='related-info.header.faq']"/></h1>
		<div class="faq">
			<div class="rlbox2">
			<div class="module-lite">
				<div class="faq-links">
					<ol>
						<xsl:for-each select="/relatedinfo/faqlist/faq">
							<xsl:variable name="positionNum"><xsl:number value="position()" format="1" /></xsl:variable>
							<li><a href="javascript:faqSwitch({$positionNum});" class="faq-off" id="faqlink{$positionNum}"><xsl:value-of select="@question"/></a></li>
						</xsl:for-each>
					</ol>
			 	</div>
			</div>
			<div class="module-lite" style="margin-top:5px">
				 <div class="speak-bubble">

					<xsl:for-each select="/relatedinfo/faqlist/faq">
						<div id="faq{position()}" style="display:none;">
							<h2><xsl:value-of select="@question"/></h2>
								<xsl:apply-templates/>
						</div>
					</xsl:for-each>

					<div class="faq-desc">
						<div id="faq-container"></div>
					</div>

				</div>
			 </div>
			 </div>
			</div><!--/end faq/-->

	</div>
</div>

</div>
<div class="module-block-right">
		<xsl:choose>
			<xsl:when test="$region='KR'">
				<div class="armory300">		
					<a href="http://www.worldofwarcraft.co.kr/myworld/friend2/index.do" target="_blank"><img src="/images/ads/box/KRad_raf2_300x250.jpg"/></a>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<div id="ad_300x250" class="armory300"></div>
			</xsl:otherwise>
		</xsl:choose>


<div class="rinfo">
	<div class="module">
		<h1><xsl:value-of select="$loc/strs/common/str[@id='related-info.header.related-links']"/></h1>
			<div id="noflash-message" class="related-links">
			<div class="module-lite">
				<div class="rlbox1">
					<p>
					<b class="noflash"><xsl:value-of select="$loc/strs/common/str[@id='related-info.header.noflash']"/></b><br/>
					<a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" target="_blank" class="noflash"><img src="images/{$lang}/getflash.gif" class="p" align="right"/></a>
					<xsl:value-of select="$loc/strs/common/str[@id='related-info.noflash']"/>
					</p>
				</div>
				</div>
			</div><!--/end related-links/-->

		<script type="text/javascript">
			if(!MM_FlashCanPlay)
				showNoflashMessage();
		</script>

			<div class="related-links">
				<div class="module-lite">
					<ul>
					<xsl:for-each select="/relatedinfo/relatedlinks/relatedlink">
						<xsl:choose>
							<xsl:when test="@type = 'hr'"><div class="hr"></div></xsl:when>
							<xsl:otherwise>
						<li>
							<xsl:attribute name="class">
							 <xsl:choose>
								<xsl:when test="@type = 'new'">relatedlinknew</xsl:when>
								<xsl:when test="@type = 'external'">external</xsl:when>
								<xsl:when test="@type = 'report'">report</xsl:when>
							 </xsl:choose>
							</xsl:attribute>
							<xsl:choose>
								<xsl:when test="@type = 'external'">
									<a href="{@url}" class="staticTip" onmouseover="setTipText('{$loc/strs/common/str[@id='external-link']}')" target="_blank"><xsl:value-of select="@name"/></a>
								</xsl:when>
								<xsl:when test="@type = 'report'">
									<a id="reportLink" class="staticTip" href="{@url}" onmouseover="setTipText('{$loc/strs/common/str[@id='report-error']}')" onmouseout="hideTip();"><xsl:value-of select="@name"/></a>
									<script type="text/javascript">
										var urlArmory = "<xsl:value-of select="$loc/strs/common/str[@id='url.armory']"/>";
										replaceLink(urlArmory);
									</script>
								</xsl:when>
								<xsl:when test="@type = 'translation'">
									<a id="reportLink" href="{@url}" class="staticTip" onmouseover="setTipText('{$loc/strs/common/str[@id='report-translation']}')" target="_blank"><xsl:value-of select="@name"/></a>
									<script type="text/javascript">
										replaceLink(urlArmory);
									</script>
								</xsl:when>
								<xsl:when test="@type = 'hr'"><em></em></xsl:when>
								<xsl:otherwise>
									<a href="{@url}"><xsl:value-of select="@name"/></a>
								</xsl:otherwise>
							</xsl:choose>
						</li>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
					</ul>
				</div>
		</div><!--/end related-links/-->
	</div>
</div>


</div>

<script type="text/javascript">
	faqSwitch(currentFaq);
</script>

</xsl:for-each>

</xsl:template>

</xsl:stylesheet>
