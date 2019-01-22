<?php
/*
 * Special Thanks to David Walsh: https://davidwalsh.name/bitly-api-php
 */


/* returns the shortened url */
function get_bitly_short_url($url,$login,$appkey,$format='txt') {
	$connectURL = 'http://api.bit.ly/v3/shorten?login='.$login.'&apiKey='.$appkey.'&uri='.urlencode($url).'&format='.$format;
	return curl_get_result($connectURL);
}

/* returns expanded url */
function get_bitly_long_url($url,$login,$appkey,$format='txt') {
	$connectURL = 'http://api.bit.ly/v3/expand?login='.$login.'&apiKey='.$appkey.'&shortUrl='.urlencode($url).'&format='.$format;
	return curl_get_result($connectURL);
}

/* returns a result form url */
function curl_get_result($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

/* examples *
// get the short url
$short_url = get_bitly_short_url('https://insightforliving.org.uk/resources/devotional-library/humility/','insight4livingusa','R_8450846a941e4366b2e03ad79816c144');

// get the long url from the short one 
$long_url = get_bitly_long_url($short_url,'insight4livingusa','R_8450846a941e4366b2e03ad79816c144');
    
    //echo "short: $short_url<br>long: $long_url";
 /* */
?>