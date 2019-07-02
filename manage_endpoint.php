<?php
	require_once __DIR__.'/vendor/autoload.php';
	 
	use Patreon\API;
	use Patreon\OAuth;
	session_start();

	//Local CLI Access
	$localAction = $argv[1] ?? '';
	$localValue = $argv[2] ?? '';

	$loggedIn = false;
	$isCreator = false;
	$outputData = "";
	$outputSuccess = false;

	//Load patreon settings from JSON
	$streamSettings = json_decode(file_get_contents("stream.json"), true);
	$patreonSettings = json_decode(file_get_contents("settings.json"), true);
	$CreatorUserID = $patreonSettings['creator']['userID'] ?? '';

	$access_token = $_SESSION["access_token"] ?? '';
	$refresh_token = $_SESSION["refresh_token"] ?? '';

	$postAction = $_POST['manageAction'] ?? '';
	$postValue = $_POST['manageValue'] ?? '';
	//replace post vars with local params
	if ($localAction != '') {
		$postAction = $localAction;
		$postValue = $localValue;
		//fwrite(STDOUT, "Local Action and Value: ".$localAction.",".$localValue);
	}

    $g_redirect_uri = $patreonSettings['google-oauth']['redirectURI'] ?? '';
    $g_API = $patreonSettings['google-oauth']['API'] ?? '';

    $g_accessToken = $patreonSettings['creator']['gAccess']['access_token'] ?? '';
    $g_refreshToken = $patreonSettings['creator']['gAccess']['refresh_token'] ?? '';
    $g_tokenExpires = $patreonSettings['creator']['gAccess']['expires_in'] ?? '';
    $g_tokenCreated = $patreonSettings['creator']['gAccess']['created'] ?? '';

    $ytStreamKeyword = $patreonSettings['creator']['ytKeyword'];
    $ytStreamSearchKey = $patreonSettings['creator']['ytSearch'];

    //End load

	$client = new Google_Client();
	$client->setAuthConfig('client_secret.json');
	$client->setRedirectUri($g_redirect_uri);
	$client->setAccessType('offline');
	$client->setIncludeGrantedScopes(true);
	$client->setAccessToken($g_accessToken);


	if ($access_token == '') {
		$loggedIn = false;
	} else {
		$loggedIn = true;
		$api_client = new API($access_token);
		$patron_response = $api_client->fetch_user();

		if ($patron_response['data']['id'] == $CreatorUserID) {
			$isCreator = true;
		}
	}

	function updateYTstreamID($ytID) {
		$streamSettings = json_decode(file_get_contents("stream.json"), true);
		$streamSettings['YTID'] = $ytID;
		$newstreamSettings = json_encode($streamSettings, JSON_PRETTY_PRINT);
		file_put_contents('stream.json', $newstreamSettings);
	}

	function refreshSettings() {
		$patreonSettings = json_decode(file_get_contents("settings.json"), true);
		$CreatorUserID = $patreonSettings['creator']['userID'] ?? '';

	    $g_redirect_uri = $patreonSettings['google-oauth']['redirectURI'] ?? '';
	    $g_API = $patreonSettings['google-oauth']['API'] ?? '';

	    $g_accessToken = $patreonSettings['creator']['gAccess']['access_token'] ?? '';
	    $g_refreshToken = $patreonSettings['creator']['gAccess']['refresh_token'] ?? '';
	    $g_tokenExpires = $patreonSettings['creator']['gAccess']['expires_in'] ?? '';
	    $g_tokenCreated = $patreonSettings['creator']['gAccess']['created'] ?? '';

	    $ytStreamKeyword = $patreonSettings['creator']['ytKeyword'];
	}

	function validateAccessToken() {
		global $client;
		global $g_tokenCreated;
		global $g_tokenExpires;
		global $g_refreshToken;

		if ($g_tokenCreated+$g_tokenExpires-300 <= time()) {
			$new_access_token = $client->refreshToken($g_refreshToken);
			$cleanNew = json_encode($new_access_token, JSON_PRETTY_PRINT);
			//fwrite(STDOUT, "New Access: ".$cleanNew);
			$client->setAccessToken($new_access_token);
			$changeSettings = json_decode(file_get_contents("settings.json"), true);
		    $changeSettings['creator']['gAccess']['access_token'] = $new_access_token['access_token'];
		    $changeSettings['creator']['gAccess']['refresh_token'] = $new_access_token['refresh_token'];
		    $changeSettings['creator']['gAccess']['expires_in'] = $new_access_token['expires_in'];
		    $changeSettings['creator']['gAccess']['created'] = $new_access_token['created'];
			$newchangeSettings = json_encode($changeSettings, JSON_PRETTY_PRINT);
			file_put_contents('settings.json', $newchangeSettings);
			refreshSettings();
		} else {
			//fwrite(STDOUT, "Old token still valid.");
		}
	}

	if ($loggedIn || $localAction != '') {
		if ($isCreator || $localAction != '') {
			switch ($postAction) {
				case 'changeYTID':
					updateYTstreamID($postValue);
					$outputData = "YTID updated";
					$outputSuccess = true;
				break;
				case 'changeTwitchChat':
					$streamSettings['twitchChat'] = $postValue;
					$newstreamSettings = json_encode($streamSettings, JSON_PRETTY_PRINT);
					file_put_contents('stream.json', $newstreamSettings);
					$outputData = "twitchChat updated";
					$outputSuccess = true;
				break;
				case 'revokeGoogle':
					validateAccessToken();
					$client->revokeToken();

					$changeSettings = json_decode(file_get_contents("settings.json"), true);
					$changeSettings['creator']['gAccess'] = "";
					$newchangeSettings = json_encode($changeSettings, JSON_PRETTY_PRINT);
					file_put_contents('settings.json', $newchangeSettings);

					$outputData = "Access Revoked";
					$outputSuccess = true;
				break;
				case 'grabYTID':
					validateAccessToken();

					//Grab latest stream ID
					$ytAPIService = new Google_Service_YouTube($client);
					$ytQueryParams = [
					    'mine' => true
					];
					$ytChannel = $ytAPIService->channels->listChannels('snippet,contentDetails,statistics', $ytQueryParams);
					//error_log(json_encode($ytChannel, JSON_PRETTY_PRINT));

					if (sizeof($ytChannel['items']) == 0) {
						error_log("No YouTube channel associated!");
						$outputData = "No YouTube channel associated";
						$outputSuccess = false;
					} else {
						$ytUploadsPlaylistID = $ytChannel['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
						$ytQueryParams = [
						    'maxResults' => 15,
						    'playlistId' => $ytUploadsPlaylistID
						];
						$ytList = $ytAPIService->playlistItems->listPlaylistItems('snippet,contentDetails', $ytQueryParams);
						$ytLatestStreamID = "";
						//error_log(json_encode($ytList, JSON_PRETTY_PRINT));

						if (sizeof($ytList['items']) == 0){
							$outputData = "No videos uploaded";
							$outputSuccess = false;
						} else {
							for ($i = 0; $i < sizeof($ytList['items']); $i++){
								if (strpos($ytList['items'][$i]['snippet'][$ytStreamSearchKey], $ytStreamKeyword) !== false){
									$ytLatestStreamID = $ytList['items'][$i]['contentDetails']['videoId'];
									updateYTstreamID($ytLatestStreamID);
									//fwrite(STDOUT, "Found ID: ".$ytLatestStreamID);
									break;
								}	
							}
						}
						//Exiting
						if ($ytLatestStreamID != "") {
							$outputData = $ytLatestStreamID;
							$outputSuccess = true;
						} else {
							$outputData = "Could not find latest stream";
							$outputSuccess = false;
						}
					}
					break;
				default:
					$outputData = "Action unspecified or does not exist";
					$outputSuccess = false;
				break;
			}
		} else {
			$outputData = "Authentication Failed";
			$outputSuccess = false;
		}
	} else {
		$outputData = "Not logged in";
		$outputSuccess = false;
	}

	//Return
	if ($localAction != ''){
		if ($outputSuccess) {
			//fwrite(STDOUT, $outputData);
		} else {
			error_log("General Error: ".$outputData);
		}
	} else {
		header('Content-type: application/json');
		$outputArray = ["success" => $outputSuccess, "message" => $outputData];
		echo json_encode($outputArray);
	}
?>