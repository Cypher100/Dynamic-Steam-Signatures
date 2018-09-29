<?php
    //Include configuration settings
    include('config.php');

    //Set header information
    header("Content-type: image/png");
    header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
    header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 180));

    //Grab information from URL.
    $skin = preg_replace('/[^A-Za-z0-9\s_-]/', '', $_GET["skin"]);
    $username = strtolower(preg_replace('/[^A-Za-z0-9\s_-]/', '', $_GET["username"]));

    //Put signature path together
    $sigpath = $temp . $skin . $username . ".png";

    //Check if signature has already been made within the cache range.
    if ((file_exists($sigpath)) && (time() - filemtime($sigpath) < $cachetime)) {
        readfile($sigpath);
        exit;
    }

    //Check if strings are empty.
    if (!$skin || !$username) {
        readfile($steamidnotfound);
        exit;
    }

    //Set Skin Path
    $skinbackground = "skins/" . $skin . "/background.png";

    //Check if the skin exists before doing anything else
    if (!file_exists($skinbackground)) {
        readfile($skinnotfound);
        exit;
    }

    //Checks if it's a 64bit steam id or a custom username
    if(is_numeric($username) && strlen($username) == 17){
        $xmlProfileurl = "https://steamcommunity.com/profiles/" . $username . "/?xml=1";
    } else {
        $xmlProfileurl = "https://steamcommunity.com/id/" . $username . "/?xml=1";
    }

    //Download XML using curl, then load XML file.
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $xmlProfileurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $xmlProfile = simplexml_load_string(curl_exec($ch));
        curl_close($ch);

    //Check XML file if it's empty.
    if (!$xmlProfile || !$xmlProfile->steamID) {
        readfile($steamidnotfound);
        exit;
    }
    //Check if steam profile is private
    if ($xmlProfile->visibilityState == "1") {
        readfile($steamidprivate);
        exit;
    }

    // Obtain XML for steam levels, retry if XML download fails
    if($apikey){
        $retry = 0;
        do {
            $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.steampowered.com/IPlayerService/GetBadges/v1/?key=" . $apikey .  "&steamid=" . $xmlProfile->steamID64 . "&format=xml");
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $xmlLevel = simplexml_load_string(curl_exec($ch));
                curl_close($ch);
                $retry++;
                if($xmlLevel->player_level) {
                    $retry = 4;
                }
        } while ($retry < 3);
    }

    //Load skin background
    $im = imagecreatefrompng($skinbackground);

    //Check users status, to set avatar border. Then place border on top of skin background.
    switch($xmlProfile->onlineState) {
        case "online":
            $color = imagecolorallocate($im, 111, 189, 255);
            $border = "skins/online.png";
            break;
        case "offline":
            $color = imagecolorallocate($im, 137, 137, 137);
            $border = "skins/offline.png";
            break;
        case "in-game":
            //Clean up stateMessage to make it look pretty on the final image.
            $xmlProfile->stateMessage = str_replace("In-Game<br/>", "In-Game: ", $xmlProfile->stateMessage);
            $xmlProfile->stateMessage = strip_tags($xmlProfile->stateMessage);
            $xmlProfile->stateMessage = str_replace(" - Join", "", $xmlProfile->stateMessage);
            if(strlen($xmlProfile->stateMessage) > 33){
                $xmlProfile->stateMessage = substr($xmlProfile->stateMessage,0,(strlen($xmlProfile->stateMessage) - 33)*-1)."...";
            }
            $color = imagecolorallocate($im, 177, 251, 80);
            $border = "skins/ingame.png";
            break;
        default:
            readfile($steamidprivate);
            exit;
    }
    
    //Load chosen border
    $imstatus = imagecreatefrompng($border);

    //Download avatar icon, then load it up.
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $xmlProfile->avatarIcon);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $imicon = imagecreatefromstring(curl_exec($ch));
        curl_close($ch);

    //Check if user has set country on their profile.
    if($xmlProfile->location){
        //Load country's information to detect which country image to place on final image.
        include("countrys.php");
        if(strrpos($xmlProfile->location,",")){
            //Clean string, so only country is in the string.
            $countryinfo = substr($xmlProfile->location,strrpos($xmlProfile->location,", ")+2);
            if(strrpos($countryinfo,"(")){
                $countryinfo = trim(substr($countryinfo,strpos($countryinfo,"(")),"()");
            }
        } else {
            $countryinfo = substr($xmlProfile->location,0);
        }

        //If country found, load country image.
        if(array_key_exists($countryinfo,$countrys)){
            $imcountry = imagecreatefromgif("skins/countrys/".$countrys[$countryinfo].".gif");
        }
    }

    //Paste country flag
    if($imcountry){
        imagecopy($im, $imcountry, 56, 37, 0, 0, 16, 11);
        imagedestroy($imcountry);
        //Place User Level
        if($xmlLevel->player_level) {
            imagettftext($im, $fsize, 0, 75, 46, $color, $font, "Level: " . $xmlLevel->player_level);
        }
    } elseif($xmlLevel->player_level) {
        //Place User Level
        imagettftext($im, $fsize, 0, 55, 46, $color, $font, "Level: " . $xmlLevel->player_level);
    }

    //Paste status border
    imagecopy($im, $imstatus, 9, 8, 0, 0, 40, 40);
    imagedestroy($imstatus);

    //Place Avatar
    imagecopy($im, $imicon, 13, 12, 0, 0, 32, 32);
    imagedestroy($imicon);

    //Place username text
    imagettftext($im, $fsize, 0, 55, 17, $color, $fontbold, html_entity_decode($xmlProfile->steamID));

    //Place status text
    imagettftext($im, $fsize, 0, 55, 32, $color, $font, $xmlProfile->stateMessage);

    //Save image into temp folder for cache.
    imagepng($im, $sigpath);
    imagedestroy($im);

    //Let's load up the final image from the temp folder
    readfile($sigpath);
?>
