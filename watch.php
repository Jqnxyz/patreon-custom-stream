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
            $baseServeURL = $patreonSettings['baseURL'];
            $checkCreatorID = $patreonSettings['creator']['ID'];
            $streamSettings = json_decode(file_get_contents("stream.json"), true);
            $ytID = $streamSettings['YTID'];
            $twitchChat = $streamSettings['twitchChat'];

            $access_token = $_SESSION["access_token"];
            $refresh_token = $_SESSION["refresh_token"];
            $api_client = new API($access_token);
            $patron_response = $api_client->fetch_user();

            $creatorMember = false;
            foreach($patron_response['data']['relationships']['memberships']['data'] as $memberInfo) {
                if ($memberInfo['id'] == $checkCreatorID) {
                    $creatorMember = true;
                }
            }
            if (!$creatorMember) {
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
            text-align: center;
            padding: 0;
            margin-bottom: -4;
            margin-top: -4;
            float: left;
        }
        .stream {
            display: inline-block;
            padding: 0;
            margin-bottom: -4;
            margin-top: -4;
        }
        .chat-left {
            padding: 0;
            margin: 0;
            float: left;
        }
        .chat-right {
            padding: 0;
            margin: 0;
            float: top;
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
            </div>
            <iframe
                frameborder='0'
                scrolling='true'
                id='chat-first'
                class='chat-right'
                src='https://www.twitch.tv/embed/<?=$twitchChat;?>/chat?darkpopout'
                height='1'
                width='1'
                style='width: 20%;'>
            </iframe>
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
                height: window.innerHeight,
                width: window.innerWidth * 4 / 5 - 15,
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

        const firstChat = document.getElementById('chat-first')

        firstChat.src = `https://www.twitch.tv/embed/<?=$twitchChat;?>/chat?darkpopout`

        firstChat.height = window.innerHeight - 10
        firstChat.width = window.innerWidth * 1 / 5
    </script>
</html>