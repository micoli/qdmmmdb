<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:include href="config.xsl"/>
<xsl:include href="language.xsl"/>
<xsl:include href="template-common.xsl"/>
<xsl:include href="template-related-info.xsl"/>

<!--page start-->
<xsl:template match="@*|node()">
<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
</xsl:copy>
</xsl:template>

<xsl:template match="processing-instruction()"/>

<xsl:template name="head-content"/>

<xsl:template match="page">
<html>
<head>
	<link rel="shortcut icon" href="/favicon.ico" />
	<title><xsl:value-of select="$loc/strs/common/str[@id='the-wow-armory']"/></title>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="{$loc/strs/common/str[@id='meta-description']}" />
	<style type="text/css" media="screen, projection">
		@import "/css/master.css";
		@import "/css/<xsl:value-of select="$lang"/>/language.css";
		@import "/shared/global/menu/topnav/topnav.css";
		<xsl:if test="not(/page/pageIndex)">
		@import "/css/int.css";
		   </xsl:if>
	</style>

	<script type="text/javascript" src="/shared/global/third-party/jquery/jquery.js"></script>
	<script type="text/javascript" src="/shared/global/third-party/sarissa/0.9.9.3/sarissa.js"></script>
	<script type="text/javascript" src="/js/common.js"></script>
	<script type="text/javascript" src="/js/tooltip_compact.js"></script>
	<script type="text/javascript" src="/js/armory.js"></script>
	<script type="text/javascript" src="/js/character/bookmark.js"></script>

	<!-- After JavaScript, before browser-specific CSS addition -->
	<xsl:call-template name="head-content"/>

	<script type="text/javascript">
		//browser detection

		if($.browser.msie){
			if($.browser.version == "7.0")		addStylesheet('/css/browser/ie7.css');
			if($.browser.version == "6.0")		addStylesheet('/css/browser/ie.css');
		}else if($.browser.mozilla){
			if(parseFloat($.browser.version) &lt;= 1.9)	addStylesheet('/css/browser/firefox2.css');
		}else if($.browser.opera)				addStylesheet('/css/browser/opera.css');
		else if($.browser.safari)				addStylesheet('/css/browser/safari.css');

		//set global login var
		var isLoggedIn = ("<xsl:value-of select="document('/login-status.xml')/page/loginStatus/@username"/>" != '');
		var bookmarkToolTip = "<xsl:value-of select="$loc/strs/common/str[@id='user.bookmark.tooltip']"/>";

		var isNotHomePage = "<xsl:value-of select="not(/page/pageIndex)" />";
		var globalSearch = "<xsl:value-of select = "@globalSearch" />";
		var theLang = "<xsl:value-of select="/page/@lang"/>";
		var searchQueryValue = '';

		if (getcookie2("armory.cookieSearch"))
			searchQueryValue = getcookie2("armory.cookieSearch");
		else
			searchQueryValue = '<xsl:value-of select="$loc/strs/common/str[@id='search-armory']"/>';

		<xsl:choose>
			<xsl:when test="armorySearch/searchResults">
				searchQueryValue = "<xsl:value-of select="armorySearch/searchResults/@searchText" />";
				setcookie("armory.cookieSearch", searchQueryValue);
			</xsl:when>
		</xsl:choose>

		if(region != "KR" &amp;&amp; region != "TW"){
			searchQueryValue = unescape(searchQueryValue);
		}
		setcookie("cookieLangId", theLang); // fixed a bug (when the page used a function 'document(url)')

		/* <![CDATA[ */
		$(document).ready(function() {

			//initialize the armory!
			initializeArmory();

		});
		/* ]]>*/
	</script>
</head>

