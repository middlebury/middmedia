<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!--
  whitespace trimming demonstration
  by Mike J. Brown

  last updated 2004-03-03

  no license; use freely

  ltrim = remove leading whitespace
  rtrim = remove trailing whitespace
  trim = remove both leading and trailing whitespace

  ...for when XPath's normalize-space() is too much!

  Also see the similar functions described in http://fxsl.sourceforge.net/
-->
<xsl:template name='trim'>
    <xsl:param name="s"/>
    
    <!--
      for trim, just rtrim the ltrimmed string
    -->
    <xsl:call-template name="rtrim">
      <xsl:with-param name="s" select="concat(substring(translate($s,' &#9;&#10;&#13;',''),1,1),substring-after($s,substring(translate($s,' &#9;&#10;&#13;',''),1,1)))"/>
    </xsl:call-template>
</xsl:template>

  <!--
    the placement of the recursive call allows the processor to
    optimize tail recursion. Not all processors optimize, though.
  -->
<xsl:template name="rtrim">
	<xsl:param name="s"/>
	<xsl:param name="i" select="string-length($s)"/>
	<xsl:choose>
	  <xsl:when test="translate(substring($s,$i,1),' &#9;&#10;&#13;','')">
		<xsl:value-of select="substring($s,1,$i)"/>
	
	  </xsl:when>
	  <xsl:when test="$i&lt;2"/>
	  <xsl:otherwise>
		<xsl:call-template name="rtrim">
		  <xsl:with-param name="s" select="$s"/>
		  <xsl:with-param name="i" select="$i - 1"/>
		</xsl:call-template>
	  </xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!--=======================================================================

	replace.xsl

	This is an example of using a named template to do substring
	replacement. It relies on tail recursion, in order to be XSLT 1.0
	conformant. This template can be used when the XPath translate()
	function, which operates on single characters in a string, is
	insufficient. It uses the XPath functions substring-before(),
	substring-after(), and concat(), and is quite simple.

	Each time the named template is invoked, it is given a string to
	perform the replacement on, a substring to find and replace, and a
	replacement string. It produces a result tree fragment containing a
	text node with the first instance of the matched string having been
	replaced.
	
	If there was any text after that first match, the template calls
	itself again, but makes the string to perform the replacement on be
	just the text after the first match. When the end of the string is
	reached, the template won't call itself again.
	
	The series of text nodes that it produces during this process are
	automatically concatenated into one text node, which you can leave
	in the result tree fragment for most situations. If it is
	necessary to have an actual string object, use the string()
	function to use the string-value of the result tree fragment.

	Written by: Mike J. Brown <mike@skew.org>
	License: none; use and distribute freely.

	Version 1.0 - 10 Nov 2000: First public version. XSLT 1.0 conformant.

