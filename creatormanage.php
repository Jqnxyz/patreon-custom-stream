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

            $g_Access = $patreonSettings['creator']['gAccess'];
            $g_clientID = $patreonSettings['google-oauth']['clientID'];
            $g_redirect_uri = $patreonSettings['google-oauth']['redirectURI'];
            $g_API = $patreonSettings['google-oauth']['API'];

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
        <!--Google Identity-->
        <script src="https://apis.google.com/js/api.js"></script>
        <script src="https://apis.google.com/js/platform.js" async defer></script>
    </head>
    <body>
        <div class="container" style="padding-top: 5vh">
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
                <div class="one-half column u-pull-right">
                    <div>
                        <h6>Google Auth</h6>
                        <button class="button-primary" id="gSignInBtn">Authorise</button>
                        <button class="button" id="gRevokeBtn" style="display: none;">Revoke access</button>
                        <p id="content"></p>
                        <script type="text/javascript">
                            var gAuthorised = <?php if ($g_Access != '') { echo 'true'; } else { echo 'false'; }; ?>;

                            if (gAuthorised) {
                                $("#gSignInBtn").hide();
                                $("#gRevokeBtn").show();
                            } else {
                                $("#gSignInBtn").show();
                                $("#gRevokeBtn").hide();
                            }

                            $("#gSignInBtn").click(function(){
                                oauthSignIn();
                            });

                            $("#gRevokeBtn").click(function(){

                            });

                            var GoogleAuth;
                            var clientId = '<?=$g_clientID;?>';
                            var scope = 'https://www.googleapis.com/auth/youtube.readonly';

                            function oauthSignIn() {
                                // Google's OAuth 2.0 endpoint for requesting an access token
                                var oauth2Endpoint = 'https://accounts.google.com/o/oauth2/v2/auth';

                                // Create <form> element to submit parameters to OAuth 2.0 endpoint.
                                var form = document.createElement('form');
                                form.setAttribute('method', 'GET'); // Send as a GET request.
                                form.setAttribute('action', oauth2Endpoint);

                                // Parameters to pass to OAuth 2.0 endpoint.
                                var params = {'client_id': '<?=$g_clientID;?>',
                                    'access_type': 'offline',
                                    'redirect_uri': '<?=$g_redirect_uri;?>',
                                    'response_type': 'code',
                                    'scope': scope,
                                    'include_granted_scopes': 'true',
                                    'state': 'gsignin'};

                                // Add form parameters as hidden input values.
                                for (var p in params) {
                                    var input = document.createElement('input');
                                    input.setAttribute('type', 'hidden');
                                    input.setAttribute('name', p);
                                    input.setAttribute('value', params[p]);
                                    form.appendChild(input);
                                }

                                // Add form to page and submit it to open the OAuth 2.0 endpoint.
                                document.body.appendChild(form);
                                form.submit();
                            }
                        </script>
                        <script async defer src="https://apis.google.com/js/api.js" onload="this.onload=function(){};handleClientLoad()" onreadystatechange="if (this.readyState === 'complete') this.onload()">
                        </script>
                    </div>
                    <hr/>
                    <a class="button" href="watch.php">Watch Stream</a>
                    <a class="button" href="logout.php">Logout</a>
                </div>
            </div>
            <div class="row">
                <div class="one-half column u-pull-left">
                    <p><b id="outputBox"></b></p>
                </div>
            </div>
        </div>
    </body>
</html>