<body>
	<div style="display:none;">
		<xsl:choose>
			<xsl:when test="$lang='en_us'">
				<xsl:call-template name="clickTracker">
					<xsl:with-param name="iframeSource" select="'http://view.atdmt.com/NTB/iview/d465f9cb65954aba85e355aa6d5553be/direct;wi.1;hi.1/01?click='"/>
					<xsl:with-param name="docWrite" select="'http://clk.atdmt.com/NTB/go/d465f9cb65954aba85e355aa6d5553be/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="docImgSrc" select="'http://view.atdmt.com/NTB/view/d465f9cb65954aba85e355aa6d5553be/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="href" select="'http://clk.atdmt.com/NTB/go/d465f9cb65954aba85e355aa6d5553be/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="imgSrc" select="'http://view.atdmt.com/NTB/view/d465f9cb65954aba85e355aa6d5553be/direct;wi.1;hi.1/01/'"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$lang='ko_kr'">
				<xsl:call-template name="clickTracker">
					<xsl:with-param name="iframeSource" select="'http://view.atdmt.com/NTB/iview/f31ae4a6663b4f77a72e227def1d6cef/direct;wi.1;hi.1/01?click='"/>
					<xsl:with-param name="docWrite" select="'http://clk.atdmt.com/NTB/go/f31ae4a6663b4f77a72e227def1d6cef/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="docImgSrc" select="'http://view.atdmt.com/NTB/view/f31ae4a6663b4f77a72e227def1d6cef/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="href" select="'http://clk.atdmt.com/NTB/go/f31ae4a6663b4f77a72e227def1d6cef/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="imgSrc" select="'http://view.atdmt.com/NTB/view/f31ae4a6663b4f77a72e227def1d6cef/direct;wi.1;hi.1/01/'"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$lang='zh_tw'">
				<xsl:call-template name="clickTracker">
					<xsl:with-param name="iframeSource" select="'http://view.atdmt.com/NTB/iview/ea9f9b408703468abfe831ec44bf96c4/direct;wi.1;hi.1/01?click='"/>
					<xsl:with-param name="docWrite" select="'http://clk.atdmt.com/NTB/go/ea9f9b408703468abfe831ec44bf96c4/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="docImgSrc" select="'http://view.atdmt.com/NTB/view/ea9f9b408703468abfe831ec44bf96c4/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="href" select="'http://clk.atdmt.com/NTB/go/ea9f9b408703468abfe831ec44bf96c4/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="imgSrc" select="'http://view.atdmt.com/NTB/view/ea9f9b408703468abfe831ec44bf96c4/direct;wi.1;hi.1/01/'"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$lang='de_de' or $lang='es_es' or $lang='fr_fr' or $lang='en_gb' or $lang='ru_ru'">
				<xsl:call-template name="clickTracker">
					<xsl:with-param name="iframeSource" select="'http://view.atdmt.com/NTB/iview/0dbf58ffa79e4e6d8cdb1026f3319531/direct;wi.1;hi.1/01?click='"/>
					<xsl:with-param name="docWrite" select="'http://clk.atdmt.com/NTB/go/0dbf58ffa79e4e6d8cdb1026f3319531/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="docImgSrc" select="'http://view.atdmt.com/NTB/view/0dbf58ffa79e4e6d8cdb1026f3319531/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="href" select="'http://clk.atdmt.com/NTB/go/0dbf58ffa79e4e6d8cdb1026f3319531/direct;wi.1;hi.1/01/'"/>
					<xsl:with-param name="imgSrc" select="'http://view.atdmt.com/NTB/view/0dbf58ffa79e4e6d8cdb1026f3319531/direct;wi.1;hi.1/01/'"/>
				</xsl:call-template>
			</xsl:when>
		</xsl:choose>
	</div>

	<form id="historyStorageForm" method="GET">
		<textarea id="historyStorageField" name="historyStorageField"></textarea>
	</form>

	<script type="text/javascript" src="/js/dhtmlHistory.js"></script>
	<script type="text/javascript" src="/js/{$lang}/strings.js"></script>
	<script type="text/javascript">global_nav_lang = '<xsl:value-of select="/page/@lang"/>'</script>
	<div id="shared_topnav" class="tn_armory"><script src="/shared/global/menu/topnav/buildtopnav.js"></script></div>
	<xsl:if test = "$lang != 'en_us'">
		<script type="text/javascript" src="/js/{$lang}/globalsearch.js"></script>
	</xsl:if>


	<xsl:variable name="textArmory" select="$loc/strs/common/str[@id='menu.armory']" />
	<xsl:variable name="textArenaTeams" select="$loc/strs/common/str[@id='menu.arenateams']" />
	<xsl:variable name="textCharacters" select="$loc/strs/common/str[@id='menu.characters']" />
	<xsl:variable name="textGuilds" select="$loc/strs/common/str[@id='menu.guilds']" />
	<xsl:variable name="textItems" select="$loc/strs/common/str[@id='menu.items']" />
	<!-- containers-->
	<div class="outer-container">
	  <div class="inner-container">
		<xsl:choose>
			<xsl:when test="/page/pageIndex"><!-- *HOME PAGE TEMPLATE -->
				<div id = "replaceMain">
					<xsl:apply-templates />
				</div>
			</xsl:when>
			<xsl:otherwise>									<!-- *INTERIOR PAGES TEMPLATE -->
				<div class="int-top">
					<div class="logo">
						<a href="/"><span><xsl:value-of select="$loc/strs/common/str[@id='the-wow-armory']"/></span></a>
						<!-- TODO replace url above with this later:{$loc/strs/common/str[@id='url.armory']}-->
					</div>
					<div class="adbox">
						<div class="ad-container">	
							<xsl:choose>
								<xsl:when test="$region='KR'">
									<a href="http://www.worldofwarcraft.co.kr/myworld/friend2/index.do" target="_blank"><img src="/images/ads/leader/KRad_raf2_728x90.jpg"/></a>
								</xsl:when>
								<xsl:otherwise>
									<div id="ad_728x90"></div>
								</xsl:otherwise>
							</xsl:choose>
						</div>
                        </div>
					</div>
							<div class="int">
								<div class="search-bar">
									<div class="module">
										<div class="search-container">
											<xsl:call-template name="searchInput"/>										</div>
										<div class="login-container">
											<xsl:call-template name="userLogin"/>										</div>
									</div>
								</div>
									<div class="data-container">
										<div class="data-shadow-top"><xsl:comment/></div>
											<div class="data-shadow-sides">
												<div class="parch-int">
													<div class="parch-bot">
														<div id="replaceMain">
															<xsl:apply-templates />
														</div>
													</div>
												</div>
											</div>
											<div class="data-shadow-bot"><xsl:comment/></div>
									</div>
									<div class="page-bot"></div>
									<xsl:call-template name="relatedContainer"/>
							</div>
			</xsl:otherwise>
		</xsl:choose>
	</div>
		<xsl:call-template name="footer"/>
	</div>

	<!-- NEW TOOLTIP -->
		<div class="globalToolTip">
			<table>
				<tr>
					<td class="tl"><em></em></td><td class="tm"></td><td class="tr"><em></em></td>
				</tr>
				<tr>
					<td class="ml"></td><td class="mm" valign="top"><div id="globalToolTip_text">.</div></td><td class="mr"></td>
				</tr>
				<tr>
					<td class="bl"><em></em></td><td class="bm"></td><td class="br"><em></em></td>
				</tr>
			</table>
		</div>

<!--	<div class="globalToolTip"><div id="globalToolTip_text"></div></div>-->

	<!--/tool tips/-->
	<div id="tooltipcontainer" onmouseout="hideTip();" class="tooltip">
		<div id="tool1container">
			<table>
				<tr><td class="tl"></td><td class="t"></td><td class="tr"></td></tr>
				<tr><td class="l"><q></q></td><td class="bg" valign="top"><div id="toolBox">-</div></td><td class="r"><q></q></td></tr>
				<tr><td class="bl"></td><td class="b"></td><td class="br"></td></tr>
			</table>
		</div>

		<table style="float:left; display:none; margin-top: 10px;" id="tool2container">
			<tr><td class="tl"></td><td class="t"></td><td class="tr"></td></tr>
			<tr><td class="l"><q></q></td><td class="bg" valign="top"><div id="toolBox_two">-</div></td><td class="r"><q></q></td></tr>
			<tr><td class="bl"></td><td class="b"></td><td class="br"></td></tr>
		</table>

		<table style="float:left; display:none; margin-top: 10px;" id="tool3container">
			<tr><td class="tl"></td><td class="t"></td><td class="tr"></td></tr>
			<tr><td class="l"><q></q></td><td class="bg" valign="top"><div id="toolBox_three">-</div></td><td class="r"><q></q></td></tr>
			<tr><td class="bl"></td><td class="b"></td><td class="br"></td></tr>
		</table>
	</div>

	<div id="output"></div>

	<script type="text/javascript">
		var elemt1c = document.getElementById("tool1container");
		var elemttc = document.getElementById("tooltipcontainer");
		var elemt2c = document.getElementById("tool2container");
		var elemt3c = document.getElementById("tool3container");
		var elemtb1 = document.getElementById("toolBox");
		var elemtb2 = document.getElementById("toolBox_two");
		var elemtb3 = document.getElementById("toolBox_three");
		var elemDoc = document.documentElement;
	</script>

	<script type="text/javascript" src="/js/ajaxtooltip.js"></script>
	<script type="text/javascript" src="https://ssl.google-analytics.com/ga.js"></script>
	<script type="text/javascript">
		var pageTracker = _gat._getTracker("UA-544112-17");
		pageTracker._setDomainName("wowarmory.com");
		pageTracker._initData();
		pageTracker._trackPageview();
	</script>

