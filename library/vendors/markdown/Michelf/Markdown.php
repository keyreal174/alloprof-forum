<?php

#
#
#           DO NOT UPDATE THIS FILE
#           DO NOT BRING IN A NEW VERSION OF THIS LIBRARY
#           VANILLA CHANGES WILL BE LOST
#
#           Please see /library/core/class.markdownvanilla.php
#
#

#
# Markdown  -  A text-to-HTML conversion tool for web writers
#
# PHP Markdown
# Copyright (c) 2004-2014 Michel Fortin
# <http://michelf.com/projects/php-markdown/>
#
# Original Markdown
# Copyright (c) 2004-2006 John Gruber
# <http://daringfireball.net/projects/markdown/>
#
namespace Michelf;


#
# Markdown Parser Class
#

class Markdown implements MarkdownInterface {

	### Version ###

	const  MARKDOWNLIB_VERSION  =  "1.4.1";

	### Simple Function Interface ###

	public static function defaultTransform($text) {
	#
	# Initialize the parser and return the result of its transform method.
	# This will work fine for derived classes too.
	#
		# Take parser class on which this function was called.
		$parser_class = \get_called_class();

		# try to take parser from the static parser list
		static $parser_list;
		$parser =& $parser_list[$parser_class];

		# create the parser it not already set
		if (!$parser)
			$parser = new $parser_class;

		# Transform text using parser.
		return $parser->transform($text);
	}

	### Configuration Variables ###

	# Change to ">" for HTML output.
	public $empty_element_suffix = " />";
	public $tab_width = 4;

	# Change to `true` to disallow markup or entities.
	public $no_markup = false;
	public $no_entities = false;

	# Predefined urls and titles for reference links and images.
	public $predef_urls = array();
	public $predef_titles = array();

	# Optional filter function for URLs
	public $url_filter_func = null;


	### Parser Implementation ###

	# Regex to match balanced [brackets].
	# Needed to insert a maximum bracked depth while converting to PHP.
	protected $nested_brackets_depth = 6;
	protected $nested_brackets_re;

	protected $nested_url_parenthesis_depth = 4;
	protected $nested_url_parenthesis_re;

	# Table of hash values for escaped characters:
	protected $escape_chars = '\`*_{}[]()>#+-.!';
	protected $escape_chars_re;


	public function __construct() {
	#
	# Constructor function. Initialize appropriate member variables.
	#
		$this->_initDetab();
		$this->prepareItalicsAndBold();

		$this->nested_brackets_re =
			str_repeat('(?>[^\[\]]+|\[', $this->nested_brackets_depth).
			str_repeat('\])*', $this->nested_brackets_depth);

		$this->nested_url_parenthesis_re =
			str_repeat('(?>[^()\s]+|\(', $this->nested_url_parenthesis_depth).
			str_repeat('(?>\)))*', $this->nested_url_parenthesis_depth);

		$this->escape_chars_re = '['.preg_quote($this->escape_chars).']';

		# Sort document, block, and span gamut in ascendent priority order.
		asort($this->document_gamut);
		asort($this->block_gamut);
		asort($this->span_gamut);
	}


	# Internal hashes used during transformation.
	protected $urls = array();
	protected $titles = array();
	protected $html_hashes = array();

	# Status flag to avoid invalid nesting.
	protected $in_anchor = false;


	protected function setup() {
	#
	# Called before the transformation process starts to setup parser
	# states.
	#
		# Clear global hashes.
		$this->urls = $this->predef_urls;
		$this->titles = $this->predef_titles;
		$this->html_hashes = array();

		$this->in_anchor = false;
	}

	protected function teardown() {
	#
	# Called after the transformation process to clear any variable
	# which may be taking up memory unnecessarly.
	#
		$this->urls = array();
		$this->titles = array();
		$this->html_hashes = array();
	}


