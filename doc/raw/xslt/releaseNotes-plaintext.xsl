<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- 
 @package concerto.docs
 
 @copyright Copyright &copy; 2005, Middlebury College
 @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 
 @version $Id: releaseNotes-plaintext.xsl,v 1.1 2007/10/09 18:47:56 adamfranco Exp $
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="trim.xsl"/>
<xsl:import href="paragraphs.xsl"/>
<xsl:output format="text" />
<xsl:strip-space elements="fix change new important" />
<!--
///////////////////////////////////////////////////////////////////////
// Release notes
///////////////////////////////////////////////////////////////////////
-->
<xsl:template match="changelog">
<xsl:variable name="currentVersion" select='./version'/>
<xsl:value-of select="@name" /> v. <xsl:value-of select="$currentVersion/@number" /><xsl:if test="$currentVersion/@date!=''"> (<xsl:value-of select="$currentVersion/@date" />)</xsl:if>
<xsl:text>
=================================
</xsl:text>

<xsl:text>
What is </xsl:text><xsl:value-of select="@name" /><xsl:text>?
------------------
</xsl:text>
<xsl:variable name='aboutText'>
	<xsl:call-template name="addNewlines">
		<xsl:with-param name="maxCharacters" select="84"/>
		<xsl:with-param name="remainingString">
			<xsl:call-template name="singleLineParagraphs">
				<xsl:with-param name="s" select="about"/>
			</xsl:call-template>
		</xsl:with-param>
	</xsl:call-template>
</xsl:variable>
<xsl:value-of select='$aboutText' disable-output-escaping='yes'/>
<xsl:text>


Current Version Notes
---------------------
</xsl:text>
<xsl:variable name='currentText'>
	<xsl:call-template name="addNewlines">
		<xsl:with-param name="maxCharacters" select="84"/>
		<xsl:with-param name="remainingString">
			<xsl:call-template name="singleLineParagraphs" >
				<xsl:with-param name="s" select="$currentVersion/releaseNotes"/>
			</xsl:call-template>
		</xsl:with-param>
	</xsl:call-template>
</xsl:variable>
<xsl:value-of select='$currentText' disable-output-escaping='yes'/>

<!-- Info Items -->
<xsl:for-each select="info">
<xsl:text>


</xsl:text>
<xsl:value-of select="title" />
<xsl:text>
---------------------
</xsl:text>
<xsl:if test="description!=''">
	<xsl:variable name='descriptionText'>
		<xsl:call-template name="addNewlines">
			<xsl:with-param name="maxCharacters" select="84"/>
			<xsl:with-param name="remainingString">
				<xsl:call-template name="singleLineParagraphs">
					<xsl:with-param name="s" select="description"/>
				</xsl:call-template>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:variable>
	<xsl:value-of select='$descriptionText' disable-output-escaping='yes'/>
<xsl:text>

</xsl:text>
</xsl:if>
<xsl:value-of select="url" disable-output-escaping='yes'/>

</xsl:for-each>







===================================================================
| Prior <xsl:value-of select="@name" /> Release Notes
| (See the <xsl:value-of select="@name" /> change log for more details)
===================================================================

<xsl:for-each select="version">
v. <xsl:value-of select="@number" /><xsl:if test="@date!=''"> (<xsl:value-of select="@date" />)</xsl:if>
----------------------------------------------------
<xsl:variable name='notesText'>
	<xsl:call-template name="addNewlines">
		<xsl:with-param name="maxCharacters" select="84"/>
		<xsl:with-param name="remainingString">
			<xsl:call-template name="singleLineParagraphs">
				<xsl:with-param name="s" select="releaseNotes"/>
			</xsl:call-template>
		</xsl:with-param>
	</xsl:call-template>
</xsl:variable>
<xsl:value-of select='$notesText' disable-output-escaping='yes'/>
<xsl:text>


</xsl:text>
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>
