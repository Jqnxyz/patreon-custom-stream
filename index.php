<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<meta charset="utf-8">
		<title>Patreon Stream</title>
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="assets/css/normalize.css">
		<link rel="stylesheet" href="assets/css/skeleton.css">
		<!--link rel="icon" type="image/png" href="assets/images/favicon.png"-->
	</head>
	<body>
		<div class="container" style="padding-top: 35vh">
			<h2>Patreon Stream</h2>
			<?php
				require_once __DIR__.'/vendor/autoload.php';
				 
				use Patreon\API;
				use Patreon\OAuth;
				session_start();
				$loggedIn = false;
				$isCreator = false;


				//Load patreon settings from JSON
				$patreonSettings = json_decode(file_get_contents("settings.json"), true);
				$client_id = $patreonSettings['oauth']['clientID'];
				$baseServeURL = $patreonSettings['baseURL'];
				$checkCreatorID = $patreonSettings['creator']['ID'];
				$CreatorUserID = $patreonSettings['creator']['userID'];

				$access_token = $_SESSION["access_token"];
				$refresh_token = $_SESSION["refresh_token"];

				if ($access_token == '' || $refresh_token == '') {
					$loggedIn = false;
				} else {
					$api_client = new API($access_token);
					$patron_response = $api_client->fetch_user();

					$creatorMember = false;
					foreach($patron_response['data']['relationships']['memberships']['data'] as $memberInfo) {
					    if ($memberInfo['id'] == $checkCreatorID) {
					        $creatorMember = true;
							$loggedIn = true;
					    }
					}
					if ($patron_response['data']['id'] == $CreatorUserID) {
						$isCreator = true;
					}
					if (!$creatorMember) {
					    header('Location: '.$baseServeURL.'/logout.php');
					} 
				}
			?>
			<div class="row">
				<div class="one-half column">
					<h5><?php 
						if ($loggedIn) { 
							echo "You are logged in"; 
						} else { 
							echo "Log in with Patreon"; 
						} 
					?></h5>
				</div>
				<div class="one-half column">
					<?php 
						if (!$loggedIn) { 
							echo '
							<a class="button button-primary" href="//www.patreon.com/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$baseServeURL.'/redirect.php">Login</a>
							<br/>
							<p><a href="//www.patreon.com/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$baseServeURL.'/redirect.php&state=manage">Manage stream (Creator)</a></p>';
						} else { 
							echo '
		                    <a class="button button-primary" href="watch.php">Watch Stream</a>
		                    <a class="button button-primary" href="logout.php">Logout</a>';
						}
						if ($isCreator) {
							echo '<br/>';
							echo '
		                    <a class="button" href="creatormanage.php">Manage Stream</a>';
						}
					?>
					
				</div>
			</div>
		</div>
	</body>
</html>