</body>
</html>
</xsl:template>


<xsl:template name="searchInput">
<xsl:variable name="textArmory" select="$loc/strs/common/str[@id='menu.armory']" />
<xsl:variable name="textArenaTeams" select="$loc/strs/common/str[@id='menu.arenateams']" />
<xsl:variable name="textCharacters" select="$loc/strs/common/str[@id='menu.characters']" />
<xsl:variable name="textGuilds" select="$loc/strs/common/str[@id='menu.guilds']" />
<xsl:variable name="textItems" select="$loc/strs/common/str[@id='menu.items']" />
<div class="search-module">
	<em class="search-icon"></em>
	<form name="formSearch" action="/search.xml" method="get" onSubmit="javascript: return menuCheckLength(document.formSearch);">
		<input id="armorySearch" type="text" name="searchQuery" value="" size="16" maxlength="72" onfocus="resetSearch();" />
		<a href="javascript:void(0);" class="submit" onclick="javascript: return menuCheckLength(document.formSearch);"><span><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.search']"/></span></a>
		<div id="errorSearchType"></div>
		<div onmouseover="javascript: this.innerHTML = '';" id="formSearch_errorSearchLength"></div>
		<input type="hidden" name="searchType" value="all" />
	</form>
	<div class="navigation">
		<!-- search bar drop down menus -->

			<xsl:for-each select="$loc/strs/menu">
				<xsl:apply-templates/>
			</xsl:for-each>
	</div>
</div>
</xsl:template>

<xsl:template name="userLogin">
<xsl:variable name="login-status" select="document('/login-status.xml')" />
<xsl:variable name="username" select="$login-status/page/loginStatus/@username" />

<!-- Log off string -->

<xsl:if test="string-length($username) != 0">
	<script type="text/javascript">
		<![CDATA[
		function loadCalendarAlerts(data) {
			if(!data.invites || !data.invites.length)
				return;

			$("#pendingInvitesNotification").show();

			var bookmarks = $("#userSelect .js-bookmark-characters");
			var names = bookmarks.find(".js-character-name"); // no
			var realms = bookmarks.find(".js-character-realm");
			var inviteNodes = bookmarks.find(".user-alerts");

			for(var j = 0, invite, invites = data.invites; invite = invites[j]; j++) {
				for(var i = 0; i < names.length; i++) {
					if($(names[i]).text() == invite.character && $(realms[i]).text() == invite.realm) {
						$(inviteNodes[i]).show().text(invite.invites);
					}
				}
			}
		}

		$(document).ready(function() {
			loadScript('/vault/calendar/alerts-user.json?callback=loadCalendarAlerts', 'jsonAlerts');
		});
		]]>
	</script>
</xsl:if>
<div class="module">
<xsl:if test="not(/page/login)">
	<xsl:choose>
		<xsl:when test="string-length($username) != 0">
		<xsl:variable name="user" select="document('/vault/character-select.xml?sel=2')" />
		<xsl:variable name="profile" select="$user/page/characters/character" />
		<xsl:variable name="pinned" select="$profile[@selected=1]" />
		<xsl:variable name="pinClassId" select="$pinned/@classId"/>
		<xsl:variable name="pinRaceId" select="$pinned/@raceId"/>
		<xsl:variable name="pinGenderId" select="$pinned/@genderId"/>
		<xsl:variable name="bookmark" select="document('/vault/bookmarks.xml')/page/characters/character" />
		<xsl:variable name="bookmarkCurr" select="document('/vault/bookmarks.xml')/page/characters/@count" />
		<xsl:variable name="bookmarkMax" select="document('/vault/bookmarks.xml')/page/characters/@max" />
		<xsl:variable name="txtClass" select="$loc/strs/classes/str[@id=concat('armory.classes.class.', $pinClassId,'.', $pinGenderId)]" />
		<xsl:variable name="txtLevel" select="$loc/strs/character/str[@id='character-level-sheet']" />
		<xsl:variable name="txtRace" select="$loc/strs/races/str[@id=concat('armory.races.race.', $pinRaceId,'.', $pinGenderId)]" />
			<div class="user-options">
				<xsl:if test="string-length($username) != 0">
					<xsl:variable name="alts">
						<xsl:for-each select="$profile[@selected = 1 or @selected = 2]"><xsl:value-of select="@name" />_<xsl:value-of select="@realm" /><xsl:if test="position() != last()">,</xsl:if></xsl:for-each>
					</xsl:variable>
					<div id="pendingInvitesNotification" class="toast staticTip" style="display:none;"
						onMouseOver="setTipText('{$loc/strs/login/str[@id='armory.login.invites.pending']}');"></div>
					<a href="javascript:void(0)" class="bookmark-user"></a>
					<div class="menu-position">
						<div class="user-menu" style="display:none" id="userSelect">
							<div class="menu-dropshadow">
								<div class="user-menu-contents">
									<div class="user-menu-section myChar">
										<h2><xsl:value-of select="$loc/strs/common/str[@id='user.change.character']"/></h2>
										<div class="char-select"><a href="/vault/character-select.xml">[<xsl:value-of select="$loc/strs/common/str[@id='user.manage.settings']"/>]</a></div>
									</div>
									<xsl:choose>
										<xsl:when test="$profile/@selected='1'">
											<xsl:for-each select="$profile[@selected &lt; 3]">
											<xsl:sort select="@selected"/>
												<div class="user-line-item js-bookmark-characters">
													<xsl:call-template name="character-list">
														<xsl:with-param name="alerts" select="1"/>
                                                        <xsl:with-param name="charLink" select="current()/@url"/>
													</xsl:call-template>
												</div>
											</xsl:for-each>
										</xsl:when>
										<xsl:otherwise>
											<span class="nochar"><xsl:value-of select="$loc/strs/common/str[@id='user.no.character']"/></span>
										</xsl:otherwise>
									</xsl:choose>
									<div class="user-menu-section myBooks">
										<!-- num bookmarks left / paging -->
										<div class="bookmarknumber staticTip" onmouseover="setTipText(bookmarkToolTip);">
											<span id="bookmarksRemaining"><xsl:value-of select="$bookmarkCurr"/></span>/<xsl:value-of select="$bookmarkMax"/>
										</div>
										<!-- my bookmarks title-->
										<h2><xsl:value-of select="$loc/strs/common/str[@id='user.change.bookmarks']"/></h2>
									</div>
									<xsl:choose>
										<xsl:when test="$bookmark">
											<xsl:for-each select="$bookmark">
												<xsl:sort select="@name"/>
												<div class="user-line-item bmlist">
													<xsl:call-template name="character-list">
														<xsl:with-param name="friends" select="1"/>
                                                        <xsl:with-param name="charLink" select="current()/@url"/>
													</xsl:call-template>
												</div>
											</xsl:for-each>
										</xsl:when>
										<xsl:otherwise>
											<div class="user-line-item nobookmark" style="height: auto;">
												<strong><xsl:value-of select="$loc/strs/login/str[@id='armory.login.bookmark.nobookmark']"/></strong>
												<p><xsl:value-of select="$loc/strs/login/str[@id='armory.login.bookmark.nobookmark.desc']"/></p>
											</div>
										</xsl:otherwise>
									</xsl:choose>
								</div>
							</div>
						</div>
					</div>
				</xsl:if>
				<div class="clear"><xsl:comment /></div>
			</div>
			<div class="usermod">
				<xsl:choose>
					<!-- no characters -->
					<xsl:when test="string-length($txtClass) = 0">
						<span><xsl:value-of select="$loc/strs/login/str[@id='armory.login.nochars']"/></span><br />
						<a href="/index.xml?logout=1"><xsl:value-of select="$loc/strs/login/str[@id='armory.login.logout']" /></a>
					</xsl:when>
					<xsl:otherwise>
					<span><xsl:value-of select="$loc/strs/login/str[@id='armory.login.logged']"/></span><br/>
						<a href="/character-sheet.xml?{$pinned/@url}" class="userName">
							<em class="classId{$pinned/@classId} staticTip" onmouseover="setTipText('{$txtClass}')"></em>
						<xsl:value-of select="$pinned/@name"/></a> |
						<a href="/index.xml?logout=1"><xsl:value-of select="$loc/strs/login/str[@id='armory.login.logout']" /></a>
					</xsl:otherwise>

				</xsl:choose>

			</div>
		</xsl:when>
		<xsl:otherwise>
		<a id="loginLinkRedirect" class="loginLink" href="{/page/@requestUrl}?login=1"><div class="userKey"></div><xsl:value-of select="$loc/strs/login/str[@id='armory.login.login']" /></a>
		</xsl:otherwise>
	</xsl:choose>
