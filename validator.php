<?php

//Paste the Adder action and secret key from Google here
$adderURL = 'https://tnt-adder.herokuapp.com/submit/';
$secretKey = '';

// Take the user agent and referrer from the previous page
$referrer = $_SERVER['HTTP_REFERER'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

//Check if the site is in staging
if (strpos($referrer, 'tntclients.com/cms/published') !== false) {
  $inStaging = true;
} else {
  $inStaging = false;
}

$submission = $_POST;
//Remove the captcha response so it doesn't show in the submission
unset($submission['g-recaptcha-response']);
//Remove numbered indexes so they don't show up when checkboxes are used
$submission = preg_replace('/(%5B)\d+(%5D=)/i', '$1$2', http_build_query($submission));

//verify response with Google
$response = $_POST["g-recaptcha-response"];
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => $secretKey,
		'response' => $_POST["g-recaptcha-response"]
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success=json_decode($verify);
	if ($captcha_success->success==false && $inStaging == false) {
		echo "<p>CAPTCHA failed!</p>";
	} else if ($captcha_success->success==true || $inStaging == true) {
//open connection
$ch = curl_init($adderURL);
//construct the submission
//Adder will only accept submissions with certain referrers and user agents so we use the ones we stored
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_REFERER, $referrer);
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
curl_setopt($ch, CURLOPT_POSTFIELDS, $submission);
// execute!
$adderResponse = curl_exec($ch);
// close the connection, release resources used
curl_close($ch);
// output response
echo $adderResponse;
//check if there's a redirect link in the Adder response
preg_match_all('/<a[^>]+href=([\'"])(?<href>.+?)\1[^>]*>/i', $adderResponse, $result);
//Perform a redirect if one is found
if (!empty($result) && $inStaging == false) {
    $redirect = $result['href'][0];    
    header('Location: '.$redirect);
  }
}
