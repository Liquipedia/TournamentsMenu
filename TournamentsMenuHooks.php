<?php

class TournamentsMenuHooks {
	public static function onSkinBuildSidebar( $skin, &$bar ) {
		if(isset($bar['TOURNAMENTS'])) {
			$message = 'Tournaments';

			if ( Title::newFromText( $message, NS_PROJECT )->exists() ) {
				$titleFromText = Title::newFromText( $message, NS_PROJECT );
				$article = WikiPage::factory($titleFromText);
				$text = $article->getText(Revision::FOR_PUBLIC);
				$lines = explode( "\n",  $text );

				$new_bar = array();
				$heading = '';
				foreach ($lines as $line) {
					if (strpos($line, '*') !== 0)
						continue;
					if (strpos($line, '**') !== 0) {
						$line = trim($line, '* ');
						$heading = $line;
						if( !array_key_exists($heading, $new_bar) ) $new_bar[$heading] = array();
					} else {
						if (strpos($line, '|') !== false) { // sanity check
							$line = array_map('trim', explode( '|' , trim($line, '* ') ) );

							foreach( $line as $key => $value ) {
								$value = trim( $value );
								if( strpos( $value, 'startdate' ) === 0 ) {
									if( !empty( trim( explode( '=', $value )[1] ) ) ) {
										$startDate = trim( explode( '=', $value )[1]);
									}
									unset($line[$key]);
								} else if( strpos( $value, 'enddate' ) === 0 ) {
									if( !empty( trim( explode( '=', $value )[1] ) ) ) {
										$endDate = trim( explode( '=', $value )[1]);
									}
									unset($line[$key]);
								} else if( strpos( $value, 'icon' ) === 0 ) {
									if( !empty( trim( explode( '=', $value )[1] ) ) ) {
										$icon = trim( explode( '=', $value )[1]);
									}
									unset($line[$key]);
								}
							}
							$line = array_values( $line );
							if( count( $line ) == 1 ) {
								$line[1] = $line[0];
							}

							
							if($line[0] == null) {
								$link = '-';
							} else {
								$link = wfMessage( $line[0] )->inContentLanguage()->text();
							}
							if ($link == '-')
								continue;

							$text = wfMessage($line[1])->text();
							if (wfMessage($line[1], $text)->inContentLanguage()->isBlank())
								$text = $line[1];
							if (wfMessage($line[0], $link)->inContentLanguage()->isBlank())
								$link = $line[0];

							if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
								$href = $link;
							} else {
								$title = Title::newFromText( $link );
								if ( $title ) {
									$title = $title->fixSpecialName();
									$href = $title->getLocalURL();
								} else {
									$href = 'INVALID-TITLE';
								}
							}

							if( isset($startDate) || isset($endDate) ) {
								$text .= ' <small>(';
								if( isset( $startDate ) ) {
									$text .= $startDate;
								}
								if( isset( $startDate ) && isset( $endDate ) ) {
									$text .= ' - ';
								}
								if( isset( $endDate ) ) {
									if( substr( $startDate, 0, 3 ) == substr( $endDate, 0, 3 ) ) {
										$endDate = substr( $endDate, 4 );
									}
									$text .= $endDate;
								}
								$text .= ')</small>';
							}

							$new_bar[$heading][] = array(
								'text' => $text,
								'href' => $href,
								'id' => 'n-' . strtr($line[1], ' ', '-'),
								'active' => false
							);
							unset($startDate, $endDate, $icon);
						} else { 
							$line = trim($line, '* ');
							//$link = wfMsgForContent( $line );
							//if ($link == '-')
							//	continue;

							$text = $line;
							$link = $line;
							$title = Title::newFromText( $link );
							if ( $title ) {
								$title = $title->fixSpecialName();
								$href = $title->getLocalURL();
							} else {
								$href = 'INVALID-TITLE';
							}
							$new_bar[$heading][] = array(
								'text' => $text,
								'href' => $href,
								'id' => 'n-' . strtr($line, ' ', '-'),
								'active' => false
							);
						}
					}
				}
				$bar['TOURNAMENTS'] = $new_bar;
			}
		}
		return true;
	}
}