</xsl:if>
<div class="clear"><xsl:comment /></div>
</div>

</xsl:template>

<xsl:template name="character-list">
	<xsl:param name="alerts" select="0"/>
	<xsl:param name="friends" select="0"/>
	<xsl:param name="charLink" />
    
    
	<xsl:variable name="subClass" select="@classId"/>
	<xsl:variable name="pinClassId" select="@classId"/>
	<xsl:variable name="pinRaceId" select="@raceId"/>
	<xsl:variable name="pinGenderId" select="@genderId"/>
	<xsl:variable name="bookmark" select="document('/bookmarks-static.xml')/page/characters/character" />
	<xsl:variable name="txtClass" select="$loc/strs/classes/str[@id=concat('armory.classes.class.', $pinClassId,'.', $pinGenderId)]" />
	<xsl:variable name="txtLevel" select="$loc/strs/character/str[@id='character-level']" />
	<xsl:variable name="txtRace" select="$loc/strs/races/str[@id=concat('armory.races.race.', $pinRaceId,'.', $pinGenderId)]" />

	<div class="character-item">
		<a href="/character-sheet.xml?{$charLink}" class="charName" style="white-space:nowrap; overflow:hidden;">
				<em class="classId{@classId} staticTip" onmouseover="setTipText('{$txtClass}')"></em>
				<xsl:if test="@selected='1'"><em class="character-main"></em></xsl:if>
				<div class="bookmarkwrap"><div><span class="js-character-name"><xsl:value-of select="@name"/></span><span style="padding:0 3px;">-</span><span><span class="js-character-realm"><xsl:value-of select="@realm"/></span></span></div></div>
		</a>
		<xsl:choose>
			<xsl:when test="$friends">
				<a href="javascript:void(0)" class="bookmark-remove staticTip"
						onmouseover="setTipText('{$loc/strs/login/str[@id='armory.login.bookmark.remove']}')">&#160;</a>
			</xsl:when>
			<xsl:otherwise>
				<a href="/vault/character-calendar.xml?{$charLink}" class="user-alerts staticTip" onmouseover="setTipText('{$loc/strs/login/str[@id='armory.login.calendar.pending']}')" style="display:none">0</a>
			</xsl:otherwise>
		</xsl:choose>
		<p>
			<xsl:call-template name="stringorder">
				<xsl:with-param name="datainsert2" select="@level"/>
				<xsl:with-param name="datainsert1" select="$txtLevel"/>
				<xsl:with-param name="orderid" select="'armory.order.character-string'"/>
			</xsl:call-template>
		</p>
		<xsl:if test="@achPoints">
			<div class="character-achievement staticTip" onMouseOver="setTipText('{$loc/strs/login/str[@id='armory.login.achievements']}');">
				<a href="/character-achievements.xml?{$charLink}"><xsl:value-of select="@achPoints"/></a>
			</div>
		</xsl:if>
	</div>
</xsl:template>

<xsl:template name="footer">

<div id="languageFooter" class="language">
	<div class="module">
	<em><xsl:value-of select="$loc/strs/common/str[@id='labels.selectlanguagecolon']"/></em>
	<xsl:for-each select="$loc/strs/language/str">
		<a href="{@url}">
		<xsl:attribute name="class">
			<xsl:choose>
				<xsl:when test="starts-with($lang,@id)">langLink select</xsl:when>
				<xsl:otherwise>langLink</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<xsl:value-of select="@name"/></a><span>|</span>
	</xsl:for-each>
