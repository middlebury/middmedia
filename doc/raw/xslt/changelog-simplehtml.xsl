<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- 
 @package concerto.docs
 
 @copyright Copyright &copy; 2005, Middlebury College
 @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 
 @version $Id: changelog-simplehtml.xsl,v 1.6 2007/10/22 15:38:30 adamfranco Exp $
 -->
 
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="trim.xsl"/>
<xsl:import href="paragraphs.xsl"/>
<!--
///////////////////////////////////////////////////////////////////////
// changelog
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="changelog">

<html>
	<head>
		<style type="text/css">
			body {
				font-family: Verdana; font-size: 12px;
			}
			
			h1, h2 {
				color: #005;
			}
			
			h1 {
				font-size: 18pt;
			}
			
			li {
				padding-bottom: 3px;
			}
			
		</style>
		<title><xsl:value-of select="@name" /> Change Log</title>

	</head>
	<body>
		<h1><xsl:value-of select="@name" /> Change Log</h1>
	
	
<xsl:for-each select="version">
		<h2>Version <xsl:value-of select="@number" /></h2>
		<xsl:if test="@date!=''"><h3><xsl:value-of select="@date" /></h3></xsl:if>
		
		<xsl:choose>
			<xsl:when test="string-length(releaseNotes)">
				<p>
				<xsl:call-template name="nl2br">
					<xsl:with-param name="stringIn">
						<xsl:call-template name="singleLineParagraphs">
							<xsl:with-param name="s" select="releaseNotes"/>
						</xsl:call-template>
					</xsl:with-param>
				</xsl:call-template>
				
				</p>
			</xsl:when>
		</xsl:choose>

		<ul>
			<xsl:apply-templates />
		</ul>
		<br />
</xsl:for-each>

	</body>
</html>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// fix
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="fix">
	<li> <b>Bug Fix:</b> <xsl:call-template name="entry" /></li>	
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// change
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="change">
	<li> <b>Change:</b> <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// new
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="new">
	<li> <b>New feature:</b> <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// important
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="important">
	<li> <span style='color: red'>*** IMPORTANT ***</span> <b>Change:</b> <xsl:call-template name="entry" /></li>
</xsl:template>

<!--
///////////////////////////////////////////////////////////////////////
// entry
///////////////////////////////////////////////////////////////////////
-->
<xsl:template name="entry">
	<xsl:if test="@ref != ''">
		<xsl:choose>
			<xsl:when test="@reftype">
				<xsl:variable name="reftype" select="@reftype" />
				<xsl:variable name="tracker" select="//reftypes/reftype[@name = $reftype]" />
        		<a>
        			<xsl:attribute name="href">
        				http://sourceforge.net/tracker/index.php?func=detail&amp;aid=<xsl:value-of select="@ref" />&amp;group_id=<xsl:value-of select="$tracker/@group" />&amp;atid=<xsl:value-of select="$tracker/@tracker" />
        			</xsl:attribute>
        			#<xsl:value-of select="@ref" />
        		</a>
			</xsl:when>
			<xsl:otherwise>
				#<xsl:value-of select="@ref" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
	<xsl:text> </xsl:text>
	<xsl:call-template name="nl2br">
		<xsl:with-param name="stringIn">
			<xsl:call-template name="singleLineParagraphs">
				<xsl:with-param name="s" select="."/>
			</xsl:call-template>
		</xsl:with-param>
	</xsl:call-template>
	<xsl:if test="@author">
		<xsl:text> (</xsl:text>
		<em>
			
			<xsl:if test="@date!=''"><xsl:value-of select="@date" /> - </xsl:if>
			
			<xsl:call-template name="authors">
				<xsl:with-param name="str" select="@author"/>
			</xsl:call-template>
		</em>
		<xsl:text>)</xsl:text>
	</xsl:if>
	
	<xsl:if test="@reporter != ''">
		<br/>
		<xsl:text> (</xsl:text>
		<em>
		<xsl:text>reported by </xsl:text>		
		<xsl:call-template name="reporters">
			<xsl:with-param name="str" select="@reporter"/>
		</xsl:call-template>
		
		</em>
		<xsl:text>)</xsl:text>
	</xsl:if>
</xsl:template>

<xsl:template name="authors">
	<xsl:param name="str"/>
	<xsl:choose>
		<xsl:when test="contains($str,',')">
			<xsl:call-template name="authors">
				<xsl:with-param name="str" select="substring-before($str,',')"/>
			</xsl:call-template>			
			<xsl:text>, </xsl:text>
			<xsl:call-template name="authors">
				<xsl:with-param name="str" select="substring-after($str,',')"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:variable name="author">
				<xsl:call-template name="trim">
					<xsl:with-param name="s" select="$str"/>
				</xsl:call-template>
			</xsl:variable>
		
			<xsl:choose>
				<xsl:when test="//authors/name[@short=$author]">
					<xsl:value-of select="//authors/name[@short=$author]" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$author" />
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="reporters">
	<xsl:param name="str"/>
	
	<xsl:choose>
	<xsl:when test="contains($str,',')">
		<xsl:call-template name="reporters">
			<xsl:with-param name="str" select="substring-before($str,',')"/>
		</xsl:call-template>
		<xsl:text>, </xsl:text>
		<xsl:call-template name="reporters">
			<xsl:with-param name="str" select="substring-after($str,',')"/>
		</xsl:call-template>
	</xsl:when>
	<xsl:otherwise>
		<xsl:variable name="reporter">
			<xsl:call-template name="trim">
				<xsl:with-param name="s" select="$str"/>
			</xsl:call-template>
		</xsl:variable>
	
		<xsl:choose>
			<xsl:when test="//reporters/reporter[@short=$reporter]">
				<xsl:value-of select="//reporters/reporter[@short=$reporter]/name" />
				<xsl:choose>
					<xsl:when test="//reporters/reporter[@short=$reporter]/institution">
						 <xsl:text> of </xsl:text>
						<xsl:value-of select="//reporters/reporter[@short=$reporter]/institution" />
					</xsl:when>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$reporter" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- Ignore the releaseNotes data as we've already used it. -->
<xsl:template match="releaseNotes"></xsl:template>

</xsl:stylesheet>
