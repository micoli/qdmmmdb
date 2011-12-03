<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:template name = "truncGuildName">
  <xsl:param name = "theGuildName" />
  <xsl:choose>
    <xsl:when test = "string-length($theGuildName) &gt; 18" ><span onMouseOut = "javascript: hideTip();" onMouseOver="javascript: showTip('{$theGuildName}')"><xsl:value-of select = "substring($theGuildName, 1, 16)" />...</span></xsl:when>
	<xsl:otherwise><xsl:value-of select = "$theGuildName" /></xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="lastSearchTemplate">
	<xsl:param name="searchNode" />
<script type="text/javascript">


	//<![CDATA[		  



function value(a,b) {
a = a[globalSort[0]] + a[0][0];
b = b[globalSort[0]] + b[0][0];
return a == b ? 0 : (a < b ? -1 : 1)
}

function valueAs(a,b) {
a = a[globalSort[0]] + a[0][0];
b = b[globalSort[0]] + b[0][0];
return a == b ? 0 : (a < b ? 1 : -1)
}

function sortNumber(a, b) {
return b[globalSort[0]][0] - a[globalSort[0]][0];
}

function sortNumberAs(a, b) {
return a[globalSort[0]][0] - b[globalSort[0]][0];
}

var globalSort = new Array;

function sortSearch2(whichElement) {


if (whichElement < 0)
	whichElement = 0 - whichElement;
globalSort[0] = whichElement;
globalSort[1] = getcookie2("cookieLeftSortUD");



  if ((typeof rightArray[0][whichElement][0]) == 'string') {
    sortAs = valueAs;
	sortDe = value;
  } else {
    sortAs = sortNumberAs;
	sortDe = sortNumber;
  }

if (globalSort[1] == 'u')
    rightArray.sort(eval(sortAs));
else
    rightArray.sort(eval(sortDe));


}


	//]]>
</script>
<xsl:choose>
	<xsl:when test="$searchNode = 'guild' and (string-length(/page/characterInfo/character/@guildName) &gt; 0 or /page/guildInfo/guild)">
		<xsl:call-template name="miniGuildSearchTemplate">
			<xsl:with-param name="path" select="$searchNode" />
		</xsl:call-template>	
	</xsl:when>
	<xsl:when test="$searchNode and $searchNode != 'guild'">
		<!-- set the expanded search panel style -->
		<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />	

		<!-- set the search js params now -->
		<script type="text/javascript">
			//alert("mini search type = <xsl:value-of select='$searchNode/@searchType'/>" +
			//	"\nmini search text = <xsl:value-of select='$searchNode/@searchText'/>" +
			//	"\nmini search tab = <xsl:value-of select='$searchNode/../tabs/@selected'/>");
			miniSearchPanelInstance.setSearchTypeParam("<xsl:value-of select='$searchNode/@searchType'/>");
			miniSearchPanelInstance.setSearchQueryParam("<xsl:value-of select='$searchNode/@searchText'/>");
			miniSearchPanelInstance.setSelectedTabParam("<xsl:value-of select='$searchNode/../tabs/@selected'/>");
			<xsl:choose>
				<xsl:when test="../miniSearch/filters">
					<xsl:for-each select="../miniSearch/filters/filter">
						miniSearchPanelInstance.setSearchFilterParam("<xsl:value-of select='@name'/>", "<xsl:value-of select='@value'/>");
					</xsl:for-each>
				</xsl:when>
				<xsl:when test="$searchNode/filters">
					<xsl:for-each select="$searchNode/filters/filter">
						miniSearchPanelInstance.setSearchFilterParam("<xsl:value-of select='@name'/>", "<xsl:value-of select='@value'/>");
					</xsl:for-each>
				</xsl:when>
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="../miniSearch/@pn and ../miniSearch/@pr and ../miniSearch/@pi">
					miniSearchPanelInstance.setProfileNameParam("<xsl:value-of select='../miniSearch/@pn'/>");
					miniSearchPanelInstance.setProfileRealmParam("<xsl:value-of select='../miniSearch/@pr'/>");
					miniSearchPanelInstance.setProfileItemParam(<xsl:value-of select='../miniSearch/@pi'/>);
				</xsl:when>
			</xsl:choose>
			<xsl:choose>
				<xsl:when test="../miniSearch/@page">
					miniSearchPanelInstance.setSearchPageNumberParam(<xsl:value-of select="../miniSearch/@page"/>);
				</xsl:when>
				<xsl:otherwise>
					miniSearchPanelInstance.setSearchPageNumberParam(<xsl:value-of select="$searchNode/@pageCurrent"/>);
				</xsl:otherwise>
			</xsl:choose>
		</script>

		<xsl:choose>
			<xsl:when test="$searchNode/arenaTeams">
				<!-- <p>CALLING ARENA LADDER SEARCH TEMPLATE</p> -->
				<xsl:call-template name="miniArenaLadderSearchTemplate">
					<xsl:with-param name="path" select="$searchNode" />
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$searchNode/items">
				<!-- <p>CALLING ITEMS SEARCH TEMPLATE</p> -->
				<xsl:call-template name="miniItemSearchTemplate">
					<xsl:with-param name="path" select="$searchNode" />
				</xsl:call-template>
			</xsl:when>
		</xsl:choose>
	</xsl:when>
  </xsl:choose>
</xsl:template>