<br/><em><xsl:value-of select="$loc/strs/common/str[@id='labels.selectregioncolon']"/></em>
	<xsl:for-each select="$loc/strs/region/str">
		<a href="{@url}">
		<xsl:attribute name="class">
			<xsl:if test="starts-with($region,@id)">select</xsl:if>
		</xsl:attribute>
		<xsl:value-of select="@name"/></a><span>|</span>
	</xsl:for-each>
	</div>
</div>

<div class="footer">
	<a href="{$loc/strs/common/str[@id='url.blizzard']}" class="blizzard"></a>
	<p>
		<a href="{$loc/strs/common/str[@id='url.privacy']}"><xsl:value-of select="$loc/strs/common/str[@id='labels.privacy']"/></a><br/>
		<a href="{$loc/strs/common/str[@id='url.legalfaq']}"><xsl:value-of select="$loc/strs/common/str[@id='labels.copyright']"/></a>
	</p>
	<xsl:if test="$region = 'US'">
		<div id="legalicon-container" style="display:block; "><a href="{$loc/strs/esrb/str[@id='privacyurl']}" target="_blank" class="legalicon3"/><a href="{$loc/strs/esrb/str[@id='ratingsurl']}" target="_blank" class="legalicon"/><a href="{$loc/strs/esrb/str[@id='ratingsurl']}" target="_blank" class="legalicon2"/></div>
	</xsl:if>
	<div class="clear"><xsl:comment/></div>
</div>
</xsl:template>


<xsl:template name="clickTracker">
<xsl:param name="iframeSource"/>
<xsl:param name="docWrite"/>
<xsl:param name="docImgSrc"/>
<xsl:param name="href"/>
<xsl:param name="imgSrc"/>
		<iframe src="{$iframeSource}" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" allowtransparency="true" width="1" height="1">
			<script language="javascript" type="text/javascript">
				document.write('<a href="{$docWrite}" target="_blank"><img src="{$docImgSrc}"/></a>');
			</script>
			<noscript>
				<a href="{$href}" target="_blank"><img border="0" src="{$imgSrc}" /></a>
			</noscript>
		</iframe>
</xsl:template>

<!-- Localization Ordering Template -->
<xsl:template name="stringorder">
	<xsl:param name="orderid"/>
	<xsl:param name="datainsert1"/>
	<xsl:param name="datainsert2"/>
	<xsl:param name="datainsert3"/>
	<xsl:param name="datainsert4"/>
	<xsl:param name="ResultsInputPaging"/>
	<xsl:param name="selectTeamType"/>

	<xsl:for-each select="$loc/strs/order[@id=$orderid]/str">
	<xsl:variable name="nodecount" select="count($loc/strs/order[@id=$orderid]/str)"/>
	<xsl:variable name="positionNum" select="position()"/>
		<span>
			<xsl:attribute name="class">
			<xsl:choose>
				<xsl:when test="@format = 'italic'">italic</xsl:when>
				<xsl:when test="@format = 'bold'">bold</xsl:when>
			</xsl:choose>
			</xsl:attribute>

			<xsl:choose>
				<xsl:when test="@id">
					<xsl:variable name="comparestring" select="@id"/>
					<xsl:value-of select="../../str[@id=$comparestring]"/>
				</xsl:when>
				<xsl:when test="@select='datainsert1'">
					<xsl:value-of select="$datainsert1"/>
				</xsl:when>
				<xsl:when test="@select='datainsert2'">
					<xsl:value-of select="$datainsert2"/>
				</xsl:when>
				<xsl:when test="@select='datainsert3'">
					<xsl:value-of select="$datainsert3"/>
				</xsl:when>
				<xsl:when test="@select='datainsert4'">
					<xsl:value-of select="$datainsert4"/>
				</xsl:when>
				<xsl:when test="@select='ResultsInputPaging'">
					<input type="text" value="{$datainsert2}" onkeypress="{{return {$datainsert3}.pageSearchOnKeyPress(event)}}" size="3" class="pagesInput" onfocus="this.value=''" onblur="if (this.value=='') this.value='{$datainsert2}'" />
				</xsl:when>
				<xsl:when test="@select='selectTeamType'">
					<a href="javascript:selectTeamTypePageInstance.goToNextPage({$selectTeamType})" title="{$datainsert1}"><span><xsl:value-of select="$datainsert1"/></span></a>
				</xsl:when>
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="$positionNum&lt;$nodecount and @space">&#160;</xsl:when>
			</xsl:choose>
		</span>
	</xsl:for-each>

</xsl:template>

<!--errorpage start-->
<xsl:template name="errorSection">
	<div class="profile-wrapper">
		<blockquote><b class="iguilds"><h4><a href="/guild-search.xml"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.armory']"/></a></h4><h3>
		<xsl:choose>
			<xsl:when test="/page/characterInfo/@errCode and /page/characterInfo/@errCode='forbidden'">
				<xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.accessdenied']"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.errorencountered']"/>
			</xsl:otherwise>
		</xsl:choose>
		</h3></b></blockquote>


		<xsl:choose>
			<xsl:when test="/page/characterInfo/@errCode and /page/characterInfo/@errCode='forbidden'">
				<div class="filtercontainer" style="margin:50px auto 0;padding:6px; width:45%">
				<div class="bankcontentsfiltercontainer" style="width: 100%; text-align: center;">
					<div style="padding: 0pt 10px 10px;">
						<div class="guildloginmsg" style="padding-left:10px">
							<div class="guilderrortitle"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.accessdenied']"/></div>
							<xsl:apply-templates select="$loc/strs/unsorted/str[@id='armory.labels.protectedcontent']"/>
						</div>
					</div>
				</div>
				<div class="clearfilterboxsm"></div>
				</div>
				<div class="bottomshadowsm" style="height:50px"></div>
			</xsl:when>
			<xsl:when test="/page/characterInfo/@errCode and /page/characterInfo/@errCode='noCharacter'">
				<!-- no character -->
				<div class="filtercontainer" style="margin:50px auto;padding:6px; width:80%">
					<div class="bankcontentsfiltercontainer" style="width:100%; text-align: center;">
						<div style="padding:10px;">
							<div class="guildloginmsg" style="padding-left:10px">
								<div class="guilderrortitle"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.error.filenotfound']"/></div>
								<xsl:apply-templates select="$loc/strs/unsorted/str[@id='armory.labels.nocharacter']"/>
							</div>
						</div>
					</div>
					<div class="clearfilterboxsm"></div>
				</div>
			</xsl:when>
			<xsl:when test="count(/page/guildInfo/*) = 0">
				<!-- no guild -->
				<div class="filtercontainer" style="margin:50px auto;padding:6px; width:80%">
					<div class="bankcontentsfiltercontainer" style="width:100%; text-align: center;">
						<div style="padding:10px;">
							<div class="guildloginmsg" style="padding-left:10px">
								<div class="guilderrortitle"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.error.filenotfound']"/></div>
								<xsl:apply-templates select="$loc/strs/unsorted/str[@id='armory.labels.noguild']"/>
							</div>
						</div>
					</div>
					<div class="clearfilterboxsm"></div>
				</div>
			</xsl:when>
			<xsl:otherwise>
			<div class="error-message">
					<h3><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.erroroccured']"/></h3>
			</div>
			</xsl:otherwise>
		</xsl:choose>
	</div>
