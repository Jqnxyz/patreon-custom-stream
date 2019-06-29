<!DOCTYPE html>
<html lang="en">
<html>
    <head>
        <?php
            require_once __DIR__.'/vendor/autoload.php';
             
            use Patreon\API;
            use Patreon\OAuth;
            session_start();
            
            //Load patreon settings from JSON
            $patreonSettings = json_decode(file_get_contents("settings.json"), true);
            $baseServeURL = $patreonSettings['baseURL'];
            $checkCreatorID = $patreonSettings['creator']['ID'];
            $CreatorUserID = $patreonSettings['creator']['userID'];

            $access_token = $_SESSION["access_token"];
            $refresh_token = $_SESSION["refresh_token"];
            $api_client = new API($access_token);
            $patron_response = $api_client->fetch_user();

            if ($patron_response['data']['id'] != $CreatorUserID) {
                header('Location: '.$baseServeURL.'/loginfailed.php?reason=Authentication failed');
            }

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
        <!--JQuery-->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="container" style="padding-top: 35vh">
            <h2>Patreon Stream Management</h2>
            <p>Logged in as <b><?= $patron_response['data']['attributes']['email'] ?></b></p>
            <div class="row">
                <div class="one-half column">
                    <label for="streamIDupdate">YouTube Stream ID</label>
                    <input class="u-full-width" type="text" placeholder="dQw4w9WgXcQ" id="streamIDupdate">
                    <a class="button button-primary" id="YTIDbtn" href="#!">Update Stream ID</a>

                    <label for="twitchChatupdate">Twitch Chat Channel</label>
                    <input class="u-full-width" type="text" placeholder="FriendlyBaron" id="twitchChatupdate">
                    <a class="button button-primary" id="twitchChatbtn" href="#!">Update Twitch Chat Channel</a>

                    <script>
                        $("#YTIDbtn").click(function(){
                            $.ajax({
                                method: "POST",
                                xhrFields: {withCredentials:true},
                                url: "<?=$baseServeURL;?>/manage_endpoint.php",
                                data: { 
                                    "manageAction": "changeYTID", 
                                    "manageValue": $("#streamIDupdate").val()
                                }
                            })
                            .done(function( returnData ) {
                                var data = returnData;
                                if (data.success) {
                                    console.log("Success: "+data.message);
                                    $("#outputBox").text("Sucessfully changed YouTube Stream ID");
                                } else {
                                    console.log("Failed: "+data.message);
                                    $("#outputBox").text("Failed to changed YouTube Stream ID");
                                }
                            });
                        });

                        $("#twitchChatbtn").click(function(){
                            $.ajax({
                                method: "POST",
                                xhrFields: {withCredentials:true},
                                url: "<?=$baseServeURL;?>/manage_endpoint.php",
                                data: { 
                                    "manageAction": "changeTwitchChat", 
                                    "manageValue": $("#twitchChatupdate").val()
                                }
                            })
                            .done(function( returnData ) {
                                var data = returnData;
                                if (data.success) {
                                    console.log("Success: "+data.message);
                                    $("#outputBox").text("Sucessfully changed Twitch Chat Channel");
                                } else {
                                    console.log("Failed: "+data.message);
                                    $("#outputBox").text("Failed to changed Twitch Chat Channel");
                                }
                            });
                        });
                </script>
                </div>
            </div>
            <div class="row">
                <div class="one-half column u-pull-left">
                    <p><b id="outputBox"></b></p>
                </div>
                <div class="one-half column u-pull-right">
                    <a class="button" href="watch.php">Watch Stream</a>
                    <a class="button" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </body>
</html>