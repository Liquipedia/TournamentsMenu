<?php

class TournamentsMenuParserFunction {
	public static function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'tournaments', 'TournamentsMenuParserFunction::getTournamentsList' );
	}
	
	public static function getTournamentsList( $parser ) {
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
						$line = array_map('trim', explode( '|' , trim($line, '* '), 2 ) );
						$link = wfMsgForContent( $line[0] );
						if ($link == '-')
							continue;

						$text = wfMsgExt($line[1], 'parsemag');
						if (wfEmptyMsg($line[1], $text))
							$text = $line[1];
						if (wfEmptyMsg($line[0], $link))
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

						$new_bar[$heading][] = array(
														'text' => $text,
														'href' => $href,
														'id' => 'n-' . strtr($line[1], ' ', '-'),
														'active' => false,
														'exists' => $title->exists()
						);
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
														'active' => false,
														'exists' => $title->exists()
						);
					}
				}
			}
			
			$return = '<ul class="tournaments-list">';
			foreach($new_bar as $type_name => $type_list) {
				$return .= '<li>';
				$return .= '<span class="tournaments-list-heading">' . $type_name . '</span>';
				$return .= '<ul class="tournaments-list-type-list">';
				foreach($type_list as $tournament_arr) {
					$return .= '<li>';
					$return .= '<a ' . ((!$tournament_arr['exists'])?'class="new" ':'') . 'href="' . $tournament_arr['href'] . '">' . $tournament_arr['text'] . '</a>';
					$return .= '</li>';
				}
				$return .= '</ul>';
				$return .= '</li>';
			}
			$return .= '</ul>';
		}
		return array(trim($return), "markerType" => 'nowiki' );
	}
}