<xsl:template name="miniCharSearchTemplate">
	<xsl:param name="path"></xsl:param>

	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />

	<script type="text/javascript">
		// if we are on the char info page then we can highlight a search result
		try {
			if (characterInfoPageInstance != null) {
				miniSearchPanelInstance.setCanHighlightSearchSelection(true);
			}
		} catch(e) {
		}

		// MFS TODO: finish implementing the eye
		/*
		var searchDefaulter = document.getElementById("search-defaulter");
		var pageObject = miniSearchPanelInstance.getPageObject();
		if (searchDefaulter != null &amp;&amp; pageObject != null &amp;&amp; pageObject.ms_generateDefaultXmlUrl != null) {
			searchDefaulter.style.display = "block";
		}
		*/
	</script>




	<xsl:variable name = "theRealm">
	  <xsl:choose>
	    <xsl:when test = "/page/guildInfo/guild"><xsl:value-of select = "/page/guildInfo/guild/@realm" /></xsl:when>
		<xsl:otherwise><xsl:value-of select = "/page/characterInfo/character/@realm" /></xsl:otherwise>
	  </xsl:choose>
	</xsl:variable>

	<xsl:variable name = "theName">
	  <xsl:choose>
	    <xsl:when test = "/page/guildInfo/guild"><xsl:value-of select = "/page/guildInfo/guild/@name" /></xsl:when>
		<xsl:otherwise><xsl:value-of select = "/page/characterInfo/character/@guildName" /></xsl:otherwise>		
	  </xsl:choose>
	</xsl:variable>

	<xsl:variable name = "theCharName"><xsl:value-of select = "/page/characterInfo/character/@name" /></xsl:variable>

	<div class="results-side">
		<div class="results-list">
	<div class="ps"><div class="ps-bot"><div class="ps-top">
			<div class="result-banner">
				<a id="search-defaulter" href="javascript: miniSearchPanelInstance.revertToDefault()" class="search-default staticTip" onmouseover="setTipText('{$loc/strs/unsorted/str[@id='armory.mini-search.label.character.default']}')"></a>
				
				<h3 class="results-header"><em><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.header.guild-roster-for']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/>
				<i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><xsl:call-template name = "truncGuildName"><xsl:with-param name = "theGuildName" select='$theName'/></xsl:call-template><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></i></em></h3>
			</div>
	<div class="data">


	<script type="text/javascript">
	
	var globalCurChar = "<xsl:value-of select = "$theCharName" />";

	//<![CDATA[		  
		  var rightArray = new Array();
		  var raC = 0;

	function returnMid(i, theTrunc) {
	var theString = "";
				theString = '<tr';
				if (rightArray[i][1] == globalCurChar) {
					theString += ' class = "data2"';
				}
				theString += '><td><div></div></td><td><q><a';
				if (rightArray[i][1].length >= theTrunc)
					theString += ' class="staticTip" onMouseOver = "setTipText(&quot;'+ rightArray[i][1] +'&quot;)"';
				theString += ' href = "character-sheet.xml?'+ rightArray[i][0][1] +'">';
				theString += truncateString(rightArray[i][1], theTrunc);
				theString += '</a></q></td><td width="20"><img onMouseOver = "setTipText(&quot;'+ rightArray[i][5] +'&quot;)"';
				theString += ' class="ci staticTip" src="/images/icons/race/'+ rightArray[i][2] +'-'+ rightArray[i][3] +'.gif" /></td>';
				theString += '<td width="20"><img onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ rightArray[i][6] +'&quot;)" class="ci" src="/images/icons/class/'+ rightArray[i][4] +'.gif" /></td><td align="right"><div></div></td></tr>';
				return theString;
	}
	//]]>


<xsl:variable name = "locGuild" select = "document(concat('/guild-info.xml?brief=1&amp;r=', $theRealm, '&amp;n=', $theName))" />
<xsl:variable name = "guildName" select = "$locGuild/page/guildInfo/guild/@realm" />
	<xsl:for-each select="$locGuild/page/guildInfo/guild/members/character">
	  rightArray[raC] = [["<xsl:value-of select = "@n" />", "?n=<xsl:value-of select = "@n" />&amp;r=<xsl:value-of select = "$guildName" />"], "<xsl:value-of select = "@n" />", "<xsl:value-of select = "@rId" />", "<xsl:value-of select = "@gId" />", "<xsl:value-of select = "@cId" />", "race", "class"]; raC++;
	</xsl:for-each>
    rightArray.sort(sortNumberRightAs);

	function returnArrayPos(theElement, theArray) {
		for (var x=0; x&lt;theArray.length; x++) {
			if (theElement == theArray[x][1])
				return x;
		}
		return 1;
	}
	setcookie("cookieRightPage", (Math.ceil(returnArrayPos(globalCurChar, rightArray)/20)));	
	</script>
	<div id = "divBackTop"></div>	
	<div id = "divRight"></div>
	<div id = "divForwardBot"></div>
	<div id = "divPagingBot"></div>	

	<script type="text/javascript" src="/js/paging/right/functions.js" ></script>


    <a href="/search.xml?{$path/@url}" class="backlink"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.back-to-results']"/></a>
	
	</div>
			</div>
		</div>
	</div><!--/end ps/-->
	
		</div><!--/end results-list/-->
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniArenaLadderSearchTemplate">
	<xsl:param name="path"></xsl:param>
	<xsl:variable name="numArenaTeams" select="count($path/arenaTeams/arenaTeam)" />
	
	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />	

	<script type="text/javascript">

		// if we are on a team info page then we can highlight a search result
		try {
			if (teamInfoPageInstance != null) {
				//alert('teamInfoPageInstance not null');
				miniSearchPanelInstance.setCanHighlightSearchSelection(true);
			}
		} catch(e) {
		}

		// MFS TODO: finish implementing the eye
		/*
		var searchDefaulter = document.getElementById("search-defaulter");
		var pageObject = miniSearchPanelInstance.getPageObject();
		if (searchDefaulter != null &amp;&amp; pageObject != null &amp;&amp; pageObject.ms_generateDefaultXmlUrl != null) {
			searchDefaulter.style.display = "block";
		}
		*/
	</script>


	<div class="results-side">
		<div class="results-list">


