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
						$line = array_map('trim', explode( '|' , trim($line, '* ') ) );

						foreach( $line as $key => $value ) {
							if( strpos( $value, 'startdate' ) !== false ) {
								$startDate = trim( explode( '=', $value )[1]);
								unset($line[$key]);
							} else if( strpos( $value, 'enddate' ) !== false ) {
								$endDate = trim( explode( '=', $value )[1]);
								unset($line[$key]);
							}
						}
						$line = array_values( $line );
						if( count( $line ) == 1 ) {
							$line[1] = $line[0];
						}

						$link = wfMessage( $line[0] )->inContentLanguage()->text();
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

						$item = array(
							'text' => $text,
							'href' => $href,
							'id' => 'n-' . strtr($line[1], ' ', '-') . '-mainpage',
							'active' => false,
							'exists' => $title->exists()
						);

						if( isset( $startDate ) ) {
							$item['startdate'] = $startDate;
						}
						if( isset( $endDate ) ) {
							$item['enddate'] = $endDate;
						}

						$new_bar[$heading][] = $item;
						unset($startDate, $endDate);
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
					$return .= '<a ' . ((!$tournament_arr['exists'])?'class="new" ':'') . 'href="' . $tournament_arr['href'] . '">';
					$return .= $tournament_arr['text'];
					if( isset( $tournament_arr['startdate'] ) && isset( $tournament_arr['enddate'] ) ) {
						$return .= '<span class="tournaments-list-dates">';
						$return .= '<sup>' . $tournament_arr['startdate'] . '</sup>';
						$return .= '<sub>' . $tournament_arr['enddate'] . '</sub>';
						$return .= '</span>';
					} else if( isset( $tournament_arr['startdate'] ) ){
						$return .= '<small class="tournaments-list-dates">';
						$return .= $tournament_arr['startdate'];
						$return .= '</small>';
					} else if( isset( $tournament_arr['enddate'] ) ){
						$return .= '<small class="tournaments-list-dates">';
						$return .= $tournament_arr['enddate'];
						$return .= '</small>';
					}
					$return .= '</a>';
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