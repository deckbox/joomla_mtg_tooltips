<?php
/**
 * @version		0.3.0
 * @package		Mtgtooltip
 * @author      Sebastian Zaha. Part of the code taken from the snippets joomla plugin by Peter van Westen <peter@nonumber.nl>
 * @copyright	Copyright (C) 2012 Sebastian Zaha. All rights reserved.
 * @license		GNU/GPL v2
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemMtgtooltip extends JPlugin {

	function __construct(&$subject, $config )
	{
        parent::__construct( $subject, $config );

        $document = &JFactory::getDocument();
        $document->addScript( 'http://deckbox.org/javascripts/bin/tooltip.js' );

        $this->comment_start = '<!-- START: Mtg -->';
        $this->comment_end = '<!-- END: Mtg -->';
        $this->message_start = '<!--  Mtg Message: ';
        $this->message_end = ' -->';

        // If our pattern is matching in a string between " quotes, we should not parse it, it means it' in a tag property (title, description etc).
        $this->regex = '/(>[^"]*)\{mtg([^}]*)\}([^"]*<)/msU';
	}

	////////////////////////////////////////////////////////////////////
	// onPrepareContent
	////////////////////////////////////////////////////////////////////

	function onPrepareContent( &$article )
	{
		if ( isset( $article->text ) ) {
			$this->replaceTags( $article->text );
		}
		if ( isset( $article->description ) ) {
			$this->replaceTags( $article->description );
		}
		if ( isset( $article->title ) ) {
			$this->replaceTags( $article->title );
		}
		if ( isset( $article->author ) ) {
			if ( isset( $article->author->name ) ) {
				$this->replaceTags( $article->author->name );
			} else if ( is_string( $article->author ) ) {
				$this->replaceTags( $article->author );
			}
		}
	}

	////////////////////////////////////////////////////////////////////
	// onAfterDispatch
	////////////////////////////////////////////////////////////////////

	function onAfterDispatch()
	{
		$document = JFactory::getDocument();
		$docType = $document->getType();

		if ( $docType != 'feed' && JRequest::getCmd( 'option' ) != 'com_acymailing' && $docType != 'pdf' ) {
			return;
		}

		if ( ( $docType == 'feed' || JRequest::getCmd( 'option' ) == 'com_acymailing' ) && isset( $document->items ) ) {
			$itemids = array_keys( $document->items );
			foreach ( $itemids as $i ) {
				$this->onPrepareContent( $document->items[$i] );
			}
		}

		// PDF
		if ( $docType == 'pdf' ) {
			if ( isset( $document->_header ) ) {
				$this->replaceTags( $document->_header );
			}
			if ( isset( $document->title ) ) {
				$this->replaceTags( $document->title );
			}
			if ( isset( $document->_buffer ) ) {
				$this->replaceTags( $document->_buffer );
			}
		}
	}

	////////////////////////////////////////////////////////////////////
	// onAfterRender
	////////////////////////////////////////////////////////////////////
	function onAfterRender()
	{
		$document = JFactory::getDocument();
		$docType = $document->getType();

		// not in pdf's
		if ( $docType !== 'html' && $docType !== 'feed' ) {
			return;
		}

		$html = JResponse::getBody();
		if ( $html == '' ) {
			return;
		}

		if ( $docType != 'html' ) {
			$this->replaceTags( $html );
		} else {
			// only do the handling inside the body
			if ( !( strpos( $html, '<body' ) === false ) && !( strpos( $html, '</body>' ) === false ) ) {
				$html_split = explode( '<body', $html, 2 );
				$body_split = explode( '</body>', $html_split['1'], 2 );

				// only do stuff in body
				$this->protect( $body_split['0'] );
				$this->replaceTags( $body_split['0'] );

				$html_split['1'] = implode( '</body>', $body_split );
				$html = implode( '<body', $html_split );
			} else {
				$this->protect( $html );
				$this->replaceTags( $html );
			}
		}

		$this->cleanLeftoverJunk( $html );
		$this->unprotect( $html );

		JResponse::setBody( $html );
	}

	function replaceTags( &$string ) {
		while ( preg_match_all( $this->regex, $string, $matches, PREG_SET_ORDER ) > 0 ) {
			foreach ($matches as $match) {
                $content = $match[2]; $html = '';

                $dirty_lines = preg_split("/[\n\r]/", $content);
                $lines = array();
                foreach ($dirty_lines as $line) {
                    $clean = trim(strip_tags($line));
                    $clean = trim(preg_replace("/[^\d\s\w,]*/", '', $clean));
                    if ($clean !== "") {
                        $lines[] = $clean;
                    }
                }
                foreach ($lines as $line) {
                    $count = '';
                    if (preg_match('/^([0-9]+)(.*)/', $line, $bits)) {
                        $count = $bits[1];
                        $line = trim($bits[2]);
                    }
                    $line = str_replace("’", "'", $line);
                    $html .= $count . '&nbsp;<a href="http://deckbox.org/mtg/'. $line . '">' . $line . '</a><br />';
                }

                $string = str_replace($match['0'], $match['1'] . $html . $match['3'], $string);
			}
		}
	}

	/*
	 * Protect admin form
	 */
	function protect( &$string )
	{
		if ( $this->isEditPage() ) {
			// Protect complete adminForm (to prevent articles from being created when editing articles and such)
			$unprotected = '{mtg';
			$protected = $this->protectStr( $unprotected );
			$string = preg_replace( '#(<'.'form [^>]*(id|name)="(adminForm|postform)")#si', '<!-- TMP_START_EDITOR -->\1', $string );
			$string = explode( '<!-- TMP_START_EDITOR -->', $string );
			foreach ( $string as $i => $str ) {
				if ( !empty( $str ) != '' && fmod( $i, 2 ) ) {
					if ( !( strpos( $str, $unprotected ) === false ) ) {
						$str = explode( '</form>', $str, 2 );
						$str['0'] = str_replace( $unprotected, $protected, $str['0'] );
						$string[$i] = implode( '</form>', $str );
					}
				}
			}
			$string = implode( '', $string );
		}
	}

	function unprotect( &$string )
	{
		$string = str_replace( $this->protectStr( '{mtg' ), '{mtg', $string );
	}

	function protectStr( $string )
	{
		return base64_encode( $string );
	}

	function cleanLeftoverJunk( &$str )
	{
		$str = preg_replace( $this->regex, '', $str );
		$str = preg_replace( '#<\!-- (START|END): SN_[^>]* -->#', '', $str );
		if ( !$this->place_comments ) {
			$str = str_replace( array(
                                      $this->comment_start, $this->comment_end,
                                      htmlentities( $this->comment_start ), htmlentities( $this->comment_end ),
                                      urlencode( $this->comment_start ), urlencode( $this->comment_end )
                                      ), '', $str );
			$str = preg_replace( '#'.preg_quote( $this->message_start, '#' ).'.*?'.preg_quote( $this->message_end, '#' ).'#', '', $str );
		}
	}

    function isEditPage()                    
    {                    
        return (
                JRequest::getCmd( 'task' ) == 'edit'
                || JRequest::getCmd( 'do' ) == 'edit'   
                || in_array( JRequest::getCmd( 'view' ), array( 'edit', 'form' ) )
                || in_array( JRequest::getCmd( 'layout' ), array( 'edit', 'form', 'write' ) )
                || in_array( JRequest::getCmd( 'option' ), array( 'com_contentsubmit', 'com_cckjseblod', 'com_k2' ) ));
    }
}
