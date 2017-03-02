<?php

	function getVariables( $var, &$html, $json = FALSE ){
		#preg_match_all('/var\s+(wmVariables)\s*=\s*(["\']?)(.*?)\2;/i', $html, $matches);
		#preg_match('/var\s+('.$var.')\s*=\s*(["\']?)(.*)?\;$/i', $html, $matches);
		preg_match('/var\s+('.$var.')\s*=\s*(["\']?)(.*)?\;$/mi', $html, $matches);
		if( isset( $matches[3] ) ){
			if( $json ){
				$result = json_decode( $matches[3] );
				if (json_last_error() === JSON_ERROR_NONE)
						return $result;
				} else {
					return $matches[3];
				}
		}

		return FALSE;
	}

?>
