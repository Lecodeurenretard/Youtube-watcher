<textarea style="width: 1220px; height: 600px;"><?php
    $handle = curl_init("https://youtube.googleapis.com/youtube/v3/search?part=snippet&maxResults=25&q=JaimeJojo&key=AIzaSyDzsndcMwxVXmIUJsA17HOBmZO2rNcX3Ck");
                    
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('application/json'));
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    
                

    while(empty($err->error)){
        $err = json_decode(curl_exec($handle));
    }
    
    print_r($err);

?></textarea>