	public function transform($text) {
	#
	# Main function. Performs some preprocessing on the input text
	# and pass it through the document gamut.
	#
		$this->setup();

		# Remove UTF-8 BOM and marker character in input, if present.
		$text = preg_replace('{^\xEF\xBB\xBF|\x1A}', '', $text);

		# Standardize line endings:
		#   DOS to Unix and Mac to Unix
		$text = preg_replace('{\r\n?}', "\n", $text);

		# Make sure $text ends with a couple of newlines:
		$text .= "\n\n";

		# Convert all tabs to spaces.
		$text = $this->detab($text);

		# Turn block-level HTML blocks into hash entries
		$text = $this->hashHTMLBlocks($text);

		# Strip any lines consisting only of spaces and tabs.
		# This makes subsequent regexen easier to write, because we can
		# match consecutive blank lines with /\n+/ instead of something
		# contorted like /[ ]*\n+/ .
		$text = preg_replace('/^[ ]+$/m', '', $text);

		# Run document gamut methods.
		foreach ($this->document_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		$this->teardown();

		return $text . "\n";
	}

	protected $document_gamut = array(
		# Strip link definitions, store in hashes.
		"stripLinkDefinitions" => 20,

		"runBasicBlockGamut"   => 30,
		);


	protected function stripLinkDefinitions($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
							(?:\n+|\Z)
			}xm',
			array($this, '_stripLinkDefinitions_callback'),
			$text);
		return $text;
	}
	protected function _stripLinkDefinitions_callback($matches) {
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		return ''; # String that will replace the block
	}


	protected function hashHTMLBlocks($text) {
		if ($this->no_markup)  return $text;

		$less_than_tab = $this->tab_width - 1;

		# Hashify HTML blocks:
		# We only want to do this for block-level HTML tags, such as headers,
		# lists, and tables. That's because we still want to wrap <p>s around
		# "paragraphs" that are wrapped in non-block-level tags, such as anchors,
		# phrase emphasis, and spans. The list of tags we're looking for is
		# hard-coded:
		#
		# *  List "a" is made of tags which can be both inline or block-level.
		#    These will be treated block-level when the start tag is alone on
		#    its line, otherwise they're not matched here and will be taken as
		#    inline later.
		# *  List "b" is made of tags which are always block-level;
		#
		$block_tags_a_re = 'ins|del';
		$block_tags_b_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|'.
						   'script|noscript|style|form|fieldset|iframe|math|svg|'.
						   'article|section|nav|aside|hgroup|header|footer|'.
						   'figure';

		# Regular expression for the content of a block tag.
		$nested_tags_level = 4;
		$attr = '
			(?>				# optional tag attributes
			  \s			# starts with whitespace
			  (?>
				[^>"/]+		# text outside quotes
			  |
				/+(?!>)		# slash not followed by ">"
			  |
				"[^"]*"		# text inside double quotes (tolerate ">")
			  |
				\'[^\']*\'	# text inside single quotes (tolerate ">")
			  )*
			)?
			';
		$content =
			str_repeat('
				(?>
				  [^<]+			# content without tag
				|
				  <\2			# nested opening tag
					'.$attr.'	# attributes
					(?>
					  />
					|
					  >', $nested_tags_level).	# end of opening tag
					  '.*?'.					# last level nested tag content
			str_repeat('
					  </\2\s*>	# closing nested tag
					)
				  |
					<(?!/\2\s*>	# other tags with a different name
				  )
				)*',
				$nested_tags_level);
		$content2 = str_replace('\2', '\3', $content);

		# First, look for nested blocks, e.g.:
		# 	<div>
		# 		<div>
		# 		tags for inner block must be indented.
		# 		</div>
		# 	</div>
		#
		# The outermost tags must start at the left margin for this to match, and
		# the inner nested divs must be indented.
		# We need to do this before the next, more liberal match, because the next
		# match will start at the first `<div>` and stop at the first `</div>`.
		$text = preg_replace_callback('{(?>
			(?>
				(?<=\n)			# Starting on its own line
				|				# or
				\A\n?			# the at beginning of the doc
			)
			(						# save in $1

			  # Match from `\n<tag>` to `</tag>\n`, handling nested tags
			  # in between.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_b_re.')# start tag = $2
						'.$attr.'>			# attributes followed by > and \n
						'.$content.'		# content, support nesting
						</\2>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special version for tags of group a.

						[ ]{0,'.$less_than_tab.'}
						<('.$block_tags_a_re.')# start tag = $3
						'.$attr.'>[ ]*\n	# attributes followed by >
						'.$content2.'		# content, support nesting
						</\3>				# the matching end tag
						[ ]*				# trailing spaces/tabs
						(?=\n+|\Z)	# followed by a newline or end of document

			| # Special case just for <hr />. It was easier to make a special
			  # case than to make the other regex more complicated.

						[ ]{0,'.$less_than_tab.'}
						<(hr)				# start tag = $2
						'.$attr.'			# attributes
						/?>					# the matching end tag
						[ ]*
						(?=\n{2,}|\Z)		# followed by a blank line or end of document

			| # Special case for standalone HTML comments:

					[ ]{0,'.$less_than_tab.'}
					(?s:
						<!-- .*? -->
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document

			| # PHP and ASP-style processor instructions (<? and <%)

					[ ]{0,'.$less_than_tab.'}
					(?s:
						<([?%])			# $2
						.*?
						\2>
					)
					[ ]*
					(?=\n{2,}|\Z)		# followed by a blank line or end of document

			)
			)}Sxmi',
			array($this, '_hashHTMLBlocks_callback'),
			$text);

		return $text;
	}
	protected function _hashHTMLBlocks_callback($matches) {
		$text = $matches[1];
		$key  = $this->hashBlock($text);
		return "\n\n$key\n\n";
	}


	protected function hashPart($text, $boundary = 'X') {
	#
	# Called whenever a tag must be hashed when a function insert an atomic
	# element in the text stream. Passing $text to through this function gives
	# a unique text-token which will be reverted back when calling unhash.
	#
	# The $boundary argument specify what character should be used to surround
	# the token. By convension, "B" is used for block elements that needs not
	# to be wrapped into paragraph tags at the end, ":" is used for elements
	# that are word separators and "X" is used in the general case.
	#
		# Swap back any tag hash found in $text so we do not have to `unhash`
		# multiple times at the end.
		$text = $this->unhash($text);

		# Then hash the block.
		static $i = 0;
		$key = "$boundary\x1A" . ++$i . $boundary;
		$this->html_hashes[$key] = $text;
		return $key; # String that will replace the tag.
	}


	protected function hashBlock($text) {
	#
	# Shortcut function for hashPart with block-level boundaries.
	#
		return $this->hashPart($text, 'B');
	}


	protected $block_gamut = array(
	#
	# These are all the transformations that form block-level
	# tags like paragraphs, headers, and list items.
	#
		"doHeaders"         => 10,
		"doHorizontalRules" => 20,

		"doLists"           => 40,
		"doCodeBlocks"      => 50,
		"doBlockQuotes"     => 60,
		);

	protected function runBlockGamut($text) {
	#
	# Run block gamut tranformations.
	#
		# We need to escape raw HTML in Markdown source before doing anything
		# else. This need to be done for each block, and not only at the
		# begining in the Markdown function since hashed blocks can be part of
		# list items and could have been indented. Indented blocks would have
		# been seen as a code block in a previous pass of hashHTMLBlocks.
		$text = $this->hashHTMLBlocks($text);

		return $this->runBasicBlockGamut($text);
	}

	protected function runBasicBlockGamut($text) {
	#
	# Run block gamut tranformations, without hashing HTML blocks. This is
	# useful when HTML blocks are known to be already hashed, like in the first
	# whole-document pass.
	#
		foreach ($this->block_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		# Finally form paragraph and restore hashed blocks.
		$text = $this->formParagraphs($text);

		return $text;
	}


	protected function doHorizontalRules($text) {
		# Do Horizontal Rules:
		return preg_replace(
			'{
				^[ ]{0,3}	# Leading space
				([-*_])		# $1: First marker
				(?>			# Repeated marker group
					[ ]{0,2}	# Zero, one, or two spaces.
					\1			# Marker character
				){2,}		# Group repeated at least twice
				[ ]*		# Tailing spaces
				$			# End of line.
			}mx',
			"\n".$this->hashBlock("<hr$this->empty_element_suffix")."\n",
			$text);
	}


	protected $span_gamut = array(
	#
	# These are all the transformations that occur *within* block-level
	# tags like paragraphs, headers, and list items.
	#
		# Process character escapes, code spans, and inline HTML
		# in one shot.
		"parseSpan"           => -30,

		# Process anchor and image tags. Images must come first,
		# because ![foo][f] looks like an anchor.
		"doImages"            =>  10,
		"doAnchors"           =>  20,

		# Make links out of things like `<http://example.com/>`
		# Must come after doAnchors, because you can use < and >
		# delimiters in inline links like [this](<url>).
		"doAutoLinks"         =>  30,
		"encodeAmpsAndAngles" =>  40,

		"doItalicsAndBold"    =>  50,
		"doHardBreaks"        =>  60
		);

	protected function runSpanGamut($text) {
	#
	# Run span gamut tranformations.
	#
		foreach ($this->span_gamut as $method => $priority) {
			$text = $this->$method($text);
		}

		return $text;
	}


	protected function doHardBreaks($text) {
		# Do hard breaks:
		return preg_replace_callback('/ {2,}\n/',
			array($this, '_doHardBreaks_callback'), $text);
	}
	protected function _doHardBreaks_callback($matches) {
		return $this->hashPart("<br$this->empty_element_suffix\n");
	}


	protected function doAnchors($text) {
	#
	# Turn Markdown link shortcuts into XHTML <a> tags.
	#
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;

		#
		# First, handle reference-style links: [link text] [id]
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

		#
		# Next, inline-style links: [link text](url "optional title")
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			)
			}xs',
			array($this, '_doAnchors_inline_callback'), $text);

		#
		# Last, handle reference-style shortcuts: [link text]
		# These must come last in case you've also got [link text][1]
		# or [link text](/foo)
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}
	protected function _doAnchors_reference_callback($matches) {
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == "") {
			# for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}

		# lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeURLAttribute($url);

			$result = "<a href=\"$url\"";
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}

			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}
	protected function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		// if the URL was of the form <s p a c e s> it got caught by the HTML
		// tag parser and hashed. Need to reverse the process before using the URL.
		$unhashed = $this->unhash($url);
		if ($unhashed != $url)
			$url = preg_replace('/^<(.*)>$/', '\1', $unhashed);

		$url = $this->encodeURLAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}


	protected function doImages($text) {
	#
	# Turn Markdown image shortcuts into <img> tags.
	#
		#
		# First, handle reference-style labeled images: ![alt text][id]
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs',
			array($this, '_doImages_reference_callback'), $text);

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs',
			array($this, '_doImages_inline_callback'), $text);

		return $text;
	}
	protected function _doImages_reference_callback($matches) {
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == "") {
			$link_id = strtolower($alt_text); # for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeURLAttribute($this->urls[$link_id]);
			$result = "<img src=\"$url\" alt=\"$alt_text\"";
			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
			$result .= $this->empty_element_suffix;
			$result = $this->hashPart($result);
		}
		else {
			# If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}
	protected function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeURLAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; # $title already quoted
		}
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}


	protected function doHeaders($text) {
		# Setext-style headers:
		#	  Header 1
		#	  ========
		#
		#	  Header 2
		#	  --------
		#
		$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
			array($this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1
		#	## Header 2
		#	## Header 2 with closing hashes ##
		#	...
		#	###### Header 6
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm',
			array($this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	protected function _doHeaders_callback_setext($matches) {
		# Terrible hack to check we haven't found an empty list item.
		if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1]))
			return $matches[0];

		$level = $matches[2]{0} == '=' ? 1 : 2;
		$block = "<h$level>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	protected function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);
		$block = "<h$level>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}


	protected function doLists($text) {
	#
	# Form HTML ordered (numbered) and unordered (bulleted) lists.
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';

		$markers_relist = array(
			$marker_ul_re => $marker_ol_re,
			$marker_ol_re => $marker_ul_re,
			);

		foreach ($markers_relist as $marker_re => $other_marker_re) {
			# Re-usable pattern to match any entirel ul or ol list:
			$whole_list_re = '
				(								# $1 = whole list
				  (								# $2
					([ ]{0,'.$less_than_tab.'})	# $3 = number of spaces
					('.$marker_re.')			# $4 = first list item marker
					[ ]+
				  )
				  (?s:.+?)
				  (								# $5
					  \z
					|
					  \n{2,}
					  (?=\S)
					  (?!						# Negative lookahead for another list item marker
						[ ]*
						'.$marker_re.'[ ]+
					  )
					|
					  (?=						# Lookahead for another kind of list
					    \n
						\3						# Must have the same indentation
						'.$other_marker_re.'[ ]+
					  )
				  )
				)
			'; // mx

			# We use a different prefix before nested lists than top-level lists.
			# See extended comment in _ProcessListItems().

			if ($this->list_level) {
				$text = preg_replace_callback('{
						^
						'.$whole_list_re.'
					}mx',
					array($this, '_doLists_callback'), $text);
			}
			else {
				$text = preg_replace_callback('{
						(?:(?<=\n)\n|\A\n?) # Must eat the newline
						'.$whole_list_re.'
					}mx',
					array($this, '_doLists_callback'), $text);
			}
		}

		return $text;
	}
	protected function _doLists_callback($matches) {
		# Re-usable patterns to match list item bullets and number markers:
		$marker_ul_re  = '[*+-]';
		$marker_ol_re  = '\d+[\.]';
		$marker_any_re = "(?:$marker_ul_re|$marker_ol_re)";

		$list = $matches[1];
		$list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol";

		$marker_any_re = ( $list_type == "ul" ? $marker_ul_re : $marker_ol_re );

		$list .= "\n";
		$result = $this->processListItems($list, $marker_any_re);

		$result = $this->hashBlock("<$list_type>\n" . $result . "</$list_type>");
		return "\n". $result ."\n\n";
	}

	protected $list_level = 0;

	protected function processListItems($list_str, $marker_any_re) {
	#
	#	Process the contents of a single ordered or unordered list, splitting it
	#	into individual list items.
	#
		# The $this->list_level global keeps track of when we're inside a list.
		# Each time we enter a list, we increment it; when we leave a list,
		# we decrement. If it's zero, we're not in a list anymore.
		#
		# We do this because when we're not inside a list, we want to treat
		# something like this:
		#
		#		I recommend upgrading to version
		#		8. Oops, now this line is treated
		#		as a sub-list.
		#
		# As a single paragraph, despite the fact that the second line starts
		# with a digit-period-space sequence.
		#
		# Whereas when we're inside a list (or sub-list), that line will be
		# treated as the start of a sub-list. What a kludge, huh? This is
		# an aspect of Markdown's syntax that's hard to parse perfectly
		# without resorting to mind-reading. Perhaps the solution is to
		# change the syntax rules such that sub-lists must start with a
		# starting cardinal number; e.g. "1." or "a.".

		$this->list_level++;

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		$list_str = preg_replace_callback('{
			(\n)?							# leading line = $1
			(^[ ]*)							# leading whitespace = $2
			('.$marker_any_re.'				# list marker and space = $3
				(?:[ ]+|(?=\n))	# space only required if item is not empty
			)
			((?s:.*?))						# list item text   = $4
			(?:(\n+(?=\n))|\n)				# tailing blank line = $5
			(?= \n* (\z | \2 ('.$marker_any_re.') (?:[ ]+|(?=\n))))
			}xm',
			array($this, '_processListItems_callback'), $list_str);

		$this->list_level--;
		return $list_str;
	}
	protected function _processListItems_callback($matches) {
		$item = $matches[4];
		$leading_line =& $matches[1];
		$leading_space =& $matches[2];
		$marker_space = $matches[3];
		$tailing_blank_line =& $matches[5];

		if ($leading_line || $tailing_blank_line ||
			preg_match('/\n{2,}/', $item))
		{
			# Replace marker with the appropriate whitespace indentation
			$item = $leading_space . str_repeat(' ', strlen($marker_space)) . $item;
			$item = $this->runBlockGamut($this->outdent($item)."\n");
		}
		else {
			# Recursion for sub-lists:
			$item = $this->doLists($this->outdent($item));
			$item = preg_replace('/\n+$/', '', $item);
			$item = $this->runSpanGamut($item);
		}

		return "<li>" . $item . "</li>\n";
	}


	protected function doCodeBlocks($text) {
	#
	#	Process Markdown `<pre><code>` blocks.
	#
		$text = preg_replace_callback('{
				(?:\n\n|\A\n?)
				(	            # $1 = the code block -- one or more lines, starting with a space/tab
				  (?>
					[ ]{'.$this->tab_width.'}  # Lines must start with a tab or a tab-width of spaces
					.*\n+
				  )+
				)
				((?=^[ ]{0,'.$this->tab_width.'}\S)|\Z)	# Lookahead for non-space at line-start, or end of doc
			}xm',
			array($this, '_doCodeBlocks_callback'), $text);

		return $text;
	}
	protected function _doCodeBlocks_callback($matches) {
		$codeblock = $matches[1];

		$codeblock = $this->outdent($codeblock);
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

		# trim leading newlines and trailing newlines
		$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

		$codeblock = "<pre><code>$codeblock\n</code></pre>";
		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}


	protected function makeCodeSpan($code) {
	#
	# Create a code span markup for $code. Called from handleSpanToken.
	#
		$code = htmlspecialchars(trim($code), ENT_NOQUOTES);

		# Vanilla: add 2 lines below to do <pre><code> if there are newlines in the code.
		if (strpos($code, "\n"))
			return $this->hashPart("<pre><code>$code</code></pre>");

		return $this->hashPart("<code>$code</code>");
	}


	protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<!_)_(?!_))(?![\.,:;]?\s)',
		'*' => '(?<![\s*])\*(?!\*)',
		'_' => '(?<![\s_])_(?!_)',
		);
	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<!_)__(?!_))(?![\.,:;]?\s)',
		'**' => '(?<![\s*])\*\*(?!\*)',
		'__' => '(?<![\s_])__(?!_)',
		);
	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<!_)___(?!_))(?![\.,:;]?\s)',
		'***' => '(?<![\s*])\*\*\*(?!\*)',
		'___' => '(?<![\s_])___(?!_)',
		);
	protected $em_strong_prepared_relist;

	protected function prepareItalicsAndBold() {
	#
	# Prepare regular expressions for searching emphasis tokens in any
	# context.
	#
		foreach ($this->em_relist as $em => $em_re) {
			foreach ($this->strong_relist as $strong => $strong_re) {
				# Construct list of allowed token expressions.
				$token_relist = array();
				if (isset($this->em_strong_relist["$em$strong"])) {
					$token_relist[] = $this->em_strong_relist["$em$strong"];
				}
				$token_relist[] = $em_re;
				$token_relist[] = $strong_re;

				# Construct master expression from list.
				$token_re = '{('. implode('|', $token_relist) .')}';
				$this->em_strong_prepared_relist["$em$strong"] = $token_re;
			}
		}
	}

	protected function doItalicsAndBold($text) {
		$token_stack = array('');
		$text_stack = array('');
		$em = '';
		$strong = '';
		$tree_char_em = false;

		while (1) {
			#
			# Get prepared regular expression for seraching emphasis tokens
			# in current context.
			#
			$token_re = $this->em_strong_prepared_relist["$em$strong"];

			#
			# Each loop iteration search for the next emphasis token.
			# Each token is then passed to handleSpanToken.
			#
			$parts = preg_split($token_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			$text_stack[0] .= $parts[0];
			$token =& $parts[1];
			$text =& $parts[2];

			if (empty($token)) {
				# Reached end of text span: empty stack without emitting.
				# any more emphasis.
				while ($token_stack[0]) {
					$text_stack[1] .= array_shift($token_stack);
					$text_stack[0] .= array_shift($text_stack);
				}
				break;
			}

			$token_len = strlen($token);
			if ($tree_char_em) {
				# Reached closing marker while inside a three-char emphasis.
				if ($token_len == 3) {
					# Three-char closing marker, close em and strong.
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong><em>$span</em></strong>";
					$text_stack[0] .= $this->hashPart($span);
					$em = '';
					$strong = '';
				} else {
					# Other closing marker: close one em or strong and
					# change current token state to match the other
					$token_stack[0] = str_repeat($token{0}, 3-$token_len);
					$tag = $token_len == 2 ? "strong" : "em";
					$span = $text_stack[0];
					$span = $this->runSpanGamut($span);
					$span = "<$tag>$span</$tag>";
					$text_stack[0] = $this->hashPart($span);
					$$tag = ''; # $$tag stands for $em or $strong
				}
				$tree_char_em = false;
			} else if ($token_len == 3) {
				if ($em) {
					# Reached closing marker for both em and strong.
					# Closing strong marker:
					for ($i = 0; $i < 2; ++$i) {
						$shifted_token = array_shift($token_stack);
						$tag = strlen($shifted_token) == 2 ? "strong" : "em";
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<$tag>$span</$tag>";
						$text_stack[0] .= $this->hashPart($span);
						$$tag = ''; # $$tag stands for $em or $strong
					}
				} else {
					# Reached opening three-char emphasis marker. Push on token
					# stack; will be handled by the special condition above.
					$em = $token{0};
					$strong = "$em$em";
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$tree_char_em = true;
				}
			} else if ($token_len == 2) {
				if ($strong) {
					# Unwind any dangling emphasis marker:
					if (strlen($token_stack[0]) == 1) {
						$text_stack[1] .= array_shift($token_stack);
						$text_stack[0] .= array_shift($text_stack);
					}
					# Closing strong marker:
					array_shift($token_stack);
					$span = array_shift($text_stack);
					$span = $this->runSpanGamut($span);
					$span = "<strong>$span</strong>";
					$text_stack[0] .= $this->hashPart($span);
					$strong = '';
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$strong = $token;
				}
			} else {
				# Here $token_len == 1
				if ($em) {
					if (strlen($token_stack[0]) == 1) {
						# Closing emphasis marker:
						array_shift($token_stack);
						$span = array_shift($text_stack);
						$span = $this->runSpanGamut($span);
						$span = "<em>$span</em>";
						$text_stack[0] .= $this->hashPart($span);
						$em = '';
					} else {
						$text_stack[0] .= $token;
					}
				} else {
					array_unshift($token_stack, $token);
					array_unshift($text_stack, '');
					$em = $token;
				}
			}
		}
		return $text_stack[0];
	}


	protected function doBlockQuotes($text) {
	   # Vanilla: delete `(.+\n)*	   # subsequent consecutive lines` from pattern.
		$text = preg_replace_callback('/
			  (								# Wrap whole match in $1
				(?>
				  ^[ ]*>[ ]?			# ">" at the start of a line
					.+\n					# rest of the first line
				  \n*						# blanks
				)+
			  )
			/xm',
			array($this, '_doBlockQuotes_callback'), $text);

		return $text;
	}
	protected function _doBlockQuotes_callback($matches) {
		$bq = $matches[1];
		# trim one level of quoting - trim whitespace-only lines
		$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
		$bq = $this->runBlockGamut($bq);		# recurse

		$bq = preg_replace('/^/m', "  ", $bq);
		# These leading spaces cause problem with <pre> content,
		# so we need to fix that:
		$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx',
			array($this, '_doBlockQuotes_callback2'), $bq);

	   # Vanilla: add ` class=\"Quote\"`
		return "\n". $this->hashBlock("<blockquote class=\"Quote\">\n$bq\n</blockquote>")."\n\n";
	}
	protected function _doBlockQuotes_callback2($matches) {
		$pre = $matches[1];
		$pre = preg_replace('/^  /m', '', $pre);
		return $pre;
	}


	protected function formParagraphs($text) {
	#
	#	Params:
	#		$text - string to process with html <p> tags
	#
		# Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value) {
			if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
				# Is a paragraph.
				$value = $this->runSpanGamut($value);
				$value = preg_replace('/^([ ]*)/', "<p>", $value);
				$value .= "</p>";
				$grafs[$key] = $this->unhash($value);
			}
			else {
				# Is a block.
				# Modify elements of @grafs in-place...
				$graf = $value;
				$block = $this->html_hashes[$graf];
				$graf = $block;
//				if (preg_match('{
//					\A
//					(							# $1 = <div> tag
//					  <div  \s+
//					  [^>]*
//					  \b
//					  markdown\s*=\s*  ([\'"])	#	$2 = attr quote char
//					  1
//					  \2
//					  [^>]*
//					  >
//					)
//					(							# $3 = contents
//					.*
//					)
//					(</div>)					# $4 = closing tag
//					\z
//					}xs', $block, $matches))
//				{
//					list(, $div_open, , $div_content, $div_close) = $matches;
//
//					# We can't call Markdown(), because that resets the hash;
//					# that initialization code should be pulled into its own sub, though.
//					$div_content = $this->hashHTMLBlocks($div_content);
//
//					# Run document gamut methods on the content.
//					foreach ($this->document_gamut as $method => $priority) {
//						$div_content = $this->$method($div_content);
//					}
//
//					$div_open = preg_replace(
//						'{\smarkdown\s*=\s*([\'"]).+?\1}', '', $div_open);
//
//					$graf = $div_open . "\n" . $div_content . "\n" . $div_close;
//				}
				$grafs[$key] = $graf;
			}
		}

		return implode("\n\n", $grafs);
	}


	protected function encodeAttribute($text) {
	#
	# Encode text for a double-quoted HTML attribute. This function
	# is *not* suitable for attributes enclosed in single quotes.
	#
		$text = $this->encodeAmpsAndAngles($text);
		$text = str_replace('"', '&quot;', $text);
		return $text;
	}


	protected function encodeURLAttribute($url, &$text = null) {
	#
	# Encode text for a double-quoted HTML attribute containing a URL,
	# applying the URL filter if set. Also generates the textual
	# representation for the URL (removing mailto: or tel:) storing it in $text.
	# This function is *not* suitable for attributes enclosed in single quotes.
	#
		if ($this->url_filter_func)
			$url = call_user_func($this->url_filter_func, $url);

		if (preg_match('{^mailto:}i', $url))
			$url = $this->encodeEntityObfuscatedAttribute($url, $text, 7);
		else if (preg_match('{^tel:}i', $url))
		{
			$url = $this->encodeAttribute($url);
			$text = substr($url, 4);
		}
		else
		{
			$url = $this->encodeAttribute($url);
			$text = $url;
		}

		return $url;
	}


	protected function encodeAmpsAndAngles($text) {
	#
	# Smart processing for ampersands and angle brackets that need to
	# be encoded. Valid character entities are left alone unless the
	# no-entities mode is set.
	#
		if ($this->no_entities) {
			$text = str_replace('&', '&amp;', $text);
		} else {
			# Ampersand-encoding based entirely on Nat Irons's Amputator
			# MT plugin: <http://bumppo.net/projects/amputator/>
			$text = preg_replace('/&(?!#?[xX]?(?:[0-9a-fA-F]+|\w+);)/',
								'&amp;', $text);
		}
		# Encode remaining <'s
		$text = str_replace('<', '&lt;', $text);

		return $text;
	}


	protected function doAutoLinks($text) {
		$text = preg_replace_callback('{<((https?|ftp|dict|tel):[^\'">\s]+)>}i',
			array($this, '_doAutoLinks_url_callback'), $text);

		# Email addresses: <address@domain.foo>
		$text = preg_replace_callback('{
			<
			(?:mailto:)?
			(
				(?:
					[-!#$%&\'*+/=?^_`.{|}~\w\x80-\xFF]+
				|
					".*?"
				)
				\@
				(?:
					[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
				|
					\[[\d.a-fA-F:]+\]	# IPv4 & IPv6
				)
			)
			>
			}xi',
			array($this, '_doAutoLinks_email_callback'), $text);

		return $text;
	}
	protected function _doAutoLinks_url_callback($matches) {
		$url = $this->encodeURLAttribute($matches[1], $text);
		$link = "<a href=\"$url\">$text</a>";
		return $this->hashPart($link);
	}
	protected function _doAutoLinks_email_callback($matches) {
		$addr = $matches[1];
		$url = $this->encodeURLAttribute("mailto:$addr", $text);
		$link = "<a href=\"$url\">$text</a>";
		return $this->hashPart($link);
	}


	protected function encodeEntityObfuscatedAttribute($text, &$tail = null, $head_length = 0) {
	#
	#	Input: some text to obfuscate, e.g. "mailto:foo@example.com"
	#
	#	Output: the same text but with most characters encoded as either a
	#		decimal or hex entity, in the hopes of foiling most address
	#		harvesting spam bots. E.g.:
	#
	#        &#109;&#x61;&#105;&#x6c;&#116;&#x6f;&#58;&#x66;o&#111;
	#        &#x40;&#101;&#x78;&#97;&#x6d;&#112;&#x6c;&#101;&#46;&#x63;&#111;
	#        &#x6d;
	#
	#	Note: the additional output $tail is assigned the same value as the
	#	ouput, minus the number of characters specified by $head_length.
	#
	#	Based by a filter by Matthew Wickline, posted to BBEdit-Talk.
	#   With some optimizations by Milian Wolff. Forced encoding of HTML
	#	attribute special characters by Allan Odgaard.
	#
		if ($text == "") return $tail = "";

		$chars = preg_split('/(?<!^)(?!$)/', $text);
		$seed = (int)abs(crc32($text) / strlen($text)); # Deterministic seed.

		foreach ($chars as $key => $char) {
			$ord = ord($char);
			# Ignore non-ascii chars.
			if ($ord < 128) {
				$r = ($seed * (1 + $key)) % 100; # Pseudo-random function.
				# roughly 10% raw, 45% hex, 45% dec
				# '@' *must* be encoded. I insist.
				# '"' and '>' have to be encoded inside the attribute
				if ($r > 90 && strpos('@"&>', $char) === false) /* do nothing */;
				else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';';
				else              $chars[$key] = '&#'.$ord.';';
			}
		}

		$text = implode('', $chars);
		$tail = $head_length ? implode('', array_slice($chars, $head_length)) : $text;

		return $text;
	}


	protected function parseSpan($str) {
	#
	# Take the string $str and parse it into tokens, hashing embeded HTML,
	# escaped characters and handling code spans.
	#
		$output = '';

		$span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
				|
					<[-a-zA-Z0-9:_]+\s*/> # xml-style empty tag
				|
					</[-a-zA-Z0-9:_]+\s*> # closing tag
			').'
				)
				}xs';

		while (1) {
			#
			# Each loop iteration seach for either the next tag, the next
			# openning code span marker, or the next escaped character.
			# Each token is then passed to handleSpanToken.
			#
			$parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

			# Create token from text preceding tag.
			if ($parts[0] != "") {
				$output .= $parts[0];
			}

			# Check if we reach the end.
			if (isset($parts[1])) {
				$output .= $this->handleSpanToken($parts[1], $parts[2]);
				$str = $parts[2];
			}
			else {
				break;
			}
		}

		return $output;
	}


	protected function handleSpanToken($token, &$str) {
	#
	# Handle $token provided by parseSpan by determining its nature and
	# returning the corresponding value that should replace it.
	#
		switch ($token{0}) {
			case "\\":
				return $this->hashPart("&#". ord($token{1}). ";");
			case "`":
				# Search for end marker in remaining text.
				if (preg_match('/^(.*?[^`])'.preg_quote($token).'(?!`)(.*)$/sm',
					$str, $matches))
				{
					$str = $matches[2];
					$codespan = $this->makeCodeSpan($matches[1]);
					return $this->hashPart($codespan);
				}
				return $token; // return as text since no ending marker found.
			default:
				return $this->hashPart($token);
		}
	}


	protected function outdent($text) {
	#
	# Remove one level of line-leading tabs or spaces
	#
		return preg_replace('/^(\t|[ ]{1,'.$this->tab_width.'})/m', '', $text);
	}


	# String length function for detab. `_initDetab` will create a function to
	# hanlde UTF-8 if the default function does not exist.
	protected $utf8_strlen = 'mb_strlen';

	protected function detab($text) {
	#
	# Replace tabs with the appropriate amount of space.
	#
		# For each line we separate the line in blocks delemited by
		# tab characters. Then we reconstruct every line by adding the
		# appropriate number of space between each blocks.

		$text = preg_replace_callback('/^.*\t.*$/m',
			array($this, '_detab_callback'), $text);

		return $text;
	}
	protected function _detab_callback($matches) {
		$line = $matches[0];
		$strlen = $this->utf8_strlen; # strlen function for UTF-8.

		# Split in blocks.
		$blocks = explode("\t", $line);
		# Add each blocks to the line.
		$line = $blocks[0];
		unset($blocks[0]); # Do not add first block twice.
		foreach ($blocks as $block) {
			# Calculate amount of space, insert spaces, insert block.
			$amount = $this->tab_width -
				$strlen($line, 'UTF-8') % $this->tab_width;
			$line .= str_repeat(" ", $amount) . $block;
		}
		return $line;
	}
	protected function _initDetab() {
	#
	# Check for the availability of the function in the `utf8_strlen` property
	# (initially `mb_strlen`). If the function is not available, create a
	# function that will loosely count the number of UTF-8 characters with a
	# regular expression.
	#
		if (function_exists($this->utf8_strlen)) return;
		$this->utf8_strlen = create_function('$text', 'return preg_match_all(
			"/[\\\\x00-\\\\xBF]|[\\\\xC0-\\\\xFF][\\\\x80-\\\\xBF]*/",
			$text, $m);');
	}


	protected function unhash($text) {
	#
	# Swap back in all the tags hashed by _HashHTMLBlocks.
	#
		return preg_replace_callback('/(.)\x1A[0-9]+\1/',
			array($this, '_unhash_callback'), $text);
	}
	protected function _unhash_callback($matches) {
		return $this->html_hashes[$matches[0]];
	}

}


