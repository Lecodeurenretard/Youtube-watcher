<!doctype HTML>
<html>
    <?php 
        session_start();
        if($_SERVER["REQUEST_METHOD"] == "POST"){$playload =& $_POST;}
        elseif($_SERVER["REQUEST_METHOD"] == "GET"){$playload =& $_GET;}
        else{
            throw new Error("Wrong method");
        }
        
        $canSearch = isset($playload["search"]);
        
        function ifNotSet(&$var, &$toSet, $defaultVal = ''){
            if(empty($var)){
                $toSet = $defaultVal;
            }else{
                $toSet = $var;
            }
        }
    ?>
    
    <head>
        <meta charset="utf-8" />
        <title><?php echo (($canSearch) ? "Résultats de la recherche: \"" . $playload['search'] . "\"" : "Watch youtube videos!"); ?></title>
        <link href="img/logo-mini.png" type="img/png" rel="favicon"/>
        <link href="img/logo-mini.png" type="img/png" rel="icon"/>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Nanum+Gothic+Coding&family=PT+Serif&display=swap');
            
            <?php require("style.css"); ?>
            
            table{
                caption-side: bottom;
                
                border-collapse: collapse;
                border: black 2px solid;
                border-style: none solid;
                
                margin: 0px 10vw;
            }
            
            caption{
                font-size: 1.5em;
            }
            
            td, th{
                border: black 2px solid;
                
                padding-right: 35px;
            }
        
            td{
                color: red;
                font-family: 'PT Serif', 'sans-serif';
                border-width: 2px 1px;
            }
            
            td.typeList{
                color: orange;
            }
            
            td.typeChan{
                color: rgb(120 225 120);
            }
            
            td.title a{
                font-family: 'Nanum Gothic Coding', 'sans-serif';
                text-decoration: none;
            }
            
            th{
                background-color: silver;
                border-style: solid none;
            }
            
            .unbreakable{
                display: inline-block;
            }
            
            input{
                display: block;
            }
            
            input::placeholder{
                color: cadetblue;
            }
        </style>
    </head>
    
    
    <body>
        <?php require("header.html"); ?>
        <?php if(isset($playload["token"])): ?>
            <h1>Voici les résultats!</h1>
            <?php
            
                $tosearch = $playload["search"];
                $tosearch = urlencode($tosearch);   //on fait en sorte que ça passe dans l'url
                ifNotSet($playload["nbppage"], $nb, 25);
                
                $handle = curl_init("https://youtube.googleapis.com/youtube/v3/search?part=snippet&q=$tosearch&maxResults=$nb&pagetoken=" . $playload["token"] . "&key=AIzaSyBm10K2I-QgkzCB8zUxnmoQTcu1UaSH9_E");
                
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('application/json'));
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($handle);

                if($response === false){throw new Error("L'éxécution de la requète à échouée.");}
               
                $converted = json_decode($response);
                if(property_exists($converted, 'error')){
                    $err = $converted->error;
                    
                    switch($err->errors[0]->reason){
                        case "quotaExceeded":
                            echo '<em class="error">Une Erreur est survenue, veuillez réessayer dans une heure et en cas d\'échec attendez demain.</em>';
                            break;
                            
                        case "badRequest":
                            if($err->errors[0]->message == "API key not valid. Please pass a valid API key."){echo "Clef API invalide, contactez <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev)";}
                            else{echo "<em class='error'>Une erreur inconnue est survenue, veuillez réessayer.</em><textarea hidden>"; print_r($err);  echo"</textarea>";}
                            break;
                            
                        case "accessNotConfigured":
                            echo "<em class='error'>Requète Bloquée à cause d'un abus, Contactez immédiatement <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev) pour le lui faire savoir.</em>";
                            break;
                        
                        case "concurrentLimitExceeded":
                            echo "<em class='error'>Une Erreur est survenue, veuillez réessayer</em>";
                            break;
                        
                        case "dailyLimitExceeded":
                            echo '<em class="error">Une Erreur est survenue, veuillez réessayer demain.<br />Si l\'erreur se répète, contacter <a href=\'mailto:leretardatn@gmail.com\'>LeRetardatN</a> (dev)</em>';
                            break;
                            
                        case "internalError":
                            echo "Une erreur du côté des serveurs de Youtube, vous pouvez contacter <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev).";
                            break;
                            
                        case "movedPermanently":
                            echo "Une erreur est survenue, contactez <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev) pour qu'il la répare.";
                            break;
                            
                        case "invalidQuery":
                            echo "Recherche interdite, veuillez reformuler la recherche.";
                            break;
                        
                        default:
                            echo "<em class='error'>Une erreur inconnue est survenue, veuillez réessayer.</em><textarea hidden>"; print_r($err);  echo"</textarea>";
                    }
                    echo "<br /><p ". (empty($_SESSION["err"]) ? "hidden" : '') .">Contact du dev: <a href='mailto:leretardatn@gmail.com' >LeRetardatN</a></p>";
                    $_SESSION["err"] = (isset($_SESSION["err"]) ? ($_SESSION["err"]+1) : 1);
                    exit;
                }
                
                ifNotSet($converted->nextPageToken, $nextPageToken);
                ifNotSet($converted->prevPageToken, $prevPageToken);
                $converted = $converted->items;
            ?>
            <table>
            <caption>Les résultats de la recherche</caption>
            
            <th>Le type d'objet</td>
            <th>Le lien principal</td>
            <th>autre</td>
            <?php

                    
                foreach($converted as $obj){
                    if($obj->id->kind == "youtube#video"){
                        echo('<tr>
                                <td class="typeVid">video</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="/watch.php?vid='.  $obj->id->videoId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . '</a></td>'.
                                '<td></td>'.
                            '</tr>');
                    }elseif($obj->id->kind == "youtube#playlist"){
                         echo('<tr>
                                <td class="typeList">playlist</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="http://www.youtube.nils.test.sc2mnrf0802.universe.wf/playlist.php?list='. $obj->id->playlistId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . ' <span class="unbreakable">(sur youtube.nils.test)</span></a></td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="https://youtube.com/playlist?list='.  $obj->id->playlistId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . ' <span class="unbreakable">(sur Youtube)</span></a></td>'.
                            '</tr>');
                    }elseif($obj->id->kind == "youtube#channel"){
                         echo('<tr>
                                <td class="typeChan">Chaine</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="https://youtube.com/channel/'.$obj->id->channelId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . '</a></td>'.
                                '<td></td>'.
                            '</tr>');
                    }else{
                        echo('<tr><td><strong class="error">Ressource inconnue, contactez le developpeur.</strong></td><td>' . str_replace('youtube#', '', $obj->id->kind) );
                    }
                }
                
            ?>
            </table>
            
            <script>
                function preSubmit(go){
                    const input = document.querySelector('input#where'),
                        next = document.querySelector('button#suiv'),
                        prev = document.querySelector('button#prec');
                        
                        if(go){ //going to next page
                            input.value = "<?php echo $nextPageToken ?>";    //nextPageToken
                        }else{
                            input.value = "<?php echo $prevPageToken ?>"    //prevPageToken
                        }
                    
                        prev.remove();
                        
                        const newButt = document.createElement("button");
                        newButt.innerText = "Aller à page " + (go ? "suivante" : "précédente");
                        newButt.setAttribute("type", "submit");
                        
                        next.parentElement.appendChild(newButt);
                        next.remove();
                }
            </script>
            
            <form method="POST">
                <input type="hidden" id="where" name="token" required />
                <input type="hidden" id="what" name="search" required />
                <input type="hidden" name="search" value="<?php echo $playload["search"]; ?>" required />
                <button type="button" onclick="preSubmit(false)" id="prec">Page précédente</button>
                <button type="button" onclick="preSubmit(true)" id="suiv">Page suivante</button>
            </form>
        
        
        
        <?php elseif(!$canSearch): ?>
            
            <h1>Faîtes une recherche youtube</h1>
            
            <form id="submit" method="POST">
               <input id="search" name="search" placeholder="Recherche" form="submit" />
                <input id="nbppage" name="nbppage" type="number" placeholder="Nombre de résultats" min="0" minlenght="1" />
                <button type="submit">Rechercher</button>
            </form>

            
        <?php else: ?>
            <h1>Voici les résultats!</h1>
            <?php
                $tosearch = $playload["search"];
                
                $tosearch = urlencode($tosearch);   //on fait en sorte que ça passe dans l'url
                $nb = (empty($playload["nbppage"])) ? 25 : $playload["nbppage"];                
                
                $handle = curl_init("https://youtube.googleapis.com/youtube/v3/search?part=snippet&maxResults=$nb&q=$tosearch&key=AIzaSyBm10K2I-QgkzCB8zUxnmoQTcu1UaSH9_E"); 
                    
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('application/json'));
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    
                $response = curl_exec($handle);
                
                if($response === false){throw new Error("L'éxécution de la requète à échouée.");}
               
                $converted = json_decode($response);
                if(isset($converted->error)){
                    $err = $converted->error;
                    
                    $email = false; //si l'email est display
                    switch($err->errors[0]->reason){
                        case "quotaExceeded":
                            echo '<em class="error">Une Erreur est survenue, veuillez réessayer dans une heure et en cas d\'échec attendez demain.</em>';
                            break;
                            
                        case "badRequest":
                            if($err->errors[0]->message == "API key not valid. Please pass a valid API key."){echo "Clef API invalide, contactez <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev)";}
                            else{echo "<em class='error'>Une erreur inconnue est survenue, veuillez réessayer.</em><textarea hidden>"; print_r($err);  echo"</textarea>";}
                            $email = true;
                            break;
                            
                        case "accessNotConfigured":
                            echo "<em class='error'>Requète Bloquée à cause d'un abus, Contactez immédiatement <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev) pour le lui faire savoir.</em>";
                            $email = true;
                            break;
                        
                        case "concurrentLimitExceeded":
                            echo "<em class='error'>Une Erreur est survenue, veuillez réessayer</em>";
                            break;
                        
                        case "dailyLimitExceeded":
                            echo '<em class="error">Une Erreur est survenue, veuillez réessayer demain.</em>';

                            break;
                            
                        case "internalError":
                            echo "Une erreur du côté des serveurs de Youtube, vous pouvez contacter <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev).";
                            $email = true;
                            break;
                            
                        case "movedPermanently":
                            echo "Une erreur est survenue, contactez <a href='mailto:leretardatn@gmail.com'>LeRetardatN</a> (dev) pour qu'il la corrige.";
                            $email = true;
                            break;
                            
                        case "invalidQuery":
                            echo "Recherche interdite, veuillez reformuler la recherche.";
                            break;
                        
                        default:
                            echo "<em class='error'>Une erreur inconnue est survenue, veuillez réessayer.</em><textarea hidden>"; print_r($err);  echo"</textarea>";
                    }
                    echo "<br /><p ". ((empty($_SESSION["err"]) && !$email) ? "hidden" : '') .">Contact du dev: <a href='mailto:leretardatn@gmail.com' >LeRetardatN</a></p>";
                    $_SESSION["err"] = (isset($_SESSION["err"]) ? ($_SESSION["err"]+1) : 1);
                    exit;
                }
                
                ifNotSet($converted->nextPageToken, $nextPageToken);
                ifNotSet($converted->prevPageToken, $prevPageToken);
                $converted = $converted->items;
            ?>
            <table>
            <caption>Les résultats de la recherche</caption>
            
            <th>Le type d'objet</td>
            <th>Le lien principal</td>
            <th>autre</td>
            <?php
                    
                foreach($converted as $obj){
                    if($obj->id->kind == "youtube#video"){
                        echo('<tr>
                                <td class="typeVid">video</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="/watch.php?vid='.  $obj->id->videoId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . '</a></td>'.
                                '<td></td>'.
                            '</tr>');
                    }elseif($obj->id->kind == "youtube#playlist"){
                         echo('<tr>
                                <td class="typeList">playlist</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="http://www.youtube.nils.test.sc2mnrf0802.universe.wf/playlist.php?list='. $obj->id->playlistId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . ' <span class="unbreakable">(sur youtube.nils.test)</span></a></td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="https://youtube.com/playlist?list='.  $obj->id->playlistId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . ' <span class="unbreakable">(sur Youtube)</span></a></td>'.
                            '</tr>');
                    }elseif($obj->id->kind == "youtube#channel"){
                         echo('<tr>
                                <td class="typeChan">Chaine</td>
                                <td class="title" title="'.$obj->snippet->title.'"><a href="https://youtube.com/channel/'.$obj->id->channelId .'">' . substr($obj->snippet->title, 0, 30) . (strlen($obj->snippet->title)>30? '...' : '') . '</a></td>'.
                                '<td></td>'.
                            '</tr>');
                    }else{
                        echo('<tr><td><strong class="error">Ressource inconnue, contactez le developpeur.</strong></td><td>' . str_replace('youtube#', '', $obj->id->kind) );
                    }
                }
                
            ?>
            </table>
            
            <script>
                function preSubmit(go){
                    const input = document.querySelector('input#where'),
                        next = document.querySelector('button#suiv'),
                        prev = document.querySelector('button#prec'),
                        inputQ = document.querySelector('input#what'),
                        query = "<?php echo $playload["search"] ?>";   //vient de PHP
                        
                        if(go){ //going to next page
                            input.value = "<?php echo $nextPageToken ?>";    //nextPageToken
                        }else{
                            input.value = "<?php echo $prevPageToken ?>"    //prevPageToken
                        }
                        inputQ.value = query;
                    
                        prev.remove();
                        
                        const newButt = document.createElement("button");
                        
                        newButt.innerText = "Aller à page " + (go ? "suivante" : "précédente");
                        newButt.setAttribute("type", "submit");
                        
                        next.parentElement.appendChild(newButt);
                        next.remove();
                }
            </script>
            
            <form method="POST">
                <input type="hidden" id="where" name="token" required />
                <input type="hidden" id="what" name="search" required />
                <button type="button" onclick="preSubmit(false)" id="prec">Page précédente</button>
                <button type="button" onclick="preSubmit(true)" id="suiv">Page suivante</button>
            </form>
            
        <?php endif; ?>
    </body>
</html>