</xsl:template>


<xsl:template name="errorPageGeneral">
<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->

<div id="dataElement">
<div class="parchment-top">
<div class="parchment-content">
<div class="mini-search-start-state" id="results-side-switch">
<div class="list">
<div class="full-list">
<div class="info-pane">

<div class="bigbaderror"><xsl:call-template name="errorSection" /></div>

</div>
<!--/end tip/-->
		</div>
<!--/end full-list/-->
	</div><!--/end list/-->
</div><!--/end results-side-expanded/-->
</div>
</div>
</div>
</xsl:template>

<xsl:template name="unavailable">

<br /><br />
<div class="generic-content">
<div class="related-links">
	<table>
		<tr>
			<td class="s-top-left" />
			<td class="s-top" />
			<td class="s-top-right" />
		</tr>
		<tr>
			<td class="s-left"><div class="shim stable" /></td>
			<td class="s-bg" style="padding: 20px;">
<div style="width: 100%; text-align: left; font-size:12px;">
<h5><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.character.refreshheader']"/></h5>
<br/>
<xsl:value-of select="$loc/strs/characterSheet/str[@id='armory.character.refresh']"/>
</div>

			</td>
			<td class="s-right"><div class="shim stable" /></td>
		</tr>
		<tr>
			<td class="s-bot-left" />
			<td class="s-bot" />
			<td class="s-bot-right" />
		</tr>
	</table>
<strong><a style="width: 300px;" href = "/index.xml"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.label.search.return']"/></a></strong>
</div><!--/end related-links/-->
</div>
</xsl:template>
<!--errorpage end-->

<xsl:template name="serverBusy">
<div class="generic-content">
<div class="related-links">
	<table>
		<tr>
			<td class="s-top-left" />
			<td class="s-top" />
			<td class="s-top-right" />
		</tr>
		<tr>
			<td class="s-left"><div class="shim stable" /></td>
			<td class="s-bg" style="padding: 20px;">
<div style="width: 100%; text-align: left; font-size:12px;">
<h5><xsl:value-of select="$loc/strs/serverBusy/str[@id='title']"/></h5>
<br/>
<xsl:value-of select="$loc/strs/serverBusy/str[@id='content']"/>
</div>

			</td>
			<td class="s-right"><div class="shim stable" /></td>
		</tr>
		<tr>
			<td class="s-bot-left" />
			<td class="s-bot" />
			<td class="s-bot-right" />
		</tr>
	</table>
<strong style = "float: right;"><a style="width: 300px;" href = "/index.xml"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.label.search.return']"/></a></strong>
<strong><a style="width: 300px;" href = "#" onclick = "javaScript:location.reload(true); return false;" ><xsl:value-of select="$loc/strs/serverBusy/str[@id='refreshpage']"/></a></strong>

</div><!--/end related-links/-->
</div>
</xsl:template>


<!--layout-utils start-->
<xsl:template name="printItemDropChance">
	<xsl:param name="dropRate"/>
	<xsl:choose>
		<xsl:when test="$dropRate=0"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.0']"/></xsl:when>
		<xsl:when test="$dropRate=1"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.1']"/></xsl:when>
		<xsl:when test="$dropRate=2"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.2']"/></xsl:when>
		<xsl:when test="$dropRate=3"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.3']"/></xsl:when>
		<xsl:when test="$dropRate=4"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.4']"/></xsl:when>
		<xsl:when test="$dropRate=5"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.5']"/></xsl:when>
		<xsl:when test="$dropRate=6"><xsl:value-of select="$loc/strs/itemInfo/str[@id='armory.item-info.drop-rate.6']"/></xsl:when>
	</xsl:choose>
</xsl:template>


<xsl:template name="nameAbbreviator">
<xsl:param name="name" />
<xsl:param name="maxLength">14</xsl:param>
<xsl:param name="extraText" />
<xsl:param name="link" />
<xsl:choose>
	 <xsl:when test="not(normalize-space($name))">
		 &#160;
	 </xsl:when>
	 <xsl:when test="string-length($name) &gt; $maxLength">
	   <a onmouseover="showTip('{$name}&lt;br /&gt;{$extraText}')" onmouseout="hideTip()">
		 <xsl:if test="$link">
		   <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
		 </xsl:if>
		 <xsl:value-of select="substring($name,0,$maxLength )"/>...
	   </a>
	 </xsl:when>
	 <xsl:otherwise>
	   <a>
		 <xsl:if test="$link">
		   <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
		 </xsl:if>
		 <xsl:if test="$extraText">
		   <xsl:attribute name="onmouseover">showTip('<xsl:value-of select="$extraText"/>')</xsl:attribute>
		   <xsl:attribute name="onmouseout">hideTip()</xsl:attribute>
		 </xsl:if>
		 <xsl:value-of select="$name"/>
	   </a>
	 </xsl:otherwise>
</xsl:choose>
</xsl:template>


<!-- number formatting templates and other stuff -->
<xsl:decimal-format name="eu_numberFormat" decimal-separator="," grouping-separator="." />
<xsl:decimal-format name="us_numberFormat" decimal-separator="." grouping-separator="," />
<!-- TODO: add a decimal-format definition for ko -->

