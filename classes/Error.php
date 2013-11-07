<?php

/**
 * 87CMS : Open Source data collector and website builder.
 * @author 87CMS <devteam@87cms.com>
 * @copyright  2013 87CMS
 * @license  GNU GPL v3
 * @package Classes
 */

class ErrorCore {
	
	public static function show($str){
		echo '<div class="style:absolute; width:80% padding:4%; margin:4%; background:#fff;">';
		var_dump($str);
		echo '</div>';
	}
	
}