#
# Temporary Markdown Extra Parser Implementation Class
#
# NOTE: DON'T USE THIS CLASS
# Currently the implementation of of Extra resides here in this temporary class.
# This makes it easier to propagate the changes between the three different
# packaging styles of PHP Markdown. When this issue is resolved, this
# MarkdownExtra_TmpImpl class here will disappear and \Michelf\MarkdownExtra
# will contain the code. So please use \Michelf\MarkdownExtra and ignore this
# one.
#

abstract class _MarkdownExtra_TmpImpl extends \Michelf\Markdown {

	### Configuration Variables ###

	# Prefix for footnote ids.
	public $fn_id_prefix = "";

	# Optional title attribute for footnote links and backlinks.
	public $fn_link_title = "";
	public $fn_backlink_title = "";

	# Optional class attribute for footnote links and backlinks.
	public $fn_link_class = "footnote-ref";
	public $fn_backlink_class = "footnote-backref";

	# Class name for table cell alignment (%% replaced left/center/right)
	# For instance: 'go-%%' becomes 'go-left' or 'go-right' or 'go-center'
	# If empty, the align attribute is used instead of a class name.
	public $table_align_class_tmpl = '';

	# Optional class prefix for fenced code block.
	public $code_class_prefix = "";
	# Class attribute for code blocks goes on the `code` tag;
	# setting this to true will put attributes on the `pre` tag instead.
	public $code_attr_on_pre = false;