<xsl:template name="formatNumber">
<xsl:param name="value"></xsl:param>
<xsl:choose>
	<xsl:when test="$value = 0">0</xsl:when>
	<xsl:otherwise>
	  <xsl:choose>
		<xsl:when test="$lang = 'en_us'">
		  <xsl:value-of select="format-number($value, '#,###', 'us_numberFormat')"/>
		</xsl:when>
		<xsl:when test="$lang = 'de_de' or $lang = 'en_gb' or $lang = 'es_es' or $lang = 'fr_fr' or $lang = 'ru_ru'">
		  <xsl:value-of select="format-number($value, '#.###', 'eu_numberFormat')"/>
		</xsl:when>
		<xsl:otherwise>
		  <xsl:value-of select="$value"/>
		</xsl:otherwise>
	  </xsl:choose>
	</xsl:otherwise>
</xsl:choose>
</xsl:template>


<!-- paging templates -->
<xsl:template name="pageLink">
<xsl:param name="linkPageNumber">10</xsl:param>
<xsl:param name="currentPageNumber">10</xsl:param>
<xsl:param name="objectName"></xsl:param>
<xsl:choose>
	  <xsl:when test="$linkPageNumber = $currentPageNumber">
		 <li><a class="sel"><xsl:value-of select="$linkPageNumber"/></a></li>
	  </xsl:when>
	  <xsl:otherwise>
		<li><a href="javascript:{$objectName}.setPageNumber({$linkPageNumber});" class="p">
		  <xsl:value-of select="$linkPageNumber"/>
		</a></li>
	  </xsl:otherwise>
</xsl:choose>
</xsl:template>

<xsl:template name="pageLinkLoop">
<xsl:param name="currentPageNumber">10</xsl:param>
<xsl:param name="start" >2</xsl:param>
<xsl:param name="stop" >50</xsl:param>
<xsl:param name="pageLinkStep">100</xsl:param>
<xsl:param name="numSurroundingPages">3</xsl:param>
<xsl:param name="objectName"></xsl:param>
	   <xsl:if test='$start &lt;= $stop'>
		  <xsl:if test='(($start mod $pageLinkStep) = 0) or (($start &gt; $currentPageNumber - $numSurroundingPages) and ($start &lt; $currentPageNumber + $numSurroundingPages))'>
			<xsl:call-template name="pageLink">
				<xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
				<xsl:with-param name="linkPageNumber" select="$start"/>
				<xsl:with-param name="objectName" select="$objectName"/>
			</xsl:call-template>
		  </xsl:if >
		  <xsl:call-template name="pageLinkLoop">
			  <xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
			  <xsl:with-param name="start" select="$start + 1"/>
			  <xsl:with-param name="stop" select="$stop"/>
			  <xsl:with-param name="pageLinkStep" select="$pageLinkStep"/>
			  <xsl:with-param name="objectName" select="$objectName"/>
		  </xsl:call-template>
	   </xsl:if>
</xsl:template>

<xsl:template name="pager">
<xsl:param name="minPageNumber" >1</xsl:param>
<xsl:param name="maxPageNumber" >50</xsl:param>
<xsl:param name="currentPageNumber">10</xsl:param>
<xsl:param name="objectName"></xsl:param>
	   <!-- print the first page link -->
	   <xsl:call-template name="pageLink">
		   <xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
		   <xsl:with-param name="linkPageNumber"  select="$minPageNumber"/>
		   <xsl:with-param name="objectName" select="$objectName"/>
	   </xsl:call-template>
	   <!-- if the min page is equal to (or greater than) the last page than we are done -->
	   <xsl:if test='$minPageNumber &lt; $maxPageNumber'>
		   <!-- algorithmically print up to the second to last page link -->
		   <xsl:call-template name="pageLinkLoop">
			   <xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
			   <xsl:with-param name="start">2</xsl:with-param>
			   <xsl:with-param name="stop" select="$maxPageNumber - 1"/>
			   <xsl:with-param name="objectName" select="$objectName"/>
		   </xsl:call-template>
		   <!-- print the last page link -->
		   <xsl:call-template name="pageLink">
			   <xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
			   <xsl:with-param name="linkPageNumber"  select="$maxPageNumber"/>
			   <xsl:with-param name="objectName" select="$objectName"/>
		   </xsl:call-template>
	   </xsl:if>
</xsl:template>

<xsl:template name="pageBar">
<xsl:param name="maxPageNumber">50</xsl:param>
<xsl:param name="currentPageNumber">10</xsl:param>
<xsl:param name="objectName"></xsl:param>

	<div class="paging">
		<div class="returned">
			<span>
			<!-- string ordering results inputtype paging -->
			<xsl:call-template name="stringorder">
				<xsl:with-param name="orderid" select="'armory.order.results-Inputpaging-string'"/>
				<xsl:with-param name="datainsert1" select="$maxPageNumber"/>
				<xsl:with-param name="ResultsInputPaging"/>
				<xsl:with-param name="datainsert2" select="$currentPageNumber"/>
				<xsl:with-param name="datainsert3" select="$objectName"/>
			</xsl:call-template>
			</span>
		</div>
		<div class="pnav">
		<ul>
		  <!-- first page -->
		  <li>
		  <xsl:choose>
			<xsl:when test="$currentPageNumber = 1">
			  <a class="prev-first-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:when>
			<xsl:otherwise>
			  <a href="javascript:{$objectName}.setPageNumber(1);" class="prev-first"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:otherwise>
		  </xsl:choose>
		  </li>

		  <!-- prev page -->
		  <li>
		  <xsl:choose>
			<xsl:when test="$currentPageNumber &lt;= 1">
			  <a class="prev-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:when>
			<xsl:otherwise>
			  <a href="javascript:{$objectName}.setPageNumber({$currentPageNumber - 1});" class="prev"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:otherwise>
		  </xsl:choose>
		  </li>

		  <xsl:call-template name="pager">
			 <xsl:with-param name="maxPageNumber" select="$maxPageNumber"/>
			 <xsl:with-param name="currentPageNumber" select="$currentPageNumber"/>
			 <xsl:with-param name="objectName" select="$objectName"/>
		  </xsl:call-template>

		  <!-- next page -->
		  <li>
		  <xsl:choose>
			<xsl:when test="$currentPageNumber &gt;= $maxPageNumber">
			  <a class="next-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:when>
			<xsl:otherwise>
			  <a href="javascript:{$objectName}.setPageNumber({$currentPageNumber + 1});" class="next"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:otherwise>
		  </xsl:choose>
		  </li>

		  <!-- last page -->
		  <li>
		  <xsl:choose>
			<xsl:when test="$currentPageNumber &gt;= $maxPageNumber">
			  <a class="next-last-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:when>
			<xsl:otherwise>
			  <a href="javascript:{$objectName}.setPageNumber({$maxPageNumber});" class="next-last"><img src="/images/pixel.gif" width="1" height="1" /></a>
			</xsl:otherwise>
		  </xsl:choose>
		  </li>
		</ul>
		</div>
		<!--/end pnav/-->
	</div><!--/end paging/-->