=======================================================================-->
	<xsl:template name="SubstringReplace">
		<xsl:param name="stringIn"/>
		<xsl:param name="substringIn"/>
		<xsl:param name="substringOut"/>
		<xsl:choose>

			<xsl:when test="contains($stringIn,$substringIn)">
				<xsl:value-of select="concat(substring-before($stringIn,$substringIn),$substringOut)"/>
				<xsl:call-template name="SubstringReplace">
					<xsl:with-param name="stringIn" select="substring-after($stringIn,$substringIn)"/>
					<xsl:with-param name="substringIn" select="$substringIn"/>
					<xsl:with-param name="substringOut" select="$substringOut"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>

				<xsl:value-of select="$stringIn"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="nl2br">
		<xsl:param name="stringIn"/>
		<xsl:choose>

			<xsl:when test="contains($stringIn,'&#x0A;')">
				<xsl:value-of select="substring-before($stringIn, '&#x0A;')"/>
				<xsl:text>&#x0A;</xsl:text><br/>
				<xsl:call-template name="nl2br">
					<xsl:with-param name="stringIn" select="substring-after($stringIn,'&#x0A;')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>

				<xsl:value-of select="$stringIn"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	
	
	<xsl:template name="addNewlines">
		<xsl:param name="completedLines" />
		<xsl:param name="currentLine" />
		<xsl:param name="remainingString" />
		<xsl:param name="maxCharacters" />
		<xsl:param name="tabs" />
		
		<xsl:variable name="lineStart">
			<xsl:choose>
					<!-- First Line -->
					<xsl:when test="not(string-length($completedLines))">
						<xsl:value-of select="''"/>
					</xsl:when>
					<!-- Remaining lines. -->
					<xsl:otherwise>
						<xsl:value-of select="concat('&#x0A;',$tabs)"/>
					</xsl:otherwise>
				</xsl:choose>
		</xsl:variable>
		<xsl:variable name="currentLineLength" select="string-length($currentLine)"/>
		<xsl:variable name="remainingStringLength" select="string-length($remainingString)"/>
		
		<xsl:choose>
			<!-- Return when we have no more breaks to add -->
			<xsl:when test="(($currentLineLength + $remainingStringLength) &lt; $maxCharacters) or not($remainingStringLength)">
				
				<xsl:choose>
					<!-- If we don't have any completed lines just add ours -->
					<xsl:when test="not(string-length($completedLines))">
						<xsl:value-of select="concat($lineStart, $currentLine, $remainingString)"/>
					</xsl:when>
					<!-- Otherwise add ours to the already completed ones -->
					<xsl:otherwise>
						<xsl:value-of select="concat($completedLines, $lineStart, $currentLine, '&#x20;', $remainingString)"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			
			<!-- Otherwise, add the next word and continue -->
			<xsl:otherwise>
				<xsl:variable name="tmpNextWord">
					<xsl:choose>
						<!-- Normal Case -->
						
						<xsl:when test="contains($remainingString, '&#x20;')">
							<xsl:value-of name="tmpNextWord" select="substring-before($remainingString, '&#x20;')"/>
						</xsl:when>
						<!-- If we only have one word remaining, it is our next word -->
						<xsl:otherwise>
							<xsl:value-of name="tmpNextWord" select="$remainingString"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<xsl:variable name="nextWord">
					<xsl:choose>
						<!-- if we have a existing newline, force-create a new line -->
						<xsl:when test="contains($tmpNextWord, '&#x0A;')">
							<xsl:value-of name="nextWord" select="substring-before($tmpNextWord, '&#x0A;')"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of name="nextWord" select="$tmpNextWord"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<xsl:variable name="nextLineBegin">
					<xsl:choose>
						<!-- if we have a existing newline, force-create a new line -->
						<xsl:when test="contains($tmpNextWord, '&#x0A;')">
							<xsl:call-template name="SubstringReplace">
								<xsl:with-param name="stringIn" select="substring-after($tmpNextWord, '&#x0A;')"/>
								<xsl:with-param name="substringIn" select="'&#x0A;'"/>
								<xsl:with-param name="substringOut" select="concat('&#x0A;', $tabs)"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of name="nextLineBegin" select="''"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				
				<xsl:variable name="nextWordLength" select="1 + string-length($nextWord)"/>
				<xsl:variable name="newRemainingString" select="substring-after($remainingString, '&#x20;')"/>
				
				<xsl:choose>
					<!-- If we don't have a current line, start it. -->
					<xsl:when test="not($currentLineLength)">
						<xsl:call-template name="addNewlines">
							<!-- Add the current lines to the completed lines -->
							<xsl:with-param name="completedLines" select="$completedLines"/>
							<xsl:with-param name="currentLine" select="$nextWord"/>
							<xsl:with-param name="tabs" select="$tabs"/>
							<xsl:with-param name="remainingString" select="$newRemainingString"/>
							<xsl:with-param name="maxCharacters" select="$maxCharacters"/>
						</xsl:call-template>
					</xsl:when>
					
					<!-- If we have a manual line break, add it -->
					<xsl:when test="string-length($nextLineBegin)">
						<xsl:call-template name="addNewlines">
							<!-- Add the current lines to the completed lines -->
							<xsl:with-param name="completedLines">
								<xsl:choose>
									<!-- If we don't have any completed lines just add ours -->
									<xsl:when test="not(string-length($completedLines))">
										<xsl:value-of select="concat($currentLine, '&#x20;', $nextWord)"/>
									</xsl:when>
									<!-- Otherwise add ours to the already completed ones -->
									<xsl:otherwise>
										<xsl:value-of select="concat($completedLines, $lineStart, concat($currentLine, '&#x20;', $nextWord))"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:with-param>
							<xsl:with-param name="currentLine" select="$nextLineBegin"/>
							<xsl:with-param name="remainingString" select="$newRemainingString"/>
							<xsl:with-param name="tabs" select="$tabs"/>
							<xsl:with-param name="maxCharacters" select="$maxCharacters"/>
						</xsl:call-template>
					</xsl:when>
					
					<!-- If adding the next word would push us over the max characters -->
					<!-- Add a line break and continue. -->
					<xsl:when test="($currentLineLength + $nextWordLength) &gt; $maxCharacters">
						<xsl:call-template name="addNewlines">
							<!-- Add the current lines to the completed lines -->
							<xsl:with-param name="completedLines">
								<xsl:choose>
									<!-- If we don't have any completed lines just add ours -->
									<xsl:when test="not(string-length($completedLines))">
										<xsl:value-of select="$currentLine"/>
									</xsl:when>
									<!-- Otherwise add ours to the already completed ones -->
									<xsl:otherwise>
										<xsl:value-of select="concat($completedLines, $lineStart, $currentLine)"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:with-param>
							<xsl:with-param name="currentLine" select="$nextWord"/>
							<xsl:with-param name="remainingString" select="$newRemainingString"/>
							<xsl:with-param name="tabs" select="$tabs"/>
							<xsl:with-param name="maxCharacters" select="$maxCharacters"/>
						</xsl:call-template>
					</xsl:when>
					
					<!-- Otherwise, add the next word to the current line and continue -->
					<xsl:otherwise>
						<xsl:call-template name="addNewlines">
							<xsl:with-param name="completedLines" select="$completedLines"/>
							<xsl:with-param name="currentLine" select="concat($currentLine, '&#x20;', $nextWord)"/>
							<xsl:with-param name="remainingString" select="$newRemainingString"/>
							<xsl:with-param name="tabs" select="$tabs"/>
							<xsl:with-param name="maxCharacters" select="$maxCharacters"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
