<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<?php 
			//Load patreon settings from JSON
			$patreonSettings = json_decode(file_get_contents("settings.json"), true);
			$client_id = $patreonSettings['oauth']['clientID'];
			$baseServeURL = $patreonSettings['baseURL'];
		?>
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
			<div class="row">
				<div class="one-half column">
					<h5><b><?php
					if ($_GET['reason'] != '') {
						echo $_GET['reason'];
					} else { 
						echo "Login failed"; 
					}
				?></b>, try again?</h5>
				</div>
				<div class="one-half column">
					<?php
					echo '
					<a class="button button-primary" href="//www.patreon.com/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$baseServeURL.'/redirect.php">Login</a>
					<br/>
					<p><a href="//www.patreon.com/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$baseServeURL.'/redirect.php&state=manage">Manage stream (Creator)</a></p>';
					?>
				</div>
			</div>
		</div>
	</body>
</html>