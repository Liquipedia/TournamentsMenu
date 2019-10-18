<?php

namespace Liquipedia\TournamentsMenu;

use Title;

class Hooks {

	/**
	 * Hook callback for 'SkinBuildSidebar'
	 * @param Skin $skin Skin object for context purposes
	 * @param array &$bar array that holds the sidebar
	 * @return array|null tournament list
	 */
	public static function onSkinBuildSidebar( $skin, &$bar ) {
		if ( isset( $bar[ 'TOURNAMENTS' ] ) ) {
			$wgOut = $skin->getOutput();
			$wgCommandLineMode = $wgOut->getConfig()->get( 'CommandLineMode' );
			$message = Data::getStandardPageName();
			$iconTemplatePrefix = Data::getIconPrefix();

			$titleFromText = Title::newFromText( $message, NS_PROJECT );
			$tournamentsData = Data::getFromTitle( $titleFromText );

			if ( !is_null( $tournamentsData ) ) {
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
						if ( array_key_exists( 'icon', $tournament ) ) {
							$iconTitle = Title::newFromText(
									$iconTemplatePrefix . '/' . $tournament[ 'icon' ],
									NS_TEMPLATE
							);
							if ( !is_null( $iconTitle ) && $iconTitle->exists() && !is_null( $skin->getTitle() ) ) {
								if ( !$wgCommandLineMode ) {
									$iconHTML = $wgOut->parseInline(
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
						}

						$data[ 'text' ] = $text;

						$tournamentsMenu[ $heading ][] = $data;
					}
				}

				$bar[ 'TOURNAMENTS' ] = $tournamentsMenu;
			}
		}
		return true;
	}

}
