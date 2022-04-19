<?php

namespace Liquipedia\Extension\TournamentsMenu;

use Parser;
use Title;

class ParserFunction {

	/**
	 * Callback for tournaments parser hook
	 * @param string $content
	 * @param array $attributes
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string tournament list as html
	 */
	public static function getTournamentsList( $content, $attributes, $parser, $frame ) {
		if ( isset( $attributes[ 'page' ] ) && !empty( $attributes[ 'page' ] ) ) {
			$message = htmlspecialchars( $attributes[ 'page' ] );
		} else {
			$message = Data::getStandardPageName();
		}
		if ( isset( $attributes[ 'filter' ] ) && !empty( $attributes[ 'filter' ] ) ) {
			$filters = explode( ',', htmlspecialchars( $attributes[ 'filter' ] ) );
		}
		$iconTemplatePrefix = Data::getIconPrefix();
		$return = '';

		$titleFromText = Title::newFromText( $message, NS_PROJECT );
		$tournamentsData = Data::getFromTitle( $titleFromText );
		if ( $tournamentsData !== null ) {
			if ( isset( $filters ) && count( $filters ) > 1 ) {
				$return .= '<form>';
				$helperA = '';
				$helperB = '';
				foreach ( $filters as $filter ) {
					$helperA .= '<input type="checkbox" id="tournaments-list-filter-' . $filter . '">';
					$helperB .= '<label for="tournaments-list-filter-' . $filter . '"></label>';
				}
				$return .= $helperA . $helperB;
				unset( $helperA, $helperB );
			}
			$return .= '<ul class="tournaments-list">';
			foreach ( $tournamentsData as $heading => $tournaments ) {
				$return .= '<li>';
				$return .= '<span class="tournaments-list-heading">' . $heading . '</span>';
				$return .= '<ul class="tournaments-list-type-list">';
				foreach ( $tournaments as $tournament ) {
					$filterAttribute = '';
					if ( isset( $tournament[ 'filter' ] ) ) {
						$filterAttribute = ' class="filter-' . $tournament[ 'filter' ] . '"';
					}
					$return .= '<li' . $filterAttribute . '>';
					$newAttribute = '';

					if ( !$tournament[ 'exists' ] ) {
						$newAttribute = ' class="new"';
					}
					$return .= '<a ' . $newAttribute . 'href="' . $tournament[ 'href' ] . '">';
					$return .= '<span class="tournaments-list-name">';

					// Should we add an icon
					// icon = SMW.Is part of series or LPDB.series
					// iconfile = SMW.Has icon or LPDB.icon
					if ( array_key_exists( 'icon', $tournament ) ) {
						$iconTitle = Title::newFromText(
								$iconTemplatePrefix . '/' . $tournament[ 'icon' ],
								NS_TEMPLATE
						);
						if (
							array_key_exists( 'icon', $tournament )
							&& $iconTitle !== null
							&& $iconTitle->exists()
							&& $parser->getTitle() !== null
						) {
							$parserOptions = $parser->getOptions();
							$wasParserReportEnabled = $parserOptions->getOption( 'enableLimitReport' );
							$parserOptions->setOption( 'enableLimitReport', false );
							$iconHTML = $parser->parse(
									'{{' . $iconTemplatePrefix . '/' . $tournament[ 'icon' ] . '|link=}}',
									$parser->getTitle(),
									$parserOptions,
									false,
									false
								)->getText();
							$parserOptions->setOption( 'enableLimitReport', $wasParserReportEnabled );
							if ( strpos( $iconHTML, 'mw-parser-output' ) !== false ) {
								$iconHTML = substr(
									$iconHTML,
									strlen( '<div class="mw-parser-output">' ),
									-strlen( '</div>' )
								);
							}
							$return .= Parser::stripOuterParagraph( $iconHTML );
						}
					} elseif ( array_key_exists( 'iconfile', $tournament ) ) {
						$iconfileTitle = Title::newFromText(
								$iconTemplatePrefix . '/mainpageTST',
								NS_TEMPLATE
						);
						if (
							array_key_exists( 'iconfile', $tournament )
							&& $iconfileTitle !== null
							&& $iconfileTitle->exists()
							&& $parser->getTitle() !== null
						) {
							$parserOptions = $parser->getOptions();
							$wasParserReportEnabled = $parserOptions->getOption( 'enableLimitReport' );
							$parserOptions->setOption( 'enableLimitReport', false );
							$iconHTML = $parser->parse(
								'{{' . $iconTemplatePrefix . '/mainpageTST|' .
								( array_key_exists( 'icondarkfile', $tournament )
									? 'iconDark=' . $tournament[ 'icondarkfile' ] . '|'
									: '' ) .
								$tournament[ 'iconfile' ] . '|link=}}',
								$parser->getTitle(),
								$parserOptions,
								false,
								false
							)->getText();
							$parserOptions->setOption( 'enableLimitReport', $wasParserReportEnabled );
							if ( strpos( $iconHTML, 'mw-parser-output' ) !== false ) {
								$iconHTML = substr(
									$iconHTML,
									strlen( '<div class="mw-parser-output">' ),
									-strlen( '</div>' )
								);
							}
							$return .= Parser::stripOuterParagraph( $iconHTML );
						}
					}

					$return .= $tournament[ 'text' ] . '</span>';
					$return .= '<small class="tournaments-list-dates">';
					if ( isset( $tournament[ 'startdate' ] ) && isset( $tournament[ 'enddate' ] ) ) {
						$return .= $tournament[ 'startdate' ];
						$return .= ' &ndash; ';
						if (
							substr(
								$tournament[ 'startdate' ], 0, 3
							) === substr(
								$tournament[ 'enddate' ], 0, 3
							)
						) {
							$tournament[ 'enddate' ] = substr( $tournament[ 'enddate' ], 4 );
						}
						$return .= $tournament[ 'enddate' ];
					} elseif ( isset( $tournament[ 'startdate' ] ) ) {
						$return .= $tournament[ 'startdate' ];
					} elseif ( isset( $tournament[ 'enddate' ] ) ) {
						$return .= $tournament[ 'enddate' ];
					}
					$return .= '</small>';
					$return .= '</a>';
					$return .= '</li>';
				}
				$return .= '</ul>';
				$return .= '</li>';
			}
			$return .= '</ul>';
			if ( isset( $filters ) && count( $filters ) > 1 ) {
				$return .= '</form>';
			}
		} else {
			global $wgMetaNamespace;
			$return .= '<span class="error">'
				. wfMessage(
					'tournamentsmenu-page-not-existing'
				)->params(
					$wgMetaNamespace . ':' . str_replace(
						$wgMetaNamespace . ':', '', $message
					)
				)->text()
				. '</span>';
		}
		return [ trim( $return ), 'markerType' => 'nowiki' ];
	}

}
