<?php

class TournamentsMenuParserFunction {
	public static function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'tournaments', 'TournamentsMenuParserFunction::getTournamentsList' );
	}
	
	public static function getTournamentsList( $parser ) {
		global $wgOut;
		$message = 'Tournaments';
		$iconTemplatePrefix = 'LeagueIconSmall';
		$return = '';

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
								$startDate = trim( explode( '=', $value )[1] );
								unset($line[$key]);
							} else if( strpos( $value, 'enddate' ) !== false ) {
								$endDate = trim( explode( '=', $value )[1] );
								unset($line[$key]);
							} else if( strpos( $value, 'icon' ) !== false ) {
								$icon = trim( explode( '=', $value )[1] );
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

						if( $title == null ) {
							$exists = false;
						} else {
							$exists = $title->exists();
						}
						
						$item = array(
							'text' => $text,
							'href' => $href,
							'id' => 'n-' . strtr($line[1], ' ', '-') . '-mainpage',
							'active' => false,
							'exists' => $exists
						);

						if( isset( $startDate ) && !empty( trim( $startDate ) ) ) {
							$item['startdate'] = $startDate;
						}
						if( isset( $endDate ) && !empty( trim( $endDate ) ) ) {
							$item['enddate'] = $endDate;
						}
						if( isset( $icon ) && !empty( trim( $icon ) ) ) {
							$item['icon'] = $icon;
						}

						$new_bar[$heading][] = $item;
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
							'active' => false,
							'exists' => $title->exists()
						);
					}
				}
			}
			
			$return .= '<ul class="tournaments-list">';
			foreach($new_bar as $type_name => $type_list) {
				$return .= '<li>';
				$return .= '<span class="tournaments-list-heading">' . $type_name . '</span>';
				$return .= '<ul class="tournaments-list-type-list">';
				foreach($type_list as $tournament_arr) {
					$return .= '<li>';
					$return .= '<a ' . ((!$tournament_arr['exists'])?'class="new" ':'') . 'href="' . $tournament_arr['href'] . '">';
					$return .= '<span class="tournaments-list-name">';
					$iconTitle = Title::newFromText( $iconTemplatePrefix . '/' . $tournament_arr['icon'], NS_TEMPLATE );
					if( isset( $tournament_arr['icon'] ) && ( $iconTitle != null ) && ( $iconTitle->exists() ) && ( $wgOut->getTitle() != null ) ) {
						$return .= str_replace( '<p>', '', str_replace( '</p>', '', $wgOut->parse( '{{' . $iconTemplatePrefix . '/' . $tournament_arr['icon'] . '|link=}}' ) ) );
					}
					$return .= $tournament_arr['text'] . '</span>';
					$return .= '<small class="tournaments-list-dates">';
					if( isset( $tournament_arr['startdate'] ) && isset( $tournament_arr['enddate'] ) ) {
						$return .= $tournament_arr['startdate'];
						$return .= ' &ndash; ';
						if( substr( $tournament_arr['startdate'], 0, 3 ) == substr( $tournament_arr['enddate'], 0, 3 ) ) {
							$tournament_arr['enddate'] = substr( $tournament_arr['enddate'], 4 );
						}
						$return .= $tournament_arr['enddate'];
					} else if( isset( $tournament_arr['startdate'] ) ){
						$return .= $tournament_arr['startdate'];
					} else if( isset( $tournament_arr['enddate'] ) ){
						$return .= $tournament_arr['enddate'];
					}
					$return .= '</small>';
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