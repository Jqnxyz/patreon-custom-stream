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
		<div class="container" style="padding-top: 5vh">
			<h2>Zlux.us Private Patreon Streams</h2>
			<?php
				require_once __DIR__.'/vendor/autoload.php';
				 
				use Patreon\API;
				use Patreon\OAuth;
				session_start();
				$loggedIn = false;
				$isCreator = false;


				//Load patreon settings from JSON
				$patreonSettings = json_decode(file_get_contents("settings.json"), true);
				$client_id = $patreonSettings['oauth']['clientID'] ?? '';
				$baseServeURL = $patreonSettings['baseURL'] ?? '';
				$CreatorUserID = $patreonSettings['creator']['userID'] ?? '';

				$access_token = $_SESSION["access_token"] ?? '';
				$refresh_token = $_SESSION["refresh_token"] ?? '';

				if ($access_token == '' || $refresh_token == '') {
					$loggedIn = false;
				} else {
					$api_client = new API($access_token);
					$patron_response = $api_client->fetch_user();

					$creatorMember = false;
					if ($patron_response['data']['relationships']['memberships']['data'][0]['type'] == "member") {
							$creatorMember = true;
							$loggedIn = true;
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
					<h5>What is this?</h5>
					<p>
						Patreon Stream is a service for Patreon creators to host private streams for their patrons.
					</p>
					<h5>For patrons</h5>
					<p>
						Sign in with your Patreon account to start watching your favourite creators!
					</p>
					<h5>For creators</h5>
					<p>
						Customize your preferred stream setup with support for YouTube and Twitch RTMP streams. Mix and match with the ability to stream through YouTube while utilising Twitch chat's extensive emotes and moderation tools.
					</p>
					<p>
						Google integration in the management portal allows us to automatically detect when a new video/stream is started and serve users the latest content. 
					</p>
				</div>
				<div class="one-half column">
					<h5><?php 
						if ($loggedIn) { 
							echo "You are logged in"; 
						} else { 
							echo "Log in with Patreon"; 
						} 
					?></h5>
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
	<footer>
		<div class="container">
			<center>
				Jerome Quah 2019 &bull; zlux@jqnxyz.xyz
				<p><a href="https://zlux.us/privacy.php">Privacy</a> &bull; <a href="https://zlux.us/terms.php">Terms</a></p>
				<p>More services at <a href="//zlux.us">Zlux.us</a></p>
			</center>
		</div>
	</footer>
</html>