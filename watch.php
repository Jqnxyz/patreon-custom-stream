<!DOCTYPE html>
<html>
    <head>
        <?php
            require_once __DIR__.'/vendor/autoload.php';
             
            use Patreon\API;
            use Patreon\OAuth;
            session_start();

            //Load patreon settings from JSON
            $patreonSettings = json_decode(file_get_contents("settings.json"), true);
            $baseServeURL = $patreonSettings['baseURL'] ?? '';
            $streamSettings = json_decode(file_get_contents("stream.json"), true);
            $ytID = $streamSettings['YTID'] ?? '';
            $twitchChat = $streamSettings['twitchChat'] ?? '';
            $CreatorUserID = $patreonSettings['creator']['userID'] ?? '';

            $access_token = $_SESSION["access_token"] ?? '';
            $refresh_token = $_SESSION["refresh_token"] ?? '';
            $api_client = new API($access_token);
            $patron_response = $api_client->fetch_user();

            $creatorMember = false;
            $isCreator = false;
            
            if ($patron_response['data']['relationships']['memberships']['data'][0]['type'] == "member") {
                    $creatorMember = true;
            }

            if ($patron_response['data']['id'] == $CreatorUserID) {
                $isCreator = true;
            }

            if (!$creatorMember && !$isCreator) {
                header('Location: '.$baseServeURL.'/loginfailed.php?reason=Not a patron of this creator');
            }

        ?>
        <meta charset="UTF-8" />
        <title>Patreon Stream</title>
    </head>
    <!-- CUSTOMISE BELOW -->
    <script src="https://player.twitch.tv/js/embed/v1.js"></script>
    <style>
        body {
            background-color: black;
        }
        .stream-wrapper {
            padding: 0;
            margin: 0;
            text-align: center;
            /*padding: 0;
            margin-bottom: -4;
            margin-top: -4;*/
            float: left;
            width:79vw;
            height:100vh;
        }
        .chat-wrapper {
            padding: 0;
            margin: 0;
            float: right;
            width:19vw;
            height:100vh;
        }
        .stream {
            padding: 0;
            margin: 0;
            display: inline-block;
            padding: 0;
            margin-bottom: -4;
            margin-top: -4;
        }
        .chat-right {
            padding: 0;
            margin: 0;
            float: top;
            width: 100%;
            height: 100%;
        }
        .dark-matter {
            font: 12px "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #D3D3D3;
            border: none;
        }
        .dark-matter label>span {
            font-weight: bold;
        }
        .dark-matter input[type="text"], .dark-matter input[type="text"], .dark-matter textarea, .dark-matter select {
            border: none;
            color: white;
            outline: 0 none;
            -webkit-border-radius: 2px;
            -moz-border-radius: 2px;
            -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            background: #19171C;
        }
        .dark-matter select {
            background: #19171C url('down-arrow.png') no-repeat right;
            background: #19171C url('down-arrow.png') no-repeat right;
            appearance:none;
            -webkit-appearance:none; 
            -moz-appearance: none;
            text-indent: 0.01px;
            text-overflow: '';
            color: #525252;
        }
        .dark-matter textarea {
        }
        .dark-matter .button {
            background: #4B367C;
            border: none;
            color: #FFFFFF;
            border-radius: 4px;
            -moz-border-radius: 4px;
            -webkit-border-radius: 4px;
            font-weight: bold;
            box-shadow: 1px 1px 1px #3D3D3D;
            -webkit-box-shadow:1px 1px 1px #3D3D3D;
            -moz-box-shadow:1px 1px 1px #3D3D3D;
        }
        .dark-matter .button:hover {
            color: #333;
            background-color: #EBEBEB;
        }
        /* unvisited link */
        a:link {
            color: #FFFFFF;
        }
        /* visited link */
        a:visited {
            color: #4B367C;
        }
        /* mouse over link */
        a:hover {
            color: #4B367C;
        }
        /* selected link */
        a:active {
            color: white;
        }
    </style>
    <body>
        <div style='width: 100%;'>
            <div>
                <div class='stream-wrapper'>
                    <div id="player" class='stream'></div>
                </div>

                <div class='chat-wrapper'>
                    <iframe frameborder='0' scrolling='true' id='chat-first' class='chat-right' src='https://www.twitch.tv/embed/<?=$twitchChat;?>/chat?darkpopout'></iframe>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript">
        document.documentElement.style.overflow = 'hidden' // firefox, chrome
        document.body.scroll = 'no' // ie only
        
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        var player;

        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: "100%",
                width: "100%",
                playerVars: { 
                    'modestbranding':1, 
                    'showinfo':0, 
                    'autoplay': 1,
                    'autohide': 1,
                    'rel': 0
                },
                videoId: '<?=$ytID;?>',
                events: {
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }

        function onPlayerReady(event) {
            event.target.playVideo();
        }

        function onPlayerStateChange(event) {}

        function stopVideo() {
            player.stopVideo();
        }
    </script>
</html>