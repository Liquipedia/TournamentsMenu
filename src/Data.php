<?php

namespace Liquipedia\Extension\TournamentsMenu;

use ContentHandler;
use MediaWiki\Revision\SlotRecord;
use Title;
use WikiPage;

class Data {

	/**
	 * Parse the page requested by $title, and return its data, or null
	 * if the page does not exist
	 * @param Title $title Title object for data page
	 * @return array|null Tournament list
	 */
	public static function getFromTitle( $title ) {
		if ( $title->exists() ) {
			$tournamentData = [];
			$wikipage = WikiPage::factory( $title );
			$revision = $wikipage->getRevisionRecord();
			if ( !$revision ) {
				return true;
			}
			$content = $revision->getContent( SlotRecord::MAIN );
			$text = ContentHandler::getContentText( $content );
			$lines = explode( "\n", $text );

			$heading = '';
			foreach ( $lines as $line ) {
				if ( strpos( $line, '*' ) !== 0 ) {
					continue;
				} elseif ( strpos( $line, '**' ) !== 0 ) {
					$line = trim( $line, '* ' );
					$heading = htmlspecialchars( $line );
					if ( !array_key_exists( $heading, $tournamentData ) ) {
						$tournamentData[ $heading ] = [];
					}
				} else {
					// sanity check
					if ( strpos( $line, '|' ) !== false ) {
						$line = array_map( 'trim', explode( '|', trim( $line, '* ' ) ) );

						foreach ( $line as $key => $value ) {
							$value = trim( $value );
							if ( strpos( $value, 'startdate' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$startDate = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							} elseif ( strpos( $value, 'enddate' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$endDate = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							} elseif ( strpos( $value, 'iconfile' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$iconfile = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							} elseif ( strpos( $value, 'icondarkfile' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$icondarkfile = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							} elseif ( strpos( $value, 'icon' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$icon = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							} elseif ( strpos( $value, 'filter' ) === 0 ) {
								if ( !empty( trim( explode( '=', $value )[ 1 ] ) ) ) {
									$filter = htmlspecialchars( trim( explode( '=', $value )[ 1 ] ) );
								}
								unset( $line[ $key ] );
							}
						}
						$line = array_values( $line );
						if ( count( $line ) == 1 ) {
							$line[ 1 ] = $line[ 0 ];
						}

						if ( $line[ 0 ] == null ) {
							$link = '-';
						} else {
							$link = wfMessage( $line[ 0 ] )->inContentLanguage()->text();
						}
						if ( $link == '-' ) {
							continue;
						}

						$text = wfMessage( $line[ 1 ] )->text();
						if ( wfMessage( $line[ 1 ], $text )->inContentLanguage()->isBlank() ) {
							$text = $line[ 1 ];
						}
						if ( wfMessage( $line[ 0 ], $link )->inContentLanguage()->isBlank() ) {
							$link = $line[ 0 ];
						}

						if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
							$href = $link;
						} else {
							$targetTitle = Title::newFromText( $link );
							if ( $targetTitle !== null ) {
								$targetTitle = $targetTitle->fixSpecialName();
								$href = $targetTitle->getLocalURL();
							} else {
								$href = 'INVALID-TITLE';
							}
						}
						if ( $targetTitle === null ) {
							$exists = false;
						} else {
							$exists = $targetTitle->exists();
						}

						$text = htmlspecialchars( $text );

						$data = [
							'text' => $text,
							'href' => $href,
							'id' => 'n-' . strtr( $line[ 1 ], ' ', '-' ),
							'active' => false,
							'exists' => $exists,
						];
						if ( isset( $startDate ) ) {
							$data[ 'startdate' ] = $startDate;
						}
						if ( isset( $endDate ) ) {
							$data[ 'enddate' ] = $endDate;
						}
						if ( isset( $icon ) ) {
							$data[ 'icon' ] = $icon;
						}
						if ( isset( $iconfile ) ) {
							$data[ 'iconfile' ] = $iconfile;
						}
						if ( isset( $icondarkfile ) ) {
							$data[ 'icondarkfile' ] = $icondarkfile;
						}
						if ( isset( $filter ) ) {
							$data[ 'filter' ] = $filter;
						}
						unset( $startDate, $endDate, $icon, $iconfile, $icondarkfile, $filter );

						$tournamentData[ $heading ][] = $data;
					} else {
						$line = trim( $line, '* ' );
						$text = htmlspecialchars( $line );
						$link = $line;
						$targetTitle = Title::newFromText( $link );
						if ( $targetTitle !== null ) {
							$targetTitle = $targetTitle->fixSpecialName();
							$href = $targetTitle->getLocalURL();
						} else {
							$href = 'INVALID-TITLE';
						}

						$data = [
							'text' => $text,
							'href' => $href,
							'id' => 'n-' . strtr( $line, ' ', '-' ),
							'active' => false,
						];

						$tournamentData[ $heading ][] = $data;
					}
				}
			}
			return $tournamentData;
		} else {
			return null;
		}
	}

	/**
	 * Return the default name which page to take
	 * @return string name of page
	 */
	public static function getStandardPageName() {
		return 'Tournaments';
	}

	/**
	 * Return the icon prefix for images
	 * @return string icon prefix
	 */
	public static function getIconPrefix() {
		return 'LeagueIconSmall';
	}

}