</xsl:template>
<!--layout-utils end-->


<!-- Access Error Window (guild banks) -->
<xsl:template name="armorymsg">
	<xsl:param name="title"></xsl:param>
	<xsl:param name="message"></xsl:param>
	<xsl:param name="displaybutton"></xsl:param>
	<div class="filtercontainer" style="width: 45%; margin: 20px auto 0; padding: 6px;">
		<div class="clearfilterboxsm"/>
		<div class="bankcontentsfiltercontainer" style="width: 100%; text-align: center;">
			<div style="padding: 0 10px 10px; ">
			<div class="guildloginmsg" style=" background: url('/images/parch-warning.gif') center left no-repeat;">
				<table style="height: 55px!important; vertical-align: middle;"><tr><td style="vertical-align: middle!important; color: #836028;">
					<xsl:if test="$title">
						<div class="guilderrortitle"><xsl:value-of select="$title"/></div>
					</xsl:if>
					<xsl:value-of select="$message"/>
				</td></tr></table>
			</div>
			<xsl:if test="string-length($displaybutton) &gt; 0">
				<div style="display: table; margin: 13px auto 5px;">
					<span><h1 class="hbluebutton"><q class="centerbluebutton"><a id="loginsubmitbutton" class="bluebutton" href="javascript: document.loginRedirect.submit();"><div class="bluebutton-a"/><div class="bluebutton-b"><div class="reldiv"><div class="bluebutton-color"><xsl:value-of select="$displaybutton"/></div>
					</div>
					<xsl:value-of select="$displaybutton"/></div><div class="bluebutton-key"/><div class="bluebutton-c"></div></a></q></h1></span>
				</div>
			</xsl:if>
			</div>
		</div>
		<div class="clearfilterboxsm"/>
	</div>
	<div class="bottomshadowsm"/>
</xsl:template>

<xsl:template name="truncate">
	<xsl:param name="string"/>
	<xsl:param name="length"/>
	<xsl:param name="suffix" select="'…'" />

	<xsl:value-of select="substring($string, 1, $length)"/>
	<xsl:if test="string-length($string) &gt; $length">
		<xsl:copy-of select="$suffix"/>
	</xsl:if>
</xsl:template>

<xsl:template name="relatedContainer">
	<xsl:variable name="isPropass">
		<xsl:choose>
			<xsl:when test="/page/characterInfo/character/@tournamentRealm">true</xsl:when>
			<xsl:otherwise>false</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="isRecommended" select="/page/armorySearch/searchResults/filters/filter[@name='recc']/@value=1" />
<xsl:choose>
	<xsl:when test="/page/armorySearch/searchResults/arenaTeams">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-arena-ladder.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/armorySearch/searchResults/characters">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-character-sheet.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/armorySearch/searchResults/guilds">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-guild-info.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="$isRecommended = 1">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-item-info-recommended.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/armorySearch/searchResults/items">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-item-info.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/characterInfo/characterTab">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src">
				<xsl:choose>
					<xsl:when test = "$isPropass != 'true'">/strings/<xsl:value-of select = "$lang" />/ri-character-sheet.xml</xsl:when>
					<xsl:otherwise>/strings/<xsl:value-of select = "$lang" />/ri-propass.xml</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/characterInfo/reputationTab">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-character-reputation.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/characterInfo/talentTab">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src">
				<xsl:choose>
					<xsl:when test = "$isPropass != 'true'">/strings/<xsl:value-of select = "$lang" />/ri-character-talents.xml</xsl:when>
					<xsl:otherwise>/strings/<xsl:value-of select = "$lang" />/ri-propass.xml</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/guildInfo/guild or /page/guildBank">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('/strings/',$lang,'/ri-guild-info.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/teamInfo/arenaTeam">
		<xsl:call-template name="related-info">
				<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-team-info.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/selectTeamType">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-arena-calculator.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/battlegroups">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-arena-ladder.xml')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/arenaLadderPagedResult/arenaTeams">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src">
				<xsl:choose>
					<xsl:when test = "$isPropass != 'true'">/strings/<xsl:value-of select = "$lang" />/ri-arena-ladder.xml</xsl:when>
					<xsl:otherwise>/strings/<xsl:value-of select = "$lang" />/ri-propass.xml</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:when>
	<xsl:when test="/page/@requestUrl='/vault/character-calendar.xml'">
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('/strings/',$lang,'/ri-calendar.xml')" />
		</xsl:call-template>
	</xsl:when>
	<xsl:otherwise>
		<xsl:call-template name="related-info">
			<xsl:with-param name="src" select="concat('../strings/',$lang,'/ri-armory.xml')"/>
		</xsl:call-template>
	</xsl:otherwise>
</xsl:choose>





</xsl:template>

<!-- l10n -->

<xsl:template match="*" mode="printf">
	<xsl:param name="param1"/>
	<xsl:param name="param2"/>
	<xsl:param name="param3"/>

	<xsl:apply-templates mode="printf">
		<xsl:with-param name="param1" select="$param1"/>
		<xsl:with-param name="param2" select="$param2"/>
		<xsl:with-param name="param3" select="$param3"/>
	</xsl:apply-templates>
</xsl:template>

<xsl:template match="param1" mode="printf"><xsl:param name="param1"/><xsl:value-of select="$param1"/></xsl:template>
<xsl:template match="param2" mode="printf"><xsl:param name="param2"/><xsl:value-of select="$param2"/></xsl:template>
<xsl:template match="param3" mode="printf"><xsl:param name="param3"/><xsl:value-of select="$param3"/></xsl:template>
<xsl:template match="param-html1" mode="printf"><xsl:param name="param1"/><xsl:copy-of select="$param1"/></xsl:template>
<xsl:template match="param-html2" mode="printf"><xsl:param name="param2"/><xsl:copy-of select="$param2"/></xsl:template>
<xsl:template match="param-html3" mode="printf"><xsl:param name="param3"/><xsl:copy-of select="$param3"/></xsl:template>

</xsl:stylesheet>