	# Predefined abbreviations.
	public $predef_abbr = array();


	### Parser Implementation ###

	public function __construct() {
	#
	# Constructor function. Initialize the parser object.
	#
		# Add extra escapable characters before parent constructor
		# initialize the table.
		$this->escape_chars .= ':|';

		# Insert extra document, block, and span transformations.
		# Parent constructor will do the sorting.
		$this->document_gamut += array(
			"doFencedCodeBlocks" => 5,
			"stripFootnotes"     => 15,
			"stripAbbreviations" => 25,
			"appendFootnotes"    => 50,
			);
		$this->block_gamut += array(
			"doFencedCodeBlocks" => 5,
			"doTables"           => 15,
			"doDefLists"         => 45,
			);
		$this->span_gamut += array(
			"doFootnotes"        => 5,
			"doAbbreviations"    => 70,
			);

		parent::__construct();
	}


	# Extra variables used during extra transformations.
	protected $footnotes = array();
	protected $footnotes_ordered = array();
	protected $footnotes_ref_count = array();
	protected $footnotes_numbers = array();
	protected $abbr_desciptions = array();
	protected $abbr_word_re = '';

	# Give the current footnote number.
	protected $footnote_counter = 1;


	protected function setup() {
	#
	# Setting up Extra-specific variables.
	#
		parent::setup();

		$this->footnotes = array();
		$this->footnotes_ordered = array();
		$this->footnotes_ref_count = array();
		$this->footnotes_numbers = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';
		$this->footnote_counter = 1;

		foreach ($this->predef_abbr as $abbr_word => $abbr_desc) {
			if ($this->abbr_word_re)
				$this->abbr_word_re .= '|';
			$this->abbr_word_re .= preg_quote($abbr_word);
			$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		}
	}

