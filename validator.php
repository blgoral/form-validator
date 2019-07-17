<?php
//Paste the Adder action and secrey key from Google here
$adderURL = 'https://tnt-adder.herokuapp.com/submit/';
$secretKey = '';

// Take the user agent and referrer from the previous page
//This will be used to fool Adder into thinking the post is coming straight from the form
$referrer = $_SERVER['HTTP_REFERER'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$submission = http_build_query($_POST);

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
	if ($captcha_success->success==false) {
		echo "<p>CAPTCHA failed!</p>";
	} else if ($captcha_success->success==true) {
//open connection
$ch = curl_init($adderURL);
//construct the submission
//Adder will only accept submissions with certain referrers and user agents so we use the ones we stored
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_REFERER, $referrer);
curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
curl_setopt($ch, CURLOPT_POSTFIELDS, $submission);

// execute!
$response = curl_exec($ch);
// close the connection, release resources used
curl_close($ch);

// do anything you want with your response
echo $response;
}