<!--	<div class="division">
		<a href = "#" onClick="resultsSideLeft(&quot;search.xml?{$path/@url}&quot;); return false;" class="left-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.search.expand']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
		<a href="javascript: resultsSideRight()" class="right-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.search.collapse']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
	</div>-->

	<div class="ps"><div class="ps-bot"><div class="ps-top">
			<div class="result-banner">
				<a id="search-defaulter" href="javascript: miniSearchPanelInstance.revertToDefault()" class="search-default" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.character.default']}')" onmouseout="hideTip()"></a>
				<h3 class="results-header"><em><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.team-results']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><i><xsl:value-of select='$path/@searchText'/></i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></em></h3>
			</div>
	<div class="data">




	<script type="text/javascript">
		  var globalCurTeam = "<xsl:value-of select = "/page/teamInfo/arenaTeam/@url" />";	  
		  var isArenaTeamsRight = 1;
		  
		var flashVarsString="";    
		  
	//<![CDATA[		  
		  var rightArray = new Array();
		  var raC = 0;

	
	function returnMid(i, theTrunc) {

				var theString = "";
				blah = (i%20)+1;				
				theString = '<tr onmouseover="popIconLarge(&quot;teamIconBoxresults2Flash&quot;,&quot;iconObject'+blah+'&quot;)" onmouseout="popIconSmall(&quot;teamIconBoxresults2Flash&quot;,&quot;iconObject'+ blah +'&quot;)"';
				if (rightArray[i][0][0] == globalCurTeam)
					theString += ' class = "data2"';
				theString += '><td><div></div></td>';
				theString += '<td><q><a class = "rarity" href = "team-info.xml?'+ rightArray[i][0][0] +'"';
				if (rightArray[i][1][0].length > theTrunc)
					theString += 'onmouseout="hideTip()" onmouseover="showTip(&quot;'+ rightArray[i][1][0] +'&quot;)"';
				theString += '>';
				theString += truncateString(rightArray[i][1][0], theTrunc);
				theString += '</a></q></td>';
				theString += '<td><q><span class="smList" style="padding-right: 20px; ">'+ rightArray[i][4][1] +'</span></q></td>';
				theString += '<td align="right"><div></div></td></tr>';
				

			flashVarsString+="iconName"+ blah +"=images/icons/team/pvp-banner-emblem-"+ rightArray[i][0][1] +".png&#38;iconColor"+ blah +"="+ rightArray[i][0][2] +"&#38;bgColor"+ blah +"="+ rightArray[i][0][3] +"&#38;borderColor"+ blah +"="+ rightArray[i][0][4] +"&#38;teamUrl"+ blah +"=team-info.xml?"+ (rightArray[i][0][0]).replace(/&/g, "%26") +"&#38;";
				
				return theString;
				
	}
	
	//]]>

	<xsl:for-each select="$path/arenaTeams/arenaTeam">
	  rightArray[raC] = [
	  ["<xsl:value-of select="@url"/>", "<xsl:value-of select="emblem/@iconStyle"/>", "<xsl:value-of select="emblem/@iconColor"/>", "<xsl:value-of select="emblem/@background"/>", "<xsl:value-of select="emblem/@borderColor"/>"],
	  ["<xsl:value-of select="@name"/>"],
	  ["<xsl:value-of select="@realm"/>"],
	  ["<xsl:value-of select="@battleGroup"/>"],
	  ["<xsl:value-of select="@size"/>", textVs<xsl:value-of select="@size"/> ],
	  ["<xsl:value-of select="@factionId"/>"],
	  [<xsl:value-of select="@relevance"/>]]; raC++;
	</xsl:for-each>
sortSearch2(getcookie2("cookieLeftSort"));
	</script>
	<div id = "divBackTop"></div>	
	<div id="teamIconBoxContainer2">
		<div id="teamIconBoxresults2"></div>
	</div>	  	
	<div id = "divRight"></div>
	<div id = "divForwardBot"></div>
	<div id = "divPagingBot"></div>	

	<script type="text/javascript" src="/js/paging/right/functions.js" ></script>











	</div><!--/end data/-->

			</div>
		</div>
	</div><!--/end ps/-->
	
		</div><!--/end results-list/-->
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniGuildSearchTemplate">
	<xsl:param name="path"></xsl:param>
	<xsl:variable name = "theRealm">
	  <xsl:choose>
	    <xsl:when test = "/page/guildKey"><xsl:value-of select = "/page/guildKey/@realm" /></xsl:when>
		<xsl:otherwise><xsl:value-of select = "/page/characterInfo/character/@realm" /></xsl:otherwise>
	  </xsl:choose>
	</xsl:variable>

	<xsl:variable name = "theName">
	  <xsl:choose>
	    <xsl:when test = "/page/guildKey"><xsl:value-of select = "/page/guildKey/@name" /></xsl:when>
		<xsl:otherwise><xsl:value-of select = "/page/characterInfo/character/@guildName" /></xsl:otherwise>		
	  </xsl:choose>
	</xsl:variable>

	<xsl:variable name = "theTestUrl">
	  <xsl:choose>
	    <xsl:when test = "/page/guildKey"><xsl:value-of select = "/page/guildKey/@url" /></xsl:when>
		<xsl:otherwise><xsl:value-of select = "/page/characterInfo/character/@guildUrl" /></xsl:otherwise>
	  </xsl:choose>
	</xsl:variable>

	<xsl:variable name = "theUrl">
	  <xsl:choose>
	    <xsl:when test = "count(document(concat('/guild-info.xml?brief=1&amp;', $theTestUrl))/page/guildInfo/guild/members/character) = 0"><xsl:call-template name = "search-and-replace">
  <xsl:with-param name = "input" select = "$theTestUrl" />
  <xsl:with-param name = "search-string" select = "'%'" />
  <xsl:with-param name = "replace-string" select = "'%25'" />
</xsl:call-template></xsl:when>
		<xsl:otherwise><xsl:value-of select = "$theTestUrl" /></xsl:otherwise>
	  </xsl:choose>
	</xsl:variable>
	
	<xsl:variable name = "theCharName"><xsl:value-of select = "/page/characterInfo/character/@name" /></xsl:variable>

<xsl:variable name = "locGuild" select = "document(concat('/guild-info.xml?brief=1&amp;', $theUrl))" />

	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />

	<div class="results-side">
		<div class="results-list">