	protected function teardown() {
	#
	# Clearing Extra-specific variables.
	#
		$this->footnotes = array();
		$this->footnotes_ordered = array();
		$this->footnotes_ref_count = array();
		$this->footnotes_numbers = array();
		$this->abbr_desciptions = array();
		$this->abbr_word_re = '';

		parent::teardown();
	}


	### Extra Attribute Parser ###

	# Expression to use to catch attributes (includes the braces)
	protected $id_class_attr_catch_re = '\{((?:[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,})[ ]*\}';
	# Expression to use when parsing in a context when no capture is desired
	protected $id_class_attr_nocatch_re = '\{(?:[ ]*[#.a-z][-_:a-zA-Z0-9=]+){1,}[ ]*\}';

	protected function doExtraAttributes($tag_name, $attr) {
	#
	# Parse attributes caught by the $this->id_class_attr_catch_re expression
	# and return the HTML-formatted list of attributes.
	#
	# Currently supported attributes are .class and #id.
	#
		if (empty($attr)) return "";

		# Split on components
		preg_match_all('/[#.a-z][-_:a-zA-Z0-9=]+/', $attr, $matches);
		$elements = $matches[0];

		# handle classes and ids (only first id taken into account)
		$classes = array();
		$attributes = array();
		$id = false;
		foreach ($elements as $element) {
			if ($element{0} == '.') {
				$classes[] = substr($element, 1);
			} else if ($element{0} == '#') {
				if ($id === false) $id = substr($element, 1);
			} else if (strpos($element, '=') > 0) {
				$parts = explode('=', $element, 2);
				$attributes[] = $parts[0] . '="' . $parts[1] . '"';
			}
		}

		# compose attributes as string
		$attr_str = "";
		if (!empty($id)) {
			$attr_str .= ' id="'.$id.'"';
		}
		if (!empty($classes)) {
			$attr_str .= ' class="'.implode(" ", $classes).'"';
		}
		if (!$this->no_markup && !empty($attributes)) {
			$attr_str .= ' '.implode(" ", $attributes);
		}
		return $attr_str;
	}


	protected function stripLinkDefinitions($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: ^[id]: url "optional title"
		$text = preg_replace_callback('{
							^[ ]{0,'.$less_than_tab.'}\[(.+)\][ ]?:	# id = $1
							  [ ]*
							  \n?				# maybe *one* newline
							  [ ]*
							(?:
							  <(.+?)>			# url = $2
							|
							  (\S+?)			# url = $3
							)
							  [ ]*
							  \n?				# maybe one newline
							  [ ]*
							(?:
								(?<=\s)			# lookbehind for whitespace
								["(]
								(.*?)			# title = $4
								[")]
								[ ]*
							)?	# title is optional
					(?:[ ]* '.$this->id_class_attr_catch_re.' )?  # $5 = extra id & class attr
							(?:\n+|\Z)
			}xm',
			array($this, '_stripLinkDefinitions_callback'),
			$text);
		return $text;
	}
	protected function _stripLinkDefinitions_callback($matches) {
		$link_id = strtolower($matches[1]);
		$url = $matches[2] == '' ? $matches[3] : $matches[2];
		$this->urls[$link_id] = $url;
		$this->titles[$link_id] =& $matches[4];
		$this->ref_attr[$link_id] = $this->doExtraAttributes("", $dummy =& $matches[5]);
		return ''; # String that will replace the block
	}


	### HTML Block Parser ###

	# Tags that are always treated as block tags:
	protected $block_tags_re = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend|article|section|nav|aside|hgroup|header|footer|figcaption|figure';

	# Tags treated as block tags only if the opening tag is alone on its line:
	protected $context_block_tags_re = 'script|noscript|style|ins|del|iframe|object|source|track|param|math|svg|canvas|audio|video';

	# Tags where markdown="1" default to span mode:
	protected $contain_span_tags_re = 'p|h[1-6]|li|dd|dt|td|th|legend|address';

	# Tags which must not have their contents modified, no matter where
	# they appear:
	protected $clean_tags_re = 'script|style|math|svg';

	# Tags that do not need to be closed.
	protected $auto_close_tags_re = 'hr|img|param|source|track';


	protected function hashHTMLBlocks($text) {
	#
	# Hashify HTML Blocks and "clean tags".
	#
	# We only want to do this for block-level HTML tags, such as headers,
	# lists, and tables. That's because we still want to wrap <p>s around
	# "paragraphs" that are wrapped in non-block-level tags, such as anchors,
	# phrase emphasis, and spans. The list of tags we're looking for is
	# hard-coded.
	#
	# This works by calling _HashHTMLBlocks_InMarkdown, which then calls
	# _HashHTMLBlocks_InHTML when it encounter block tags. When the markdown="1"
	# attribute is found within a tag, _HashHTMLBlocks_InHTML calls back
	#  _HashHTMLBlocks_InMarkdown to handle the Markdown syntax within the tag.
	# These two functions are calling each other. It's recursive!
	#
		if ($this->no_markup)  return $text;

		#
		# Call the HTML-in-Markdown hasher.
		#
		list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);

		return $text;
	}
	protected function _hashHTMLBlocks_inMarkdown($text, $indent = 0,
										$enclosing_tag_re = '', $span = false)
	{
	#
	# Parse markdown text, calling _HashHTMLBlocks_InHTML for block tags.
	#
	# *   $indent is the number of space to be ignored when checking for code
	#     blocks. This is important because if we don't take the indent into
	#     account, something like this (which looks right) won't work as expected:
	#
	#     <div>
	#         <div markdown="1">
	#         Hello World.  <-- Is this a Markdown code block or text?
	#         </div>  <-- Is this a Markdown code block or a real tag?
	#     <div>
	#
	#     If you don't like this, just don't indent the tag on which
	#     you apply the markdown="1" attribute.
	#
	# *   If $enclosing_tag_re is not empty, stops at the first unmatched closing
	#     tag with that name. Nested tags supported.
	#
	# *   If $span is true, text inside must treated as span. So any double
	#     newline will be replaced by a single newline so that it does not create
	#     paragraphs.
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');

		# Regex to check for the presense of newlines around a block tag.
		$newline_before_re = '/(?:^\n?|\n\n)*$/';
		$newline_after_re =
			'{
				^						# Start of text following the tag.
				(?>[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs';

		# Regex to match any tag.
		$block_tag_re =
			'{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						(?>				# Tag name.
							'.$this->block_tags_re.'			|
							'.$this->context_block_tags_re.'	|
							'.$this->clean_tags_re.'        	|
							(?!\s)'.$enclosing_tag_re.'
						)
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				'. ( !$span ? ' # If not in span.
				|
					# Indented code block
					(?: ^[ ]*\n | ^ | \n[ ]*\n )
					[ ]{'.($indent+4).'}[^\n]* \n
					(?>
						(?: [ ]{'.($indent+4).'}[^\n]* | [ ]* ) \n
					)*
				|
					# Fenced code block marker
					(?<= ^ | \n )
					[ ]{0,'.($indent+3).'}(?:~{3,}|`{3,})
									[ ]*
					(?:
					\.?[-_:a-zA-Z0-9]+ # standalone class name
					|
						'.$this->id_class_attr_nocatch_re.' # extra attributes
					)?
					[ ]*
					(?= \n )
				' : '' ). ' # End (if not is span).
				|
					# Code span marker
					# Note, this regex needs to go after backtick fenced
					# code blocks but it should also be kept outside of the
					# "if not in span" condition adding backticks to the parser
					`+
				)
			}xs';


		$depth = 0;		# Current depth inside the tag tree.
		$parsed = "";	# Parsed text that will be returned.

		#
		# Loop through every tag until we find the closing tag of the parent
		# or loop until reaching the end of text if no parent tag specified.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made
			# by the pattern.
			#
			$parts = preg_split($block_tag_re, $text, 2,
								PREG_SPLIT_DELIM_CAPTURE);

			# If in Markdown span mode, add a empty-string span-level hash
			# after each newline to prevent triggering any block element.
			if ($span) {
				$void = $this->hashPart("", ':');
				$newline = "$void\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}

			$parsed .= $parts[0]; # Text before current tag.

			# If end of $text has been reached. Stop loop.
			if (count($parts) < 3) {
				$text = "";
				break;
			}

			$tag  = $parts[1]; # Tag to handle.
			$text = $parts[2]; # Remaining text after current tag.
			$tag_re = preg_quote($tag); # For use in a regular expression.

			#
			# Check for: Fenced code block marker.
			# Note: need to recheck the whole tag to disambiguate backtick
			# fences from code spans
			#
			if (preg_match('{^\n?([ ]{0,'.($indent+3).'})(~{3,}|`{3,})[ ]*(?:\.?[-_:a-zA-Z0-9]+|'.$this->id_class_attr_nocatch_re.')?[ ]*\n?$}', $tag, $capture)) {
				# Fenced code block marker: find matching end marker.
				$fence_indent = strlen($capture[1]); # use captured indent in re
				$fence_re = $capture[2]; # use captured fence in re
				if (preg_match('{^(?>.*\n)*?[ ]{'.($fence_indent).'}'.$fence_re.'[ ]*(?:\n|$)}', $text,
					$matches))
				{
					# End marker found: pass text unchanged until marker.
					$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));
				}
				else {
					# No end marker: just skip it.
					$parsed .= $tag;
				}
			}
			#
			# Check for: Indented code block.
			#
			else if ($tag{0} == "\n" || $tag{0} == " ") {
				# Indented code block: pass it unchanged, will be handled
				# later.
				$parsed .= $tag;
			}
			#
			# Check for: Code span marker
			# Note: need to check this after backtick fenced code blocks
			#
			else if ($tag{0} == "`") {
				# Find corresponding end marker.
				$tag_re = preg_quote($tag);
				if (preg_match('{^(?>.+?|\n(?!\n))*?(?<!`)'.$tag_re.'(?!`)}',
					$text, $matches))
				{
					# End marker found: pass text unchanged until marker.
					$parsed .= $tag . $matches[0];
					$text = substr($text, strlen($matches[0]));
				}
				else {
					# Unmatched marker: just skip it.
					$parsed .= $tag;
				}
			}
			#
			# Check for: Opening Block level tag or
			#            Opening Context Block tag (like ins and del)
			#               used as a block tag (tag is alone on it's line).
			#
			else if (preg_match('{^<(?:'.$this->block_tags_re.')\b}', $tag) ||
				(	preg_match('{^<(?:'.$this->context_block_tags_re.')\b}', $tag) &&
					preg_match($newline_before_re, $parsed) &&
					preg_match($newline_after_re, $text)	)
				)
			{
				# Need to parse tag and following text using the HTML parser.
				list($block_text, $text) =
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true);

