<!DOCTYPE html>
<html>
    <?php
        if(isset($_GET["list"])){
        $handler = curl_init('https://youtube.googleapis.com/youtube/v3/playlists?part=snippet%2CcontentDetails%2Cplayer&id=' . $_GET["list"] .'&maxResults=25&key=AIzaSyBm10K2I-QgkzCB8zUxnmoQTcu1UaSH9_E');

        curl_setopt($handler, CURLOPT_HTTPHEADER, array('application/json'));
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        
        $ok = json_decode($response)->pageInfo->totalResults != 0;
        $playlist = json_decode($response)->items[0];
    }else{$ok=false;}
    ?>
    <head>
        <meta charset="utf-8"/>
        <style>
            <?php require("style.css"); ?>
        
            #desc{
                background-color: cyan;
                margin: 3em 25vw;
            }
            
            #vidDisplay{
                margin: 0vh 25vw;
            }
        </style>
    </head>
    <body>
        <?php require("header.html"); ?>
        <?php echo $playlist->player->embedHtml ?>
        <div id="desc"><?php echo  $playlist->snippet->description ?></div>
        <script>
            document.querySelector("iframe").id = "vidDisplay"
        </script>
    </body>
</html>