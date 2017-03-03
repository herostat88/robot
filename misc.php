<?php

	# Format Excel compatible string
	function cleanData( &$str ){
		$str = preg_replace("/\t/", "\\t", $str);
		$str = preg_replace("/\r?\n/", "\\n", $str);
	}

?>
