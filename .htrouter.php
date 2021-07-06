<?php

if ( ! file_exists(__DIR__ . '/' . $_SERVER['REQUEST_URI'])) {

	$explode = explode('?', $_SERVER['REQUEST_URI']);

	$url = $explode[0];
	$other = false;

	if (isset($explode[1])) {
		$other = $explode[1];
	}

    $_GET['_url'] = $url;

    if ($other) {

        $params = explode('&', $other);

        foreach ($params as $param) {
            list($key, $value) = explode('=', $param);
            $_GET[$key] = $value;
        }
    }
}

return false;