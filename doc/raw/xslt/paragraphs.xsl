<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!-- 
Create single-line paragraphs from tabbed and line-returned text by making
double-returns into the paragraph breaks

===========
Ex Input:
===========
	Aliquam et nunc vitae elit ullamcorper 
	fringilla. 
	
	Quisque sed odio. Nullam posuere
	eleifend leo. Proin adipiscing nunc 
	et pede. 
===========
Ex Output
===========
Aliquam et nunc vitae elit ullamcorper fringilla. 

Quisque sed odio. Nullam posuere eleifend leo. Proin adipiscing nunc et pede. 
===========

@author Adam Franco
@package harmoni.docs
@copyright Copyright &copy; 2007, Middlebury College
@license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
@version $Id: paragraphs.xsl,v 1.1 2007/10/09 18:47:56 adamfranco Exp $

-->

<xsl:template name='singleLineParagraphs'>
    <xsl:param name="s"/>
    
	<!-- Strip out any tab characters -->
	<xsl:variable name="s1">
		<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s"/>
			<xsl:with-param name="substringIn" select="'&#x09;'"/>
			<xsl:with-param name="substringOut" select="''"/>
		</xsl:call-template>
	</xsl:variable>
    
    <!-- Convert any Horizontal Rules to placeholders -->
	<xsl:variable name="s1a">
    	<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s1"/>
			<xsl:with-param name="substringIn" select="'&#x0A;----&#x0A;'"/>
			<xsl:with-param name="substringOut" select="'##HR##'"/>
		</xsl:call-template>
    </xsl:variable>
	
	<!-- Convert double new-lines to placeholders -->
    <xsl:variable name="s2">
    	<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s1a"/>
			<xsl:with-param name="substringIn" select="'&#x0A;&#x0A;'"/>
			<xsl:with-param name="substringOut" select="'##PARA##'"/>
		</xsl:call-template>
    </xsl:variable>
	
	<!-- Convert any '*' list-markers to placeholders -->
	<xsl:variable name="s2a">
    	<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s2"/>
			<xsl:with-param name="substringIn" select="'&#x0A;*'"/>
			<xsl:with-param name="substringOut" select="'##LIST_ITEM##'"/>
		</xsl:call-template>
    </xsl:variable>
    
    <!-- Convert any ' *' list-markers to placeholders -->
	<xsl:variable name="s2b">
    	<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s2a"/>
			<xsl:with-param name="substringIn" select="'&#x0A; *'"/>
			<xsl:with-param name="substringOut" select="'##LIST_ITEM##'"/>
		</xsl:call-template>
    </xsl:variable>
	
	 <!-- remove any remaining new-lines -->
    <xsl:variable name="s3">
	    <xsl:value-of select="normalize-space(translate($s2b,'&#x0A;',' '))" />
    </xsl:variable>
    
    <!-- Put back the double newlines -->
	<xsl:variable name="s4">
	    <xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s3"/>
			<xsl:with-param name="substringIn" select="'##PARA##'"/>
			<xsl:with-param name="substringOut" select="'&#x0A;&#x0A;'"/>
		</xsl:call-template>
	</xsl:variable>	
	
	<xsl:variable name="s5">
		<xsl:call-template name="trim">
			<xsl:with-param name="s" select="$s4"/>
		</xsl:call-template>
	</xsl:variable>
	
	<!-- Put back the list-items -->
	<xsl:variable name="s6">
		<xsl:call-template name="SubstringReplace">
			<xsl:with-param name="stringIn" select="$s5"/>
			<xsl:with-param name="substringIn" select="'##LIST_ITEM##'"/>
			<xsl:with-param name="substringOut" select="'&#x0A;&#x0A; *'"/>
		</xsl:call-template>
	</xsl:variable>
	
	<!-- Put back in the HRs -->
	<xsl:call-template name="SubstringReplace">
		<xsl:with-param name="stringIn" select="$s6"/>
		<xsl:with-param name="substringIn" select="'##HR##'"/>
		<xsl:with-param name="substringOut" select="'&#x0A;&#x0A;----&#x0A;&#x0A;'"/>
	</xsl:call-template>
</xsl:template>

</xsl:stylesheet>