<!--	<div class="division">
		<a href = "#" onClick="resultsSideLeft(&quot;guild-info.xml?{$locGuild/page/guildInfo/guild/@rosterUrl}&quot;); return false;" class="left-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.guild.expand']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
		<a href="javascript: resultsSideRight()" class="right-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.guild.collapse']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
	</div>-->
	<div class="ps"><div class="ps-bot"><div class="ps-top"><div class="data">
			 <div class="result-banner">
				<a id="search-defaulter" href="javascript: miniSearchPanelInstance.revertToDefault()" class="search-default" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.character.default']}')" onmouseout="hideTip()"></a>
				<h3 class="results-header"><em><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.header.guild-roster-for']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><i><xsl:call-template name = "truncGuildName"><xsl:with-param name = "theGuildName" select='$theName'/></xsl:call-template></i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></em></h3>
			</div>






<div class="parch-search"><input value = "{$loc/strs/guild/str[@id='searchroster']}" onblur="if (this.value=='') this.value='{$loc/strs/guild/str[@id='searchroster']}'" onfocus="this.value=''" size = "16" type="text" onKeyUp = "javascript: filterRightArray(this.value)" id = "inputFilter"/></div>

	<script type="text/javascript">

	var globalCurChar = "<xsl:value-of select = "$theCharName" />";

	//<![CDATA[		  
		  var rightArray = new Array();
	var filteredArray = new Array();		  
		  var raC = 0;
		  var filterPage = 1;
		  var totalFilterPages = 0;

	function returnMid(i, theTrunc) {
	var theString = "";
				theString = '<tr';
				if (rightArray[i][1] == globalCurChar) {
					theString += ' class = "data2"';
				}
				theString += '><td><div></div></td><td><q><a';
				if (rightArray[i][1].length >= theTrunc)
					theString += ' onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ rightArray[i][1] +'&quot;)"';
				theString +=' href = "character-sheet.xml?'+ rightArray[i][0][1] +'">';
				theString += truncateString(rightArray[i][1], theTrunc);
				theString += '</a></q></td><td width="20"><img onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ rightArray[i][5] +'&quot;)"';
				theString += ' class="ci" src="/images/icons/race/'+ rightArray[i][2] +'-'+ rightArray[i][3] +'.gif" /></td>';
				theString += '<td width="20"><img onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ rightArray[i][6] +'&quot;)" class="ci" src="/images/icons/class/'+ rightArray[i][4] +'.gif" /></td><td align="right"><div></div></td></tr>';
				return theString;
	}

	function returnMid2(i, theTrunc) {
	var theString = "";
				theString = '<tr';
				if (filteredArray[i][1] == globalCurChar) {
					theString += ' class = "data2"';
				}
				theString += '><td><div></div></td><td><q><a';
				if (filteredArray[i][1].length >= theTrunc)
					theString += ' onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ filteredArray[i][1] +'&quot;)"';
				theString +=' href = "character-sheet.xml?'+ filteredArray[i][0][1] +'">';
				theString += truncateString(filteredArray[i][1], theTrunc);
				theString += '</a></q></td><td width="20"><img onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ filteredArray[i][5] +'&quot;)"';
				theString += ' class="ci" src="/images/icons/race/'+ filteredArray[i][2] +'-'+ filteredArray[i][3] +'.gif" /></td>';
				theString += '<td width="20"><img onmouseout="hideTip()" onMouseOver = "showTip(&quot;'+ filteredArray[i][6] +'&quot;)" class="ci" src="/images/icons/class/'+ filteredArray[i][4] +'.gif" /></td><td align="right"><div></div></td></tr>';
				return theString;
	}

	//]]>

	<xsl:for-each select="$locGuild/page/guildInfo/guild/members/character">
	  rightArray[raC] = [["<xsl:value-of select = "@n" />", "<xsl:value-of select = "@url" />"], "<xsl:value-of select = "@n" />", "<xsl:value-of select = "@rId" />", "<xsl:value-of select = "@gId" />", "<xsl:value-of select = "@cId" />", text<xsl:value-of select = "@rId" />race, text<xsl:value-of select = "@cId" />class]; raC++;
	</xsl:for-each>
    rightArray.sort(sortNumberRightAs);	

	function returnArrayPos(theElement, theArray) {
		for (var x=0; x&lt;theArray.length; x++) {
			if (theElement == theArray[x][1])
				return x;
		}
		return 1;
	}
	setcookie("cookieRightPage", (Math.ceil(returnArrayPos(globalCurChar, rightArray)/20)));	

	</script>
	<div id = "divBackTop"></div>	
	<div id = "divRight"></div>
	<div id = "divForwardBot"></div>
	<div id = "divPagingBot"></div>	

	<script type="text/javascript" src="/js/paging/right/functions.js" ></script>








	</div><!--/end data/-->
			</div>
		</div>
	</div><!--/end ps/-->
	
		</div><!--/end results-list/-->
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniItemSearchTemplate">
	<xsl:param name="path"></xsl:param>

	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />

	<script type="text/javascript">
		// if we are on the char info page then we can highlight a search result
		try {
			if (itemInfoPageInstance != null) {
				miniSearchPanelInstance.setCanHighlightSearchSelection(true);
			}
		} catch(e) {
		}
	</script>

	<div class="results-side">
		<div class="results-list">
			<div class="result-banner">
				<a id="search-defaulter" href="javascript: miniSearchPanelInstance.revertToDefault()" class="search-default" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.character.default']}')" onmouseout="hideTip()"></a>
				<div class="ps">
					<div class="ps-bot">
						<div class="ps-top">
							<h3 class="results-header"><em>
								<xsl:choose>
									<xsl:when test="string-length($path/@searchText) &gt; 0">
										<xsl:choose>
											<xsl:when test="$lang='ko_kr'">				
												<i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><xsl:value-of select='$path/@searchText'/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.item-results-for']"/>
											</xsl:when>
											<xsl:otherwise>	
												<xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.item-results-for']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/><!-- Modified by Ock - mini-search-templates-searchText-string --><i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><xsl:value-of select='$path/@searchText'/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></i>			
											</xsl:otherwise>
										</xsl:choose>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.item-results']"/>
									</xsl:otherwise>
								</xsl:choose>
							</em></h3>
						</div>
						<div class="data">

	<script type="text/javascript" src = "/js/{$lang}/results-items.js"></script>
	<script type="text/javascript">
	
	  var globalCurItem = "<xsl:value-of select = "/page/itemInfo/item/@id" />";
	//<![CDATA[		  
	
		  var rightArray = new Array();
		  var raC = 0;
	
	function returnMid(i, theTrunc) {
	var theString = "";
				theString = '<tr';
				if (rightArray[i][0][1] == globalCurItem)
					theString += ' class = "data2"';
				theString += '>';
				theString += '<td width="21"><img id="'+rightArray[i][0][2]+'" class="p21s staticTip itemToolTip" src="/wow-icons/_images/21x21/'+ rightArray[i][0][0] +'.png" /></td>';
				theString += '<td><q><a id="'+rightArray[i][0][2]+'" class = "rarity'+ rightArray[i][0][3] +' staticTip itemToolTip" href="item-info.xml?'+ rightArray[i][0][2] +'">';
				theString += truncateString(rightArray[i][1][0], theTrunc);
				theString += '</a></q></td>';
				theString += '</tr>';
				return theString;
	}
	
	//]]>

	<xsl:for-each select="$path/items/item">
	itemName = "<xsl:call-template name = "search-and-replace">
  						<xsl:with-param name = "input" select = "@name" />
  						<xsl:with-param name = "search-string" select = "'&#34;'" />
  						<xsl:with-param name = "replace-string" select = "'\&#34;'" />
							</xsl:call-template>";
	
	  rightArray[raC] = [["<xsl:value-of select="@icon"/>", <xsl:value-of select="@id"/>, "<xsl:value-of select="@url"/>", "<xsl:value-of select="@rarity"/>"], 
	  [itemName], 
	  
	  <xsl:for-each select="filter">
	    <xsl:choose>
		  <xsl:when test = "@name = 'source'" >[<xsl:value-of select="@value"/>, "<xsl:value-of select="@areaName"/>", <xsl:value-of select="@areaId"/>, <xsl:value-of select="@creatureId"/>, "<xsl:value-of select="@creatureName"/>", "<xsl:value-of select="@difficulty"/>", <xsl:value-of select="@dropRate"/>]</xsl:when>
		  <xsl:when test = "@name = 'type'" >["<xsl:value-of select="@value"/>"]</xsl:when>
		  <xsl:when test = "@name = 'inventoryType'" >[slot<xsl:value-of select="@value"/>]</xsl:when>
		  <xsl:when test = "@name = 'hasSpellEffect'" >[spellEffect<xsl:value-of select="@value"/>]</xsl:when>		  
		  <xsl:when test = "@value = -1" >[0]</xsl:when>
		  <xsl:otherwise>[<xsl:value-of select="@value"/>]</xsl:otherwise>
		</xsl:choose>
		<xsl:choose><xsl:when test="position() != last()">, </xsl:when></xsl:choose>
	  </xsl:for-each>
	  
	  ];raC++;
	</xsl:for-each>
