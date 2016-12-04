<?php

function dd($i, $d = null)
{
	echo '<pre>';
	
	if ($d) {
		var_dump($i);
	} else {
		print_r($i);
	}

	die('</pre>');
}

require __DIR__ . '/../bootstrap.php';