				# Make sure it stays outside of any paragraph by adding newlines.
				$parsed .= "\n\n$block_text\n\n";
			}
			#
			# Check for: Clean tag (like script, math)
			#            HTML Comments, processing instructions.
			#
			else if (preg_match('{^<(?:'.$this->clean_tags_re.')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Need to parse tag and following text using the HTML parser.
				# (don't check for markdown attribute)
				list($block_text, $text) =
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashClean", false);

				$parsed .= $block_text;
			}
			#
			# Check for: Tag with same name as enclosing tag.
			#
			else if ($enclosing_tag_re !== '' &&
				# Same name as enclosing tag.
				preg_match('{^</?(?:'.$enclosing_tag_re.')\b}', $tag))
			{
				#
				# Increase/decrease nested tag count.
				#
				if ($tag{1} == '/')						$depth--;
				else if ($tag{strlen($tag)-2} != '/')	$depth++;

				if ($depth < 0) {
					#
					# Going out of parent element. Clean up and break so we
					# return to the calling function.
					#
					$text = $tag . $text;
					break;
				}

				$parsed .= $tag;
			}
			else {
				$parsed .= $tag;
			}
		} while ($depth >= 0);

		return array($parsed, $text);
	}
	protected function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) {
	#
	# Parse HTML, calling _HashHTMLBlocks_InMarkdown for block tags.
	#
	# *   Calls $hash_method to convert any blocks.
	# *   Stops when the first opening tag closes.
	# *   $md_attr indicate if the use of the `markdown="1"` attribute is allowed.
	#     (it is not inside clean tags)
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');

		# Regex to match `markdown` attribute inside of a tag.
		$markdown_attr_re = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?>
					(["\'])		# $1: quote delimiter
					(.*?)		# $2: attribute value
					\1			# matching delimiter
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs';

		# Regex to match any tag.
		$tag_re = '{
				(					# $2: Capture whole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						(?:
							(?=[\s"\'/a-zA-Z0-9])	# Allowed characters after tag name.
							(?>
								".*?"		|	# Double quotes (can contain `>`)
								\'.*?\'   	|	# Single quotes (can contain `>`)
								.+?				# Anything but quotes and `>`.
							)*?
						)?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';

		$original_text = $text;		# Save original text in case of faliure.

		$depth		= 0;	# Current depth inside the tag tree.
		$block_text	= "";	# Temporary text holder for current text.
		$parsed		= "";	# Parsed text that will be returned.

		#
		# Get the name of the starting tag.
		# (This pattern makes $base_tag_name_re safe without quoting.)
		#
		if (preg_match('/^<([\w:$]*)\b/', $text, $matches))
			$base_tag_name_re = $matches[1];

		#
		# Loop through every tag until we find the corresponding closing tag.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made
			# by the pattern.
			#
			$parts = preg_split($tag_re, $text, 2, PREG_SPLIT_DELIM_CAPTURE);

			if (count($parts) < 3) {
				#
				# End of $text reached with unbalenced tag(s).
				# In that case, we return original text unchanged and pass the
				# first character as filtered to prevent an infinite loop in the
				# parent function.
				#
				return array($original_text{0}, substr($original_text, 1));
			}

			$block_text .= $parts[0]; # Text before current tag.
			$tag         = $parts[1]; # Tag to handle.
			$text        = $parts[2]; # Remaining text after current tag.

			#
			# Check for: Auto-close tag (like <hr/>)
			#			 Comments and Processing Instructions.
			#
			if (preg_match('{^</?(?:'.$this->auto_close_tags_re.')\b}', $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Just add the tag to the block as if it was text.
				$block_text .= $tag;
			}
			else {
				#
				# Increase/decrease nested tag count. Only do so if
				# the tag's name match base tag's.
				#
				if (preg_match('{^</?'.$base_tag_name_re.'\b}', $tag)) {
					if ($tag{1} == '/')						$depth--;
					else if ($tag{strlen($tag)-2} != '/')	$depth++;
				}

				#
				# Check for `markdown="1"` attribute and handle it.
				#
				if ($md_attr &&
					preg_match($markdown_attr_re, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
					# Remove `markdown` attribute from opening tag.
					$tag = preg_replace($markdown_attr_re, '', $tag);

					# Check if text inside this tag must be parsed in span mode.
					$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match('{^<(?:'.$this->contain_span_tags_re.')\b}', $tag);

					# Calculate indent before tag.
					if (preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches)) {
						$strlen = $this->utf8_strlen;
						$indent = $strlen($matches[1], 'UTF-8');
					} else {
						$indent = 0;
					}

					# End preceding block with this tag.
					$block_text .= $tag;
					$parsed .= $this->$hash_method($block_text);

					# Get enclosing tag name for the ParseMarkdown function.
					# (This pattern makes $tag_name_re safe without quoting.)
					preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name_re = $matches[1];

					# Parse the content using the HTML-in-Markdown parser.
					list ($block_text, $text)
						= $this->_hashHTMLBlocks_inMarkdown($text, $indent,
							$tag_name_re, $span_mode);

					# Outdent markdown text.
					if ($indent > 0) {
						$block_text = preg_replace("/^[ ]{1,$indent}/m", "",
													$block_text);
					}

					# Append tag content to parsed text.
					if (!$span_mode)	$parsed .= "\n\n$block_text\n\n";
					else				$parsed .= "$block_text";

					# Start over with a new block.
					$block_text = "";
				}
				else $block_text .= $tag;
			}

		} while ($depth > 0);

		#
		# Hash last block text that wasn't processed inside the loop.
		#
		$parsed .= $this->$hash_method($block_text);

		return array($parsed, $text);
	}


	protected function hashClean($text) {
	#
	# Called whenever a tag must be hashed when a function inserts a "clean" tag
	# in $text, it passes through this function and is automaticaly escaped,
	# blocking invalid nested overlap.
	#
		return $this->hashPart($text, 'C');
	}


	protected function doAnchors($text) {
	#
	# Turn Markdown link shortcuts into XHTML <a> tags.
	#
		if ($this->in_anchor) return $text;
		$this->in_anchor = true;

		#
		# First, handle reference-style links: [link text] [id]
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

		#
		# Next, inline-style links: [link text](url "optional title")
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				('.$this->nested_brackets_re.')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					('.$this->nested_url_parenthesis_re.')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
			)
			}xs',
			array($this, '_doAnchors_inline_callback'), $text);

		#
		# Last, handle reference-style shortcuts: [link text]
		# These must come last in case you've also got [link text][1]
		# or [link text](/foo)
		#
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs',
			array($this, '_doAnchors_reference_callback'), $text);

		$this->in_anchor = false;
		return $text;
	}
	protected function _doAnchors_reference_callback($matches) {
		$whole_match =  $matches[1];
		$link_text   =  $matches[2];
		$link_id     =& $matches[3];

		if ($link_id == "") {
			# for shortcut links like [this][] or [this].
			$link_id = $link_text;
		}

		# lower-case and turn embedded newlines into spaces
		$link_id = strtolower($link_id);
		$link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

		if (isset($this->urls[$link_id])) {
			$url = $this->urls[$link_id];
			$url = $this->encodeURLAttribute($url);

			$result = "<a href=\"$url\"";
			if ( isset( $this->titles[$link_id] ) ) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
			if (isset($this->ref_attr[$link_id]))
				$result .= $this->ref_attr[$link_id];

			$link_text = $this->runSpanGamut($link_text);
			$result .= ">$link_text</a>";
			$result = $this->hashPart($result);
		}
		else {
			$result = $whole_match;
		}
		return $result;
	}
	protected function _doAnchors_inline_callback($matches) {
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];
		$attr  = $this->doExtraAttributes("a", $dummy =& $matches[8]);

		// if the URL was of the form <s p a c e s> it got caught by the HTML
		// tag parser and hashed. Need to reverse the process before using the URL.
		$unhashed = $this->unhash($url);
		if ($unhashed != $url)
			$url = preg_replace('/^<(.*)>$/', '\1', $unhashed);

		$url = $this->encodeURLAttribute($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\"";
		}
		$result .= $attr;

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}


	protected function doImages($text) {
	#
	# Turn Markdown image shortcuts into <img> tags.
	#
		#
		# First, handle reference-style labeled images: ![alt text][id]
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs',
			array($this, '_doImages_reference_callback'), $text);

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				('.$this->nested_brackets_re.')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			  (?:[ ]? '.$this->id_class_attr_catch_re.' )?	 # $8 = id/class attributes
			)
			}xs',
			array($this, '_doImages_inline_callback'), $text);

		return $text;
	}
	protected function _doImages_reference_callback($matches) {
		$whole_match = $matches[1];
		$alt_text    = $matches[2];
		$link_id     = strtolower($matches[3]);

		if ($link_id == "") {
			$link_id = strtolower($alt_text); # for shortcut links like ![this][].
		}

		$alt_text = $this->encodeAttribute($alt_text);
		if (isset($this->urls[$link_id])) {
			$url = $this->encodeURLAttribute($this->urls[$link_id]);
			$result = "<img src=\"$url\" alt=\"$alt_text\"";
			if (isset($this->titles[$link_id])) {
				$title = $this->titles[$link_id];
				$title = $this->encodeAttribute($title);
				$result .=  " title=\"$title\"";
			}
			if (isset($this->ref_attr[$link_id]))
				$result .= $this->ref_attr[$link_id];
			$result .= $this->empty_element_suffix;
			$result = $this->hashPart($result);
		}
		else {
			# If there's no such link ID, leave intact:
			$result = $whole_match;
		}

		return $result;
	}
	protected function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];
		$attr  = $this->doExtraAttributes("img", $dummy =& $matches[8]);

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeURLAttribute($url);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; # $title already quoted
		}
		$result .= $attr;
		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}


	protected function doHeaders($text) {
	#
	# Redefined to add id and class attribute support.
	#
		# Setext-style headers:
		#	  Header 1  {#header1}
		#	  ========
		#
		#	  Header 2  {#header2 .class1 .class2}
		#	  --------
		#
		$text = preg_replace_callback(
			'{
				(^.+?)								# $1: Header text
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*\n(=+|-+)[ ]*\n+				# $3: Header footer
			}mx',
			array($this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1        {#header1}
		#	## Header 2       {#header2}
		#	## Header 2 with closing hashes ##  {#header3.class1.class2}
		#	...
		#	###### Header 6   {.class2}
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				(?:[ ]+ '.$this->id_class_attr_catch_re.' )?	 # $3 = id/class attributes
				[ ]*
				\n+
			}xm',
			array($this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
	protected function _doHeaders_callback_setext($matches) {
		if ($matches[3] == '-' && preg_match('{^- }', $matches[1]))
			return $matches[0];
		$level = $matches[3]{0} == '=' ? 1 : 2;
		$attr  = $this->doExtraAttributes("h$level", $dummy =& $matches[2]);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
	protected function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);
		$attr  = $this->doExtraAttributes("h$level", $dummy =& $matches[3]);
		$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}


	protected function doTables($text) {
	#
	# Form HTML tables.
	#
		$less_than_tab = $this->tab_width - 1;
		#
		# Find tables with leading pipe.
		#
		#	| Header 1 | Header 2
		#	| -------- | --------
		#	| Cell 1   | Cell 2
		#	| Cell 3   | Cell 4
		#
		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|]							# Optional leading pipe (present)
				(.+) \n						# $1: Header row (at least one pipe)

				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				[|] ([ ]*[-:]+[-| :]*) \n	# $2: Header underline

				(							# $3: Cells
					(?>
						[ ]*				# Allowed whitespace.
						[|] .* \n			# Row content.
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array($this, '_doTable_leadingPipe_callback'), $text);

		#
		# Find tables without leading pipe.
		#
		#	Header 1 | Header 2
		#	-------- | --------
		#	Cell 1   | Cell 2
		#	Cell 3   | Cell 4
		#
		$text = preg_replace_callback('
			{
				^							# Start of a line
				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				(\S.*[|].*) \n				# $1: Header row (at least one pipe)

				[ ]{0,'.$less_than_tab.'}	# Allowed whitespace.
				([-:]+[ ]*[|][-| :]*) \n	# $2: Header underline

				(							# $3: Cells
					(?>
						.* [|] .* \n		# Row content
					)*
				)
				(?=\n|\Z)					# Stop at final double newline.
			}xm',
			array($this, '_DoTable_callback'), $text);

		return $text;
	}
	protected function _doTable_leadingPipe_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];

		# Remove leading pipe for each row.
		$content	= preg_replace('/^ *[|]/m', '', $content);

		return $this->_doTable_callback(array($matches[0], $head, $underline, $content));
	}
	protected function _doTable_makeAlignAttr($alignname)
	{
		if (empty($this->table_align_class_tmpl))
			return " align=\"$alignname\"";

		$classname = str_replace('%%', $alignname, $this->table_align_class_tmpl);
		return " class=\"$classname\"";
	}
	protected function _doTable_callback($matches) {
		$head		= $matches[1];
		$underline	= $matches[2];
		$content	= $matches[3];

		# Remove any tailing pipes for each line.
		$head		= preg_replace('/[|] *$/m', '', $head);
		$underline	= preg_replace('/[|] *$/m', '', $underline);
		$content	= preg_replace('/[|] *$/m', '', $content);

		# Reading alignement from header underline.
		$separators	= preg_split('/ *[|] */', $underline);
		foreach ($separators as $n => $s) {
			if (preg_match('/^ *-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('right');
			else if (preg_match('/^ *:-+: *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('center');
			else if (preg_match('/^ *:-+ *$/', $s))
				$attr[$n] = $this->_doTable_makeAlignAttr('left');
			else
				$attr[$n] = '';
		}

		# Parsing span elements, including code spans, character escapes,
		# and inline HTML tags, so that pipes inside those gets ignored.
		$head		= $this->parseSpan($head);
		$headers	= preg_split('/ *[|] */', $head);
		$col_count	= count($headers);
		$attr       = array_pad($attr, $col_count, '');

		# Write column headers.
		$text = "<table>\n";
		$text .= "<thead>\n";
		$text .= "<tr>\n";
		foreach ($headers as $n => $header)
			$text .= "  <th$attr[$n]>".$this->runSpanGamut(trim($header))."</th>\n";
		$text .= "</tr>\n";
		$text .= "</thead>\n";

		# Split content by row.
		$rows = explode("\n", trim($content, "\n"));

		$text .= "<tbody>\n";
		foreach ($rows as $row) {
			# Parsing span elements, including code spans, character escapes,
			# and inline HTML tags, so that pipes inside those gets ignored.
			$row = $this->parseSpan($row);

			# Split row by cell.
			$row_cells = preg_split('/ *[|] */', $row, $col_count);
			$row_cells = array_pad($row_cells, $col_count, '');

			$text .= "<tr>\n";
			foreach ($row_cells as $n => $cell)
				$text .= "  <td$attr[$n]>".$this->runSpanGamut(trim($cell))."</td>\n";
			$text .= "</tr>\n";
		}
		$text .= "</tbody>\n";
		$text .= "</table>";

		return $this->hashBlock($text) . "\n";
	}


	protected function doDefLists($text) {
	#
	# Form HTML definition lists.
	#
		$less_than_tab = $this->tab_width - 1;

		# Re-usable pattern to match any entire dl list:
		$whole_list_re = '(?>
			(								# $1 = whole list
			  (								# $2
				[ ]{0,'.$less_than_tab.'}
				((?>.*\S.*\n)+)				# $3 = defined term
				\n?
				[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
			  )
			  (?s:.+?)
			  (								# $4
				  \z
				|
				  \n{2,}
				  (?=\S)
				  (?!						# Negative lookahead for another term
					[ ]{0,'.$less_than_tab.'}
					(?: \S.*\n )+?			# defined term
					\n?
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
				  (?!						# Negative lookahead for another definition
					[ ]{0,'.$less_than_tab.'}:[ ]+ # colon starting definition
				  )
			  )
			)
		)'; // mx

		$text = preg_replace_callback('{
				(?>\A\n?|(?<=\n\n))
				'.$whole_list_re.'
			}mx',
			array($this, '_doDefLists_callback'), $text);

		return $text;
	}
	protected function _doDefLists_callback($matches) {
		# Re-usable patterns to match list item bullets and number markers:
		$list = $matches[1];

		# Turn double returns into triple returns, so that we can make a
		# paragraph for the last item in a list, if necessary:
		$result = trim($this->processDefListItems($list));
		$result = "<dl>\n" . $result . "\n</dl>";
		return $this->hashBlock($result) . "\n\n";
	}


	protected function processDefListItems($list_str) {
	#
	#	Process the contents of a single definition list, splitting it
	#	into individual term and definition list items.
	#
		$less_than_tab = $this->tab_width - 1;

		# trim trailing blank lines:
		$list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

		# Process definition terms.
		$list_str = preg_replace_callback('{
			(?>\A\n?|\n\n+)					# leading line
			(								# definition terms = $1
				[ ]{0,'.$less_than_tab.'}	# leading whitespace
				(?!\:[ ]|[ ])				# negative lookahead for a definition
											#   mark (colon) or more whitespace.
				(?> \S.* \n)+?				# actual term (not whitespace).
			)
			(?=\n?[ ]{0,3}:[ ])				# lookahead for following line feed
											#   with a definition mark.
			}xm',
			array($this, '_processDefListItems_callback_dt'), $list_str);

		# Process actual definitions.
		$list_str = preg_replace_callback('{
			\n(\n+)?						# leading line = $1
			(								# marker space = $2
				[ ]{0,'.$less_than_tab.'}	# whitespace before colon
				\:[ ]+						# definition mark (colon)
			)
			((?s:.+?))						# definition text = $3
			(?= \n+ 						# stop at next definition mark,
				(?:							# next term or end of text
					[ ]{0,'.$less_than_tab.'} \:[ ]	|
					<dt> | \z
				)
			)
			}xm',
			array($this, '_processDefListItems_callback_dd'), $list_str);

		return $list_str;
	}
	protected function _processDefListItems_callback_dt($matches) {
		$terms = explode("\n", trim($matches[1]));
		$text = '';
		foreach ($terms as $term) {
			$term = $this->runSpanGamut(trim($term));
			$text .= "\n<dt>" . $term . "</dt>";
		}
		return $text . "\n";
	}
	protected function _processDefListItems_callback_dd($matches) {
		$leading_line	= $matches[1];
		$marker_space	= $matches[2];
		$def			= $matches[3];

		if ($leading_line || preg_match('/\n{2,}/', $def)) {
			# Replace marker with the appropriate whitespace indentation
			$def = str_repeat(' ', strlen($marker_space)) . $def;
			$def = $this->runBlockGamut($this->outdent($def . "\n\n"));
			$def = "\n". $def ."\n";
		}
		else {
			$def = rtrim($def);
			$def = $this->runSpanGamut($this->outdent($def));
		}

		return "\n<dd>" . $def . "</dd>\n";
	}


	protected function doFencedCodeBlocks($text) {
	#
	# Adding the fenced code block syntax to regular Markdown:
	#
	# ~~~
	# Code block
	# ~~~
	#
		$less_than_tab = $this->tab_width;

		$text = preg_replace_callback('{
				(?:\n|\A)
				# 1: Opening marker
				(
					(?:~{3,}|`{3,}) # 3 or more tildes/backticks.
				)
				[ ]*
				(?:
					\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
				|
					'.$this->id_class_attr_catch_re.' # 3: Extra attributes
				)?
				[ ]* \n # Whitespace and newline following marker.

				# 4: Content
				(
					(?>
						(?!\1 [ ]* \n)	# Not a closing marker.
						.*\n+
					)+
				)

				# Closing marker.
				\1 [ ]* (?= \n )
			}xm',
			array($this, '_doFencedCodeBlocks_callback'), $text);

		return $text;
	}
	protected function _doFencedCodeBlocks_callback($matches) {
		$classname =& $matches[2];
		$attrs     =& $matches[3];
		$codeblock = $matches[4];
		$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
		$codeblock = preg_replace_callback('/^\n+/',
			array($this, '_doFencedCodeBlocks_newlines'), $codeblock);

		if ($classname != "") {
			if ($classname{0} == '.')
				$classname = substr($classname, 1);
			$attr_str = ' class="'.$this->code_class_prefix.$classname.'"';
		} else {
			$attr_str = $this->doExtraAttributes($this->code_attr_on_pre ? "pre" : "code", $attrs);
		}
		$pre_attr_str  = $this->code_attr_on_pre ? $attr_str : '';
		$code_attr_str = $this->code_attr_on_pre ? '' : $attr_str;
		$codeblock  = "<pre$pre_attr_str><code$code_attr_str>$codeblock</code></pre>";

		return "\n\n".$this->hashBlock($codeblock)."\n\n";
	}
	protected function _doFencedCodeBlocks_newlines($matches) {
		return str_repeat("<br$this->empty_element_suffix",
			strlen($matches[0]));
	}


	#
	# Redefining emphasis markers so that emphasis by underscore does not
	# work in the middle of a word.
	#
	protected $em_relist = array(
		''  => '(?:(?<!\*)\*(?!\*)|(?<![a-zA-Z0-9_])_(?!_))(?![\.,:;]?\s)',
		'*' => '(?<![\s*])\*(?!\*)',
		'_' => '(?<![\s_])_(?![a-zA-Z0-9_])',
		);
	protected $strong_relist = array(
		''   => '(?:(?<!\*)\*\*(?!\*)|(?<![a-zA-Z0-9_])__(?!_))(?![\.,:;]?\s)',
		'**' => '(?<![\s*])\*\*(?!\*)',
		'__' => '(?<![\s_])__(?![a-zA-Z0-9_])',
		);
	protected $em_strong_relist = array(
		''    => '(?:(?<!\*)\*\*\*(?!\*)|(?<![a-zA-Z0-9_])___(?!_))(?![\.,:;]?\s)',
		'***' => '(?<![\s*])\*\*\*(?!\*)',
		'___' => '(?<![\s_])___(?![a-zA-Z0-9_])',
		);


	protected function formParagraphs($text) {
	#
	#	Params:
	#		$text - string to process with html <p> tags
	#
		# Strip leading and trailing lines:
		$text = preg_replace('/\A\n+|\n+\z/', '', $text);

		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value) {
			$value = trim($this->runSpanGamut($value));

			# Check if this should be enclosed in a paragraph.
			# Clean tag hashes & block tag hashes are left alone.
			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);

			if ($is_p) {
				$value = "<p>$value</p>";
			}
			$grafs[$key] = $value;
		}

		# Join grafs in one text, then unhash HTML tags.
		$text = implode("\n\n", $grafs);

		# Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);

		return $text;
	}


	### Footnotes

	protected function stripFootnotes($text) {
	#
	# Strips link definitions from text, stores the URLs and titles in
	# hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: [^id]: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\[\^(.+?)\][ ]?:	# note_id = $1
			  [ ]*
			  \n?					# maybe *one* newline
			(						# text = $2 (no blank lines allowed)
				(?:
					.+				# actual text
				|
					\n				# newlines but
					(?!\[.+?\][ ]?:\s)# negative lookahead for footnote or link definition marker.
					(?!\n+[ ]{0,3}\S)# ensure line is not blank and followed
									# by non-indented content
				)*
			)
			}xm',
			array($this, '_stripFootnotes_callback'),
			$text);
		return $text;
	}
	protected function _stripFootnotes_callback($matches) {
		$note_id = $this->fn_id_prefix . $matches[1];
		$this->footnotes[$note_id] = $this->outdent($matches[2]);
		return ''; # String that will replace the block
	}


	protected function doFootnotes($text) {
	#
	# Replace footnote references in $text [^id] with a special text-token
	# which will be replaced by the actual footnote marker in appendFootnotes.
	#
		if (!$this->in_anchor) {
			$text = preg_replace('{\[\^(.+?)\]}', "F\x1Afn:\\1\x1A:", $text);
		}
		return $text;
	}


	protected function appendFootnotes($text) {
	#
	# Append footnote list to text.
	#
		$text = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}',
			array($this, '_appendFootnotes_callback'), $text);

		if (!empty($this->footnotes_ordered)) {
			$text .= "\n\n";
			$text .= "<div class=\"footnotes\">\n";
			$text .= "<hr". $this->empty_element_suffix ."\n";
			$text .= "<ol>\n\n";

			$attr = "";
			if ($this->fn_backlink_class != "") {
				$class = $this->fn_backlink_class;
				$class = $this->encodeAttribute($class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_backlink_title != "") {
				$title = $this->fn_backlink_title;
				$title = $this->encodeAttribute($title);
				$attr .= " title=\"$title\"";
			}
			$num = 0;

			while (!empty($this->footnotes_ordered)) {
				$footnote = reset($this->footnotes_ordered);
				$note_id = key($this->footnotes_ordered);
				unset($this->footnotes_ordered[$note_id]);
				$ref_count = $this->footnotes_ref_count[$note_id];
				unset($this->footnotes_ref_count[$note_id]);
				unset($this->footnotes[$note_id]);

				$footnote .= "\n"; # Need to append newline before parsing.
				$footnote = $this->runBlockGamut("$footnote\n");
				$footnote = preg_replace_callback('{F\x1Afn:(.*?)\x1A:}',
					array($this, '_appendFootnotes_callback'), $footnote);

				$attr = str_replace("%%", ++$num, $attr);
				$note_id = $this->encodeAttribute($note_id);

				# Prepare backlink, multiple backlinks if multiple references
				$backlink = "<a href=\"#fnref:$note_id\"$attr>&#8617;</a>";
				for ($ref_num = 2; $ref_num <= $ref_count; ++$ref_num) {
					$backlink .= " <a href=\"#fnref$ref_num:$note_id\"$attr>&#8617;</a>";
				}
				# Add backlink to last paragraph; create new paragraph if needed.
				if (preg_match('{</p>$}', $footnote)) {
					$footnote = substr($footnote, 0, -4) . "&#160;$backlink</p>";
				} else {
					$footnote .= "\n\n<p>$backlink</p>";
				}

				$text .= "<li id=\"fn:$note_id\">\n";
				$text .= $footnote . "\n";
				$text .= "</li>\n\n";
			}

			$text .= "</ol>\n";
			$text .= "</div>";
		}
		return $text;
	}
	protected function _appendFootnotes_callback($matches) {
		$node_id = $this->fn_id_prefix . $matches[1];

		# Create footnote marker only if it has a corresponding footnote *and*
		# the footnote hasn't been used by another marker.
		if (isset($this->footnotes[$node_id])) {
			$num =& $this->footnotes_numbers[$node_id];
			if (!isset($num)) {
				# Transfer footnote content to the ordered list and give it its
				# number
				$this->footnotes_ordered[$node_id] = $this->footnotes[$node_id];
				$this->footnotes_ref_count[$node_id] = 1;
				$num = $this->footnote_counter++;
				$ref_count_mark = '';
			} else {
				$ref_count_mark = $this->footnotes_ref_count[$node_id] += 1;
			}

			$attr = "";
			if ($this->fn_link_class != "") {
				$class = $this->fn_link_class;
				$class = $this->encodeAttribute($class);
				$attr .= " class=\"$class\"";
			}
			if ($this->fn_link_title != "") {
				$title = $this->fn_link_title;
				$title = $this->encodeAttribute($title);
				$attr .= " title=\"$title\"";
			}

			$attr = str_replace("%%", $num, $attr);
			$node_id = $this->encodeAttribute($node_id);

			return
				"<sup id=\"fnref$ref_count_mark:$node_id\">".
				"<a href=\"#fn:$node_id\"$attr>$num</a>".
				"</sup>";
		}

		return "[^".$matches[1]."]";
	}


	### Abbreviations ###

	protected function stripAbbreviations($text) {
	#
	# Strips abbreviations from text, stores titles in hash references.
	#
		$less_than_tab = $this->tab_width - 1;

		# Link defs are in the form: [id]*: url "optional title"
		$text = preg_replace_callback('{
			^[ ]{0,'.$less_than_tab.'}\*\[(.+?)\][ ]?:	# abbr_id = $1
			(.*)					# text = $2 (no blank lines allowed)
			}xm',
			array($this, '_stripAbbreviations_callback'),
			$text);
		return $text;
	}
	protected function _stripAbbreviations_callback($matches) {
		$abbr_word = $matches[1];
		$abbr_desc = $matches[2];
		if ($this->abbr_word_re)
			$this->abbr_word_re .= '|';
		$this->abbr_word_re .= preg_quote($abbr_word);
		$this->abbr_desciptions[$abbr_word] = trim($abbr_desc);
		return ''; # String that will replace the block
	}


	protected function doAbbreviations($text) {
	#
	# Find defined abbreviations in text and wrap them in <abbr> elements.
	#
		if ($this->abbr_word_re) {
			// cannot use the /x modifier because abbr_word_re may
			// contain significant spaces:
			$text = preg_replace_callback('{'.
				'(?<![\w\x1A])'.
				'(?:'.$this->abbr_word_re.')'.
				'(?![\w\x1A])'.
				'}',
				array($this, '_doAbbreviations_callback'), $text);
		}
		return $text;
	}
	protected function _doAbbreviations_callback($matches) {
		$abbr = $matches[0];
		if (isset($this->abbr_desciptions[$abbr])) {
			$desc = $this->abbr_desciptions[$abbr];
			if (empty($desc)) {
				return $this->hashPart("<abbr>$abbr</abbr>");
			} else {
				$desc = $this->encodeAttribute($desc);
				return $this->hashPart("<abbr title=\"$desc\">$abbr</abbr>");
			}
		} else {
			return $matches[0];
		}
	}

}