sortSearch2(getcookie2("cookieLeftSort"));
	</script>
	<div id = "divBackTop"></div>	
	<div id = "divRight"></div>
	<div id = "divForwardBot"></div>
	<div id = "divPagingBot"></div>	

	<script type="text/javascript" src="/js/paging/right/functions.js" ></script>

						</div>
					</div>
				</div>
			</div><!--/end ps/-->
		</div><!--/end results-list/-->
		<div class="backlisting">
			<xsl:choose>
				<xsl:when test="string-length($path/@searchFilter) &gt; 0">
					<a href="javascript:return false" onclick="resultsSideLeft(&quot;search.xml?{$path/@url}&amp;{$path/@searchFilter}&quot;); return false;"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.mini-search.label.items.expand']"/></a>
				</xsl:when>
				<xsl:otherwise>
					<a href="javascript:return false" onclick="resultsSideLeft(&quot;search.xml?{$path/@url}&quot;); return false;"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.mini-search.label.items.expand']"/></a>
				</xsl:otherwise>
			</xsl:choose>	
		</div>
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniGuildRosterTemplate">
	<xsl:param name="path"></xsl:param>

	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />


	<script type="text/javascript">
		try {
			if (characterInfoPageInstance != null) {
				//alert('<xsl:value-of select="$path/guild/@name"/>');
				characterInfoPageInstance.ms_setGuildNameParam('<xsl:value-of select="$path/guild/@name"/>');
			}
		} catch(e) {
		}
	</script>


	<div class="results-side">
		<div class="results-list">
		
