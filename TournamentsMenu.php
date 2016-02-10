<?php

$wgExtensionCredits['parserhook'][] = array(
								'name' => 'TournamentsMenu',
								'author' =>'Philip Becker-Ehmck',
								'url' => '',
								'description' => 'Allows everyone to modify the tournaments menu point.',
								'descriptionmsg' => "tournamentsmenu-desc",
								'version' => '1.0',
								'path' => __FILE__,
);

$wgHooks['SkinBuildSidebar'][] = 'fnTournamentsMenu';

function fnTournamentsMenu( $skin, &$bar ) {
        if(isset($bar['TOURNAMENTS'])) {
                $message = 'Tournaments';

                if ( Title::newFromText( $message, NS_PROJECT )->exists() ) {
                        $titleFromText = Title::newFromText( $message, NS_PROJECT );
                        $article = new Article($titleFromText);
                        $text = $article->getContent();
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
                                                                                                                'active' => false
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
                                                                                                                'active' => false
                                                );
                                        }
                                }
                        }
                }
                $bar['TOURNAMENTS'] = $new_bar;
        }
	return true;
}


?>