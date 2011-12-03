<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
  <xsl:output indent="yes" method="html" />
  <xsl:template match="/page/characterInfo">
    <div class="moduleContent" style="width:100%;">
      <xsl:call-template name="character"/>
    </div>
	</xsl:template>
	
	<xsl:template name="character">
    <center>
     <strong>
       <a target="_blank"><xsl:attribute name="href">http://eu.wowarmory.com/character-sheet.xml?r=<xsl:value-of select="character/@realm"/>&amp;n=<xsl:value-of select="character/@name"/>&amp;locale=fr</xsl:attribute><xsl:value-of select="character/@name"/></a> (<xsl:value-of select="character/@level"/>, <xsl:value-of select="character/@guildName"/>, <i><xsl:value-of select="character/@lastModified"/></i>)
     </strong>
    </center>
	  <img border="0" ><xsl:attribute name="src">http://www.wowarmory.com/images/icons/race/<xsl:value-of select="character/@raceId" />-0.gif</xsl:attribute></img>&#160;<xsl:value-of select="character/@race"/>&#160;<xsl:value-of select="character/@gender"/>&#160; 
	  <img border="0" ><xsl:attribute name="src">http://www.wowarmory.com/images/icons/class/<xsl:value-of select="character/@classId" />.gif</xsl:attribute></img>&#160;<xsl:value-of select="character/@class"/>&#160; 
	  <a target="_blank"><xsl:attribute name="href">http://eu.wowarmory.com/character-talents.xml?r=<xsl:value-of select="character/@realm"/>&amp;n=<xsl:value-of select="character/@name"/>&amp;locale=fr</xsl:attribute>
	    <xsl:value-of select="characterTab/talentSpec/@treeOne"/>,<xsl:value-of select="characterTab/talentSpec/@treeTwo"/>,<xsl:value-of select="characterTab/talentSpec/@treeThree"/>
	  </a> <br />
	
	  <strong>Vie : </strong><xsl:value-of select="characterTab/characterBars/health/@effective"/>&#160;
	  <strong>M/E/R : </strong><xsl:value-of select="characterTab/characterBars/secondBar/@effective"/> <br />
	  
	  <strong>Basic Item Score</strong> : <xsl:value-of select="sum(//item[@onList=1]/@itemLevel)" /> (<xsl:value-of select="format-number(sum(//item[@onList=1]/@itemLevel) div count (//item[@onList=1]/@itemLevel),'#.00')" />)<br />
	  
		<strong>Hauts faits</strong> : <xsl:value-of select="character/@points"/> <br />
	  <xsl:for-each select="characterTab/professions/skill">
	    <strong><xsl:value-of select="@name"/> : </strong> <xsl:value-of select="@value"/>/<xsl:value-of select="@max"/>&#160; 
	  </xsl:for-each>
	  <div style="height:200px;overflow:auto;">
		  <table border="0" cellpadding="2" cellspacing="3" width="100%">
		    <tbody>
		      <tr>
		        <td>
		          <u><b>Resistance</b></u> <br />
		          <strong>Arcane: </strong><xsl:value-of select="characterTab/resistances/arcane/@value"/><br />
		          <strong>Feu: </strong><xsl:value-of select="characterTab/resistances/fire/@value"/><br />
		          <strong>Givre: </strong><xsl:value-of select="characterTab/resistances/frost/@value"/><br />
		          <strong>Sacre: </strong><xsl:value-of select="characterTab/resistances/holy/@value"/><br />
		          <strong>Nature : </strong><xsl:value-of select="characterTab/resistances/nature/@value"/><br />
		          <strong>Ombre : </strong><xsl:value-of select="characterTab/resistances/shadow/@value"/><br />
		        </td>        
		        <td>
		          <u><b>Base</b></u> <br />
		          <strong>Force: </strong><xsl:value-of select="characterTab/baseStats/strength/@effective"/><br />
		          <strong>Agilite: </strong><xsl:value-of select="characterTab/baseStats/agility/@effective"/><br />
		          <strong>Endurance: </strong><xsl:value-of select="characterTab/baseStats/stamina/@effective"/><br />
		          <strong>Intelligence: </strong><xsl:value-of select="characterTab/baseStats/intellect/@effective"/><br />
		          <strong>Esprit: </strong><xsl:value-of select="characterTab/baseStats/spirit/@effective"/><br />
		          <strong>Armure: </strong><xsl:value-of select="characterTab/baseStats/armor/@effective"/><br />
		        </td>
		      </tr>
		      <tr>
		        <td>
		          <u><b>Defense</b></u> <br />
		          <strong>Armure: </strong><xsl:value-of select="characterTab/defenses/armor/@effective"/><br />
		          <strong>Defense: </strong><xsl:value-of select="characterTab/defenses/defense/@value"/><br />
		          <strong>Sc.Toucher: </strong><xsl:value-of select="characterTab/melee/hitRating/@value"/><br />
		          <strong>Critiques: </strong><xsl:value-of select="characterTab/melee/critChance/@percent"/><br />
		          <strong>Blocag. : </strong><xsl:value-of select="characterTab/defenses/block/@rating"/><br />
		          <strong>Resilience: </strong><xsl:value-of select="characterTab/defenses/resilience/@value"/><br />
		        </td>        
		        <td>
		          <u><b>Melee</b></u> <br />
		          <strong>Degats: </strong><xsl:value-of select="characterTab/melee/mainHandDamage/@min"/>-<xsl:value-of select="characterTab/melee/mainHandDamage/@max"/><br />
		          <strong>Vitesse: </strong><xsl:value-of select="characterTab/melee/mainHandSpeed/@value"/><br />
		          <strong>Puissance: </strong><xsl:value-of select="characterTab/melee/power/@effective"/><br />
		          <strong>Sc.toucher: </strong><xsl:value-of select="characterTab/melee/hitRating/@value"/><br />
		          <strong>Critiques : </strong><xsl:value-of select="characterTab/melee/critChance/@percent"/><br />
		          <strong>Expertises: </strong><xsl:value-of select="characterTab/melee/expertise/@value"/><br />
		        </td>        
		      </tr>
		      <tr>
		        <td>
		          <u><b>A distance</b></u> <br />
		          <strong>Degats: </strong><xsl:value-of select="characterTab/ranged/damage/@min"/>-<xsl:value-of select="characterTab/ranged/damage/@max"/><br />
		          <strong>Vitesse: </strong><xsl:value-of select="characterTab/ranged/speed/@value"/><br />
		          <strong>Puissance: </strong><xsl:value-of select="characterTab/ranged/power/@effective"/><br />
		          <strong>Sc.toucher: </strong><xsl:value-of select="characterTab/ranged/hitRating/@value"/><br />
		          <strong>Critiques : </strong><xsl:value-of select="characterTab/ranged/critChance/@percent"/><br />
		        </td>        
		        <td>
		          <u><b>Sortileges</b></u> <br />
		          <strong>Bon. Degats/Soins: </strong><xsl:value-of select="characterTab/spell/bonusHealing/@value"/><br />
		          <strong>CritChance: </strong><xsl:value-of select="characterTab/spell/critChance/@rating"/><br />
		          <strong>Sc.toucher: </strong><xsl:value-of select="characterTab/spell/hitRating/@value"/><br />
		          <strong>Score de hate: </strong><xsl:value-of select="characterTab/spell/hasteRating/@hasteRating"/>(<xsl:value-of select="characterTab/spell/hasteRating/@hastePercent"/>%)<br />
		          <strong>Regen. mana : </strong><xsl:value-of select="characterTab/spell/manaRegen/@casting"/>-<xsl:value-of select="characterTab/spell/manaRegen/@notCasting"/><br />
		          <strong>Penetration : </strong><xsl:value-of select="characterTab/spell/penetration/@value"/><br />
		        </td>        
		      </tr>
		    </tbody>
	    </table>
	  </div>
	</xsl:template>
</xsl:stylesheet>
