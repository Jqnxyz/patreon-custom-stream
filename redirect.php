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
	$CreatorUserID = $patreonSettings['creator']['userID'];

    $g_redirect_uri = $patreonSettings['google-oauth']['redirectURI'];
    $g_API = $patreonSettings['google-oauth']['API'];

	$client = new Google_Client();
	$client->setAuthConfig('client_secret.json');
	$client->setRedirectUri($g_redirect_uri);
	$client->setAccessType('offline');        // offline access
	$client->setIncludeGrantedScopes(true);   // incremental auth

	$outputData = "";
	$outputSuccess = false;

	$patCode = $_GET['code'];
	$patState = $_GET['state'];

	if ($patState == "gsignin") {
		if ($_GET['code'] != '') {
			$client->authenticate($_GET['code']);
			$access_token = $client->getAccessToken();
			if ($access_token != '') {
				$changeSettings = json_decode(file_get_contents("settings.json"), true);
				$changeSettings['creator']['gAccess'] = $access_token;
				$newchangeSettings = json_encode($changeSettings, JSON_PRETTY_PRINT);
				file_put_contents('settings.json', $newchangeSettings);
				//Back to management
				header('Location: '.$baseServeURL.'/creatormanage.php');
				$outputData = "Access granted";
				$outputSuccess = true;
			} else {
				$outputData = "Failed obtaining access token";
				$outputSuccess = false;
			}
		} else {
			$outputData = "Google sign in failed";
			$outputSuccess = false;
		}
	}

	if ( $patCode != '' && $patState != "gsignin" ) {
		
		$oauth_client = new OAuth($client_id, $client_secret);	
		$tokens = $oauth_client->get_tokens($_GET['code'], $redirect_uri);
		$access_token = $tokens['access_token'];
		$refresh_token = $tokens['refresh_token'];

		$api_client = new API($access_token);
		$patron_response = $api_client->fetch_user();

		$creatorMember = false;
		if ($patron_response['data']['relationships']['memberships']['data'][0]['type'] == "member") {
				$creatorMember = true;
		}
		/*
		foreach($patron_response['data']['relationships']['memberships']['data'] as $memberInfo) {
			if ($memberInfo['id'] == $checkCreatorID) {
				$creatorMember = true;
			}
		}
		*/
		error_log("Email: ".$patron_response['data']['attributes']['email']);
		error_log("Is Patron: ".$patron_response['included'][0]['attributes']['patron_status']);
		error_log("data: ".$patron_response['data']['relationships']['memberships']['data'][0]['type']);
		error_log("Response: ".json_encode($patron_response));


		if ($creatorMember || $patron_response['data']['id'] == $CreatorUserID) {
			$_SESSION["access_token"]=$access_token;
			$_SESSION["refresh_token"]=$refresh_token;
			if ($patState == "manage" && $patron_response['data']['id'] == $CreatorUserID) {
				header('Location: '.$baseServeURL.'/creatormanage.php');
			} else {
				header('Location: '.$baseServeURL.'/watch.php');
			}
		} else {
			header('Location: '.$baseServeURL.'/loginfailed.php?reason=Not a patron of this creator');
		}


	} else if ( $patCode == '' && $patState != "gsignin" ) {
		header('Location: '.$baseServeURL.'/loginfailed.php');
	}

	if ($outputSuccess == false) {
		error_log("Redirect Error: ".$outputData);
	}


?>