<?php
/*
 * Special Thanks to David Walsh: https://davidwalsh.name/bitly-api-php
 */


/* returns the shortened url */
function iflm_devo_get_bitly_short_url($url,$login,$appkey,$format='txt') {
	$connectURL = 'http://api.bit.ly/v3/shorten?login='.$login.'&apiKey='.$appkey.'&uri='.urlencode($url).'&format='.$format;
	$response = wp_remote_get($connectURL);
	return trim($response["body"]);
}

/* returns expanded url */
function iflm_devo_get_bitly_long_url($url,$login,$appkey,$format='txt') {
	$connectURL = 'http://api.bit.ly/v3/expand?login='.$login.'&apiKey='.$appkey.'&shortUrl='.urlencode($url).'&format='.$format;
	$response = wp_remote_get($connectURL);
	return trim($response["body"]);
}
?>