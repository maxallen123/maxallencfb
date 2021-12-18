<?php

	// Return spread with either decimal places if it's a number or return whatever it was instead
	function formatSpread($spread) {
		if(is_numeric($spread)) {
			return number_format($spread, 1);
		} else {
			return $spread;
		}
	}

?>