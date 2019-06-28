<?php
	require_once __DIR__.'/vendor/autoload.php';
	use Patreon\API;
	use Patreon\OAuth;
	session_start();

	//Load patreon settings from JSON
	$patreonSettings = json_decode(file_get_contents("settings.json"), true);
	$baseServeURL = $patreonSettings['baseURL'];
	$client_id = $patreonSettings['oauth']['clientID'];
	$client_secret = $patreonSettings['oauth']['clientSecret'];
	$redirect_uri = $patreonSettings['oauth']['redirectURI'];
	$checkCreatorID = $patreonSettings['creator']['ID'];
	$CreatorEmail = $patreonSettings['creator']['email'];

	$patCode = $_GET['code'];
	$patState = $_GET['state'];

	if ( $patCode != '' ) {
		
		$oauth_client = new OAuth($client_id, $client_secret);	
		$tokens = $oauth_client->get_tokens($_GET['code'], $redirect_uri);
		$access_token = $tokens['access_token'];
		$refresh_token = $tokens['refresh_token'];

		$api_client = new API($access_token);
		$patron_response = $api_client->fetch_user();

		$creatorMember = false;
		foreach($patron_response['data']['relationships']['memberships']['data'] as $memberInfo) {
			if ($memberInfo['id'] == $checkCreatorID) {
				$creatorMember = true;
			}
		}
		if ($creatorMember || $patron_response['data']['email'] == $CreatorEmail) {
			$_SESSION["access_token"]=$access_token;
			$_SESSION["refresh_token"]=$refresh_token;
			if ($patState == "manage" && $patron_response['data']['attributes']['email'] == $CreatorEmail) {
				header('Location: '.$baseServeURL.'/creatormanage.php');
			} else {
				header('Location: '.$baseServeURL.'/watch.php');
			}
		} else {
			header('Location: '.$baseServeURL.'/loginfailed.php?reason=Not a patron of this creator');
		}


	} else {
		header('Location: '.$baseServeURL.'/loginfailed.php');
	}


?>