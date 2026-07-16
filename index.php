<?php
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}

	// Without a Host header we cannot build an absolute redirect URL.
	// Fail loudly instead of emitting a broken "Location:" header.
	if (empty($_SERVER['HTTP_HOST'])) {
		error_log('index.php: cannot redirect, missing HTTP_HOST header');
		http_response_code(400);
		header('Content-Type: text/plain; charset=utf-8');
		echo 'Unable to redirect: the request did not include a Host header.';
		exit(1);
	}

	// header() silently does nothing once output has started, which would
	// leave the client with no redirect and no explanation.
	if (headers_sent($file, $line)) {
		error_log(sprintf('index.php: headers already sent in %s on line %d; cannot redirect', $file, $line));
		http_response_code(500);
		echo 'Unable to redirect: response headers were already sent.';
		exit(1);
	}

	$uri .= $_SERVER['HTTP_HOST'];
	header('Location: '.$uri.'/dashboard/');
	exit;
?>
Something is wrong with the XAMPP installation :-(