<!--	<div class="division">
		<a href = "#" onClick="resultsSideLeft(&quot;guild-info.xml?{$path/guild/@rosterUrl}&quot;); return false;" class="left-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.guild.expand']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
		<a href="javascript: resultsSideRight()" class="right-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.guild.collapse']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
	</div>-->
	<div class="ps"><div class="ps-bot"><div class="ps-top">
			<div class="result-banner">
		<h3 class="results-header"><em><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.labels.header.guild-roster-for']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.normalSpace']"/><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.openQuote']"/><i><xsl:call-template name = "truncGuildName"><xsl:with-param name = "theGuildName" select='$path/guild/@name'/></xsl:call-template></i><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.search.closeQuote']"/></em></h3>
			</div>
	<div class="data">

	  <!-- jump backward one page -->
      <xsl:choose>
        <xsl:when test="$path/guild/members/@page &gt; 1">
	      <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$path/guild/members/@page - 1});" class="uptab"></a>
	    </xsl:when>
	    <xsl:otherwise>
	      <a class="uptab-off"></a>
	    </xsl:otherwise>
	  </xsl:choose>

    <table class="data-table">
	<xsl:for-each select="$path/guild/members/character">
    <tr>
		<xsl:attribute name="class"><xsl:choose><xsl:when test="@selected">data2</xsl:when></xsl:choose></xsl:attribute>
      <td><div><p></p></div></td><td><a><strong></strong></a></td>
      <td><q>
      	<xsl:choose>
            <xsl:when test="@url">
                <a><strong>
                <xsl:call-template name="nameAbbreviator">
                    <xsl:with-param name="name" select="@name" />
                    <xsl:with-param name="link">character-sheet.xml?<xsl:value-of select="@url" /></xsl:with-param>
                </xsl:call-template>
                </strong></a>
	      	</xsl:when>
	      	<xsl:otherwise>
                <a><strong>
                <xsl:call-template name="nameAbbreviator">
                    <xsl:with-param name="name" select="@name" />
                </xsl:call-template>
                </strong></a>
	      	</xsl:otherwise>
      	</xsl:choose>
      </q></td>
      <td align="center">

		<xsl:variable name="raceIdStringGender" select="concat('armory.races.race.', @raceId,'.', @genderId)" />
        <xsl:variable name="classIdStringGender" select="concat('armory.classes.class.', @classId,'.', @genderId)" />
		
        <img onmouseover="setTipText('{$loc/strs/races/str[@id=$raceIdStringGender]}',100)" class="ci staticTip" src="/images/icons/race/{@raceId}-{@genderId}.gif" />
        <img src="/shared/wow-com/images/layout/pixel.gif" width = "2" />
        <img onmouseover="setTipText('{$loc/strs/classes/str[@id=$classIdStringGender]}',100)" class="ci staticTip" src="/images/icons/class/{@classId}.gif" />
      </td>
      
    </tr>
	</xsl:for-each>
    </table>

    <!-- jump forward one page -->
    <xsl:choose>
      <xsl:when test="$path/guild/members/@page &lt; $path/guild/members/@maxPage">
        <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$path/guild/members/@page + 1});" class="downtab"></a>
      </xsl:when>
      <xsl:otherwise>
        <a class="downtab-off"></a>
      </xsl:otherwise>
    </xsl:choose>

    <xsl:if test="$path/guild/members/@page">
      <xsl:call-template name="miniDefaultPageBar">
        <xsl:with-param name="currentPage" select="$path/guild/members/@page" />
        <xsl:with-param name="maxPage" select="$path/guild/members/@maxPage" />
      </xsl:call-template>
    </xsl:if>

	</div>
			</div>
		</div>
	</div><!--/end ps/-->

		</div><!--/end results-list/-->
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniArenaLadderTemplate">
	<xsl:param name="path"></xsl:param>
	<xsl:variable name="numArenaTeams" select="count($path/arenaTeams/arenaTeam)" />
	
	
	<!-- set the expanded search panel style -->
	<span style="display:none;">start</span><!--needed to fix IE bug that ignores script includes-->
	<link rel="stylesheet" type="text/css" media="screen, projection" href="css/mini-search-expand.css" />	
	
	<div class="results-side">
		<div class="results-list">
			
