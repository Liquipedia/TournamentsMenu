<?php

namespace Liquipedia\Extension\TournamentsMenu;

class Helper {

	public static function unwrapHtml( string $html ): string {
		return substr(
			$html,
			strlen( '<div class="mw-content-ltr mw-parser-output" lang="en" dir="ltr">' ),
			-strlen( '</div>' )
		);
	}

}
