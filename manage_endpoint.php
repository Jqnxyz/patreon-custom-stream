<?php
	require_once __DIR__.'/vendor/autoload.php';
	 
	use Patreon\API;
	use Patreon\OAuth;
	session_start();
	$loggedIn = false;
	$isCreator = false;
	$outputData = "";
	$outputSuccess = false;

	$streamSettings = json_decode(file_get_contents("stream.json"), true);
	//Load patreon settings from JSON
	$patreonSettings = json_decode(file_get_contents("settings.json"), true);
	$checkCreatorID = $patreonSettings['creator']['ID'] ?? '';
	$CreatorUserID = $patreonSettings['creator']['userID'] ?? '';

	$access_token = $_SESSION["access_token"] ?? '';
	$refresh_token = $_SESSION["refresh_token"] ?? '';
	$postAction = $_POST['manageAction'] ?? '';
	$postValue = $_POST['manageValue'] ?? '';

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

	if ($loggedIn) {
		if (!$isCreator) {
			$outputData = "Authentication Failed";
			$outputSuccess = false;
		} else {
			switch ($postAction) {
				case changeYTID:
					$streamSettings['YTID'] = $postValue;
					$newstreamSettings = json_encode($streamSettings, JSON_PRETTY_PRINT);
					file_put_contents('stream.json', $newstreamSettings);
					$outputData = "YTID updated";
					$outputSuccess = true;
				break;
				case changeTwitchChat:
					$streamSettings['twitchChat'] = $postValue;
					$newstreamSettings = json_encode($streamSettings, JSON_PRETTY_PRINT);
					file_put_contents('stream.json', $newstreamSettings);
					$outputData = "twitchChat updated";
					$outputSuccess = true;
				break;
				case revokeGoogle:
				break;
				default:
					$outputData = "Action unspecified or does not exist";
					$outputSuccess = false;
				break;
			}
		}
	} else {
		$outputData = "Not logged in";
		$outputSuccess = false;
	}

	//Return
	header('Content-type: application/json');
	$outputArray = ["success" => $outputSuccess, "message" => $outputData];
	echo json_encode($outputArray);
?>