<!--	<div class="division">
		<a href = "#" onClick="resultsSideLeft(&quot;arena-ladder.xml?{$path/@url}&quot;); return false;" class="left-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.arena-ladder.expand']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
		<a href="javascript: resultsSideRight()" class="right-button" onmouseover="showTip('{$loc/strs/unsorted/str[@id='armory.mini-search.label.arena-ladder.collapse']}')" onmouseout="hideTip()"><img src="/images/pixel.gif" width="1" height="1" /></a>
	</div>-->
	

	
	<div class="ps">
		<div class="ps-bot">
			<div class="ps-top">
			<div class="result-banner">
			<h3 class="results-header"><em>
			<xsl:choose>
				<xsl:when test="$path/@teamSize = 2"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.mini-search.label.arena-ladder.2v2']"/></xsl:when>
				<xsl:when test="$path/@teamSize = 3"><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.mini-search.label.arena-ladder.3v3']"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="$loc/strs/unsorted/str[@id='armory.mini-search.label.arena-ladder.5v5']"/></xsl:otherwise>
			</xsl:choose>
			</em>
			</h3>
			</div>
	<div class="data">

	  <!-- jump backward one page -->
      <xsl:choose>
        <xsl:when test="$path/@page &gt; 1">
	      <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$path/@page - 1});" class="uptab"></a>
	    </xsl:when>
	    <xsl:otherwise>
	      <a class="uptab-off"></a>
	    </xsl:otherwise>
	  </xsl:choose>

	<div id="teamIconBoxContainer2">
		<div id="teamIconBoxresults"></div>
	</div>	  
	  
    <table class="data-table">
	
	
	<xsl:if test="$numArenaTeams &gt; 0">
		<script type="text/javascript">
		var flashVarsString="totalIcons=<xsl:value-of select="$numArenaTeams" />&#38;initScale=25&#38;overScale=75&#38;overModifierX=40&#38;overModifierY=0&#38;startPointX=3&#38;";
		</script>
	</xsl:if>		
	
	<xsl:for-each select="$path/arenaTeams/arenaTeam">
	
	<xsl:variable name="positionNum">
		<xsl:number value="position()" format="1" />
	</xsl:variable>

	<xsl:for-each select="emblem">
	<xsl:if test="$numArenaTeams &gt; 0">
		<script type="text/javascript">
		if(Browser.ie &amp;&amp; region == 'KR'){
			var teamUrl = unescape("<xsl:value-of select="../@url"/>").replace(/&amp;/g, "%26");
			flashVarsString+="iconName<xsl:value-of select="$positionNum"/>=images/icons/team/pvp-banner-emblem-<xsl:value-of select="@iconStyle"/>.png&#38;iconColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@iconColor"/>&#38;bgColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@background"/>&#38;borderColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@borderColor"/>&#38;teamUrl<xsl:value-of select="$positionNum"/>=team-info.xml?"+teamUrl +"&#38;";
		}
		else
		flashVarsString+="iconName<xsl:value-of select="$positionNum"/>=images/icons/team/pvp-banner-emblem-<xsl:value-of select="@iconStyle"/>.png&#38;iconColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@iconColor"/>&#38;bgColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@background"/>&#38;borderColor<xsl:value-of select="$positionNum"/>=<xsl:value-of select="@borderColor"/>&#38;teamUrl<xsl:value-of select="$positionNum"/>=team-info.xml?b=<xsl:value-of select="../@battleGroup"/>%26ts=<xsl:value-of select="../@size"/>%26t=<xsl:value-of select="../@name"/>&#38;";
		</script>
	</xsl:if>
	</xsl:for-each>	
	
    <tr onmouseover="popIconLarge('teamIconBoxresultsFlash','iconObject{$positionNum}')" onmouseout="popIconSmall('teamIconBoxresultsFlash','iconObject{$positionNum}')"><xsl:attribute name="class"><xsl:choose><xsl:when test="@selected">data2</xsl:when><xsl:when test="position() mod 2 = 0">data1</xsl:when><xsl:otherwise>data0</xsl:otherwise></xsl:choose></xsl:attribute>	
      <td><span class="mini-rank"><i><xsl:value-of select="@ranking"/></i></span></td>
      <td width="100%">
      	<xsl:choose>
            <xsl:when test="@url">
                <a><strong>
                <xsl:call-template name="miniSearchTeamNameAbbreviator">
                    <xsl:with-param name="name" select="@name" />
                    <xsl:with-param name="realm"><xsl:value-of select="$loc/strs/semicommon/str[@id='realm']"/><xsl:value-of select="$loc/strs/general/str[@id='colon']"/>&#160;<xsl:value-of select="@realm"/></xsl:with-param>
                    <xsl:with-param name="factionId" select="@factionId" />
                    <xsl:with-param name="link">team-info.xml?<xsl:value-of select="@url" /></xsl:with-param>
                </xsl:call-template>
                </strong></a>
	      	</xsl:when>
	      	<xsl:otherwise>
                <a><strong>
                <xsl:call-template name="miniSearchTeamNameAbbreviator">
                    <xsl:with-param name="name" select="@name" />
                    <xsl:with-param name="realm"><xsl:value-of select="$loc/strs/semicommon/str[@id='realm']"/><xsl:value-of select="$loc/strs/general/str[@id='colon']"/>&#160;<xsl:value-of select="@realm"/></xsl:with-param>
                    <xsl:with-param name="factionId" select="@factionId" />
                </xsl:call-template>
                </strong></a>
	      	</xsl:otherwise>
      	</xsl:choose>
      </td>
      <td><q class="filler"></q></td>
    </tr>
	</xsl:for-each>
    </table>

	<script type="text/javascript">
	var heightCalc=(<xsl:value-of select="$numArenaTeams" />*28)+10;
	if(heightCalc&lt;100)heightCalc=100;
	printFlash('teamIconBoxresults', '/images/icons/team/pvpemblems.swf', 'transparent', '', '#000000', '76', heightCalc, 'high', '', flashVarsString, '');
	</script>	
    <!-- jump forward one page -->
      <xsl:choose>
        <xsl:when test="$path/@page &lt; $path/@maxPage">
        <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$path/@page + 1});" class="downtab"></a>
      </xsl:when>
      <xsl:otherwise>
        <a class="downtab-off"></a>
      </xsl:otherwise>
    </xsl:choose>


	


    <xsl:call-template name="miniDefaultPageBar">
      <xsl:with-param name="currentPage" select="$path/@page" />
      <xsl:with-param name="maxPage" select="$path/@maxPage" />
    </xsl:call-template>

	
	</div>
			</div>
		</div>
	</div><!--/end ps/-->
	
		</div><!--/end results-list/-->
	</div><!--/end results-side/-->
</xsl:template>


