<?php 
//Load patreon settings from JSON
$patreonSettings = json_decode(file_get_contents("settings.json"), true);
$baseServeURL = $patreonSettings['baseURL'];
session_start();
unset($_SESSION["access_token"]);
unset($_SESSION["refresh_token"]);
header('Location: '.$baseServeURL);
?>