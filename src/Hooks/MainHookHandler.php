<?php

namespace Liquipedia\Extension\TournamentsMenu\Hooks;

use Liquipedia\Extension\TournamentsMenu\Data;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SkinBuildSidebarHook;
use Title;

class MainHookHandler implements
	ParserFirstCallInitHook,
	SkinBuildSidebarHook
{

	/**
	 * Hook callback for 'ParserFirstCallInit'
	 * @param Parser $parser Parser object
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook(
			'tournaments',
			'Liquipedia\Extension\TournamentsMenu\ParserFunction::getTournamentsList'
		);
	}

	/**
	 * Hook callback for 'SkinBuildSidebar'
	 * @param Skin $skin Skin object for context purposes
	 * @param array &$bar array that holds the sidebar
	 * @return array|null tournament list
	 */
	public function onSkinBuildSidebar( $skin, &$bar ) {
		$key = 'TOURNAMENTS';
		if ( array_key_exists( $key, $bar ) ) {
			$out = $skin->getOutput();
			$commandLineMode = $out->getConfig()->get( 'CommandLineMode' );
			$message = Data::getStandardPageName();
			$iconTemplatePrefix = Data::getIconPrefix();

			$titleFromText = Title::newFromText( $message, NS_PROJECT );
			$tournamentsData = Data::getFromTitle( $titleFromText );

			if ( $tournamentsData !== null ) {
				$tournamentsMenu = [];

				foreach ( $tournamentsData as $heading => $tournaments ) {
					if ( !array_key_exists( $heading, $tournamentsMenu ) ) {
						$tournamentsMenu[ $heading ] = [];
					}
					foreach ( $tournaments as $tournament ) {
						$text = $tournament[ 'text' ];
						$data = [
							'href' => $tournament[ 'href' ],
							'id' => $tournament[ 'id' ],
							'active' => $tournament[ 'active' ],
						];

						// Should we add an icon
						// icon = SMW.Is part of series or LPDB.series
						// iconfile = SMW.Has icon or LPDB.icon
						if ( array_key_exists( 'icon', $tournament ) ) {
							$iconTitle = Title::newFromText(
									$iconTemplatePrefix . '/' . $tournament[ 'icon' ],
									NS_TEMPLATE
							);
							if ( $iconTitle !== null && $iconTitle->exists() && $skin->getTitle() !== null ) {
								if ( !$commandLineMode ) {
									$iconHTML = $out->parseInlineAsInterface(
										'{{' . $iconTemplatePrefix . '/' . $tournament[ 'icon' ] . '|link=}}',
										false
									);
									if ( strpos( $iconHTML, 'mw-parser-output' ) !== false ) {
										$iconHTML = substr(
											$iconHTML,
											strlen( '<div class="mw-parser-output">' ),
											-strlen( '</div>' )
										);
									}
									$text = $iconHTML . ' ' . $text;
								}
							}
						} elseif ( array_key_exists( 'iconfile', $tournament ) ) {
							$iconfileTitle = Title::newFromText(
									$iconTemplatePrefix . '/mainpageTST',
									NS_TEMPLATE
							);
							if ( $iconfileTitle !== null && $iconfileTitle->exists() && $skin->getTitle() !== null ) {
								if ( !$commandLineMode ) {
									$iconHTML = $out->parseInlineAsInterface(
										'{{' . $iconTemplatePrefix . '/mainpageTST|' .
										( array_key_exists( 'icondarkfile', $tournament )
											? 'iconDark=' . $tournament[ 'icondarkfile' ] . '|'
											: '' ) .
										$tournament[ 'iconfile' ] . '|link=}}',
										false
									);
									if ( strpos( $iconHTML, 'mw-parser-output' ) !== false ) {
										$iconHTML = substr(
											$iconHTML,
											strlen( '<div class="mw-parser-output">' ),
											-strlen( '</div>' )
										);
									}
									$text = $iconHTML . ' ' . $text;
								}
							}
						}

						$data[ 'text' ] = $text;

						$tournamentsMenu[ $heading ][] = $data;
					}
				}

				$bar[ $key ] = $tournamentsMenu;
			}
		}
		return true;
	}

}
