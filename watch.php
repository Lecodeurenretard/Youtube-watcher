<!doctype HTML>
<html>
    <?php
    if(isset($_GET["vid"])){
        $handler = curl_init('https://youtube.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails%2Cstatistics%2Cplayer&id=' . $_GET['vid'] .'&key=AIzaSyBm10K2I-QgkzCB8zUxnmoQTcu1UaSH9_E');

        curl_setopt($handler, CURLOPT_HTTPHEADER, array('application/json'));
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        $ok = json_decode($response)->pageInfo->totalResults != 0;
        $video = json_decode($response)->items[0];
    }else{$ok=false;}
    
    ?>
    
    <head>
        <meta charset="utf-8" />
        <title><?php echo('On regarde "'. $video->snippet->title . '" de la chaine "' . $video->snippet->channelTitle .'"') ?></title>
        <link href="img/logo-mini.png" type="img/png" rel="favicon"/>
        <link href="img/logo-mini.png" type="img/png" rel="icon"/>
        <link href="img/logo.png" type="img/png" rel="icon" />
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
        <?php require("header.html") ?>
        
        <?php if($ok):?>
        <h2><?php echo  $video->snippet->title; ?></h2>
        <?php echo $video->player->embedHtml ?>
       
        <div id="desc"><?php echo  $video->snippet->description ?></div>
        
        <script>
            document.querySelector("iframe").id = "vidDisplay"
        </script>
        <?php endif; ?>
    </body>
</html>