<xsl:template name="miniSearchCharNameAbbreviator">
   <xsl:param name="name" />
   <xsl:param name="maxLength" select="$loc/strs/unsorted/str[@id='armory.mini-search.label.maxlength.arena']"/>
   <xsl:param name="realm" />
   <xsl:param name="guild" />
   <xsl:param name="link" />
   <xsl:choose>
     <xsl:when test="not(normalize-space($name))">
         &#160;
     </xsl:when>
     <xsl:otherwise>
       <a onmouseout="hideTip()">
         <xsl:attribute name="onmouseover">
           <xsl:choose>
             <!-- assume that if they have a guild then they have a realm -->
             <xsl:when test="string-length($guild) &gt; 0">
               showTip(&quot;&lt;b&gt;<xsl:value-of select="$name"/>&lt;/b&gt;&lt;br/&gt;&amp;lt;<xsl:value-of select="$guild"/>&amp;gt;&lt;br/&gt;<xsl:value-of select="$realm"/>&quot;)
             </xsl:when>
             <xsl:otherwise>
               showTip(&quot;&lt;b&gt;<xsl:value-of select="$name"/>&lt;/b&gt;&lt;br/&gt;<xsl:value-of select='$realm'/>&quot;)
             </xsl:otherwise>
           </xsl:choose>
         </xsl:attribute>
         <xsl:if test="$link">
           <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
         </xsl:if>
         <xsl:choose>
           <xsl:when test="string-length($name) &gt; $maxLength">
             <xsl:value-of select="substring($name,0,$maxLength )"/>...
           </xsl:when>
           <xsl:otherwise>
             <xsl:value-of select="$name" />
           </xsl:otherwise>
         </xsl:choose>
       </a>
     </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="miniSearchTeamNameAbbreviator">
   <xsl:param name="name" />
   <xsl:param name="maxLength" select="$loc/strs/unsorted/str[@id='armory.mini-search.label.maxlength.arena']"/>
   <xsl:param name="realm" />
   <xsl:param name="factionId" />
   <xsl:param name="link" />
   <xsl:choose>
     <xsl:when test="not(normalize-space($name))">
         &#160;
     </xsl:when>
     <xsl:otherwise>
       <a onmouseout="hideTip()">
         <xsl:attribute name="onmouseover">
           <xsl:choose>
             <!-- assume that if they have a guild then they have a realm -->
             <xsl:when test="$factionId = 0">
               showTip(&quot;&lt;b&gt;<xsl:value-of select="$name"/>&lt;/b&gt;&lt;br/&gt;<xsl:value-of select="$realm"/>&lt;br/&gt;&lt;a class='faction2 alliance'&gt;<xsl:value-of select="$loc/strs/semicommon/str[@id='alliance']"/>&lt;/a&gt;&quot;)
             </xsl:when>
             <xsl:when test="$factionId = 1">
               showTip(&quot;&lt;b&gt;<xsl:value-of select="$name"/>&lt;/b&gt;&lt;br/&gt;<xsl:value-of select="$realm"/>&lt;br/&gt;&lt;a class='faction2 horde'&gt;<xsl:value-of select="$loc/strs/semicommon/str[@id='horde']"/>&lt;/a&gt;&quot;)
             </xsl:when>
             <xsl:otherwise>
               showTip(&quot;&lt;b&gt;<xsl:value-of select="$name"/>&lt;/b&gt;&lt;br/&gt;<xsl:value-of select="$realm"/>&quot;)
             </xsl:otherwise>
           </xsl:choose>
         </xsl:attribute>
         <xsl:if test="$link">
           <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
         </xsl:if>
         <xsl:choose>
           <xsl:when test="string-length($name) &gt; $maxLength">
             <xsl:value-of select="substring($name,0,$maxLength )"/>...
           </xsl:when>
           <xsl:otherwise>
             <xsl:value-of select="$name" />
           </xsl:otherwise>
         </xsl:choose>
       </a>
     </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="miniSearchGuildNameAbbreviator">
   <xsl:param name="name" />
   <xsl:param name="maxLength" select="$loc/strs/unsorted/str[@id='armory.mini-search.label.maxlength']"/>
   <xsl:param name="realm" />
   <xsl:param name="link" />
   <xsl:choose>
     <xsl:when test="not(normalize-space($name))">
         &#160;
     </xsl:when>
     <xsl:otherwise>
       <a onmouseout="hideTip()" onmouseover="showTip(&quot;&lt;b&gt;{$name}&lt;/b&gt;&lt;br/&gt;{$realm}&quot;)">
         <xsl:if test="$link">
           <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
         </xsl:if>
         <xsl:choose>
           <xsl:when test="string-length($name) &gt; $maxLength">
             <xsl:value-of select="substring($name,0,$maxLength )"/>...
           </xsl:when>
           <xsl:otherwise>
             <xsl:value-of select="$name" />
           </xsl:otherwise>
         </xsl:choose>
       </a>
     </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="miniSearchItemNameAbbreviator">
   <xsl:param name="name" />
   <xsl:param name="maxLength" select="$loc/strs/unsorted/str[@id='armory.mini-search.label.maxlength']"/>
   <xsl:param name="itemId" />
   <xsl:param name="rarity" />
   <xsl:param name="link" />
   <xsl:choose>
     <xsl:when test="not(normalize-space($name))">
         &#160;
     </xsl:when>
     <xsl:otherwise>
       <a onmouseout="hideTip()" onmouseover="loadTooltip(&quot;{$loc/strs/unsorted/str[@id='armory.character-sheet.ttip.loading']}&quot;, {$itemId})">
         <xsl:if test="$link">
           <xsl:attribute name="href"><xsl:value-of select="$link" /></xsl:attribute>
         </xsl:if>
         <xsl:if test="$rarity">
           <xsl:attribute name="class">rarity<xsl:value-of select="$rarity" /></xsl:attribute>
         </xsl:if>
         <xsl:choose>
           <xsl:when test="string-length($name) &gt; $maxLength">
             <xsl:value-of select="substring($name,0,$maxLength )"/>...
           </xsl:when>
           <xsl:otherwise>
             <xsl:value-of select="$name" />
           </xsl:otherwise>
         </xsl:choose>
       </a>
     </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="miniSearchPageBar">
	<xsl:param name="path"></xsl:param>

    <div class="mnav">
    <ul>
      <!-- first page -->
      <li>
      <xsl:choose>
        <xsl:when test="$path/@pageCurrent = 1">
          <a class="prev-first-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setSearchPageNumber(1);" class="prev-first"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- prev page -->
      <li>
      <xsl:choose>
        <xsl:when test="$path/@pageCurrent &lt;= 1">
          <a class="prev-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setSearchPageNumber({$path/@pageCurrent - 1});" class="prev"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- next page -->
      <li>
      <xsl:choose>
        <xsl:when test="$path/@pageCurrent &gt;= $path/@pageCount">
          <a class="next-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setSearchPageNumber({$path/@pageCurrent + 1});" class="next"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- last page -->
      <li>
      <xsl:choose>
        <xsl:when test="$path/@pageCurrent &gt;= $path/@pageCount">
          <a class="next-last-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setSearchPageNumber({$path/@pageCount});" class="next-last"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>
    </ul>
    </div>
</xsl:template>

<xsl:template name="miniDefaultPageBar">
  <xsl:param name="maxPage">50</xsl:param>
  <xsl:param name="currentPage">10</xsl:param>

    <div class="mnav">
    <ul>
      <!-- first page -->
      <li>
      <xsl:choose>
        <xsl:when test="$currentPage = 1">
          <a class="prev-first-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber(1);" class="prev-first"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- prev page -->
      <li>
      <xsl:choose>
        <xsl:when test="$currentPage &lt;= 1">
          <a class="prev-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$currentPage - 1});" class="prev"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- next page -->
      <li>
      <xsl:choose>
        <xsl:when test="$currentPage &gt;= $maxPage">
          <a class="next-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$currentPage + 1});" class="next"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>

      <!-- last page -->
      <li>
      <xsl:choose>
        <xsl:when test="$currentPage &gt;= $maxPage">
          <a class="next-last-off"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:when>
        <xsl:otherwise>
          <a href="javascript:miniSearchPanelInstance.setDefaultPageNumber({$maxPage});" class="next-last"><img src="/images/pixel.gif" width="1" height="1" /></a>
        </xsl:otherwise>
      </xsl:choose>
      </li>
    </ul>
    </div>

</xsl:template>


</xsl:stylesheet>
