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
        $xmlurl = "https://steamcommunity.com/profiles/" . $username . "/?xml=1";
    } else {
        $xmlurl = "https://steamcommunity.com/id/" . $username . "/?xml=1";
    }

    //Download XML using curl, then load XML file.
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $xmlurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $xml = simplexml_load_string(curl_exec($ch));
        curl_close($ch);

    //Check XML file if it's empty.
    if (!$xml || !$xml->steamID) {
        readfile($steamidnotfound);
        exit;
    }
    //Check if steam profile is private
    if ($xml->visibilityState == "1") {
        readfile($steamidprivate);
        exit;
    }

    //Load skin background
    $im = imagecreatefrompng($skinbackground);

    //Check users status, to set avatar border. Then place border on top of skin background.
    switch($xml->onlineState) {
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
            $xml->stateMessage = str_replace("In-Game<br/>", "In-Game: ", $xml->stateMessage);
            $xml->stateMessage = strip_tags($xml->stateMessage);
            $xml->stateMessage = str_replace(" - Join", "", $xml->stateMessage);
            if(strlen($xml->stateMessage) > 35){
                $xml->stateMessage = substr($xml->stateMessage,0,(strlen($xml->stateMessage) - 35)*-1)."...";
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
        curl_setopt($ch, CURLOPT_URL, $xml->avatarIcon);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $imicon = imagecreatefromstring(curl_exec($ch));
        curl_close($ch);

    //Check if user has set country on their profile.
    if($xml->location){
        //Load country's information to detect which country image to place on final image.
        include("countrys.php");
        if(strrpos($xml->location,",")){
            //Clean string, so only country is in the string.
            $countryinfo = substr($xml->location,strrpos($xml->location,", ")+2);
            if(strrpos($countryinfo,"(")){
                $countryinfo = trim(substr($countryinfo,strpos($countryinfo,"(")),"()");
            }
        } else {
            $countryinfo = substr($xml->location,0);
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
    }

    //Paste status border
    imagecopy($im, $imstatus, 9, 8, 0, 0, 40, 40);
    imagedestroy($imstatus);

    //Place Avatar
    imagecopy($im, $imicon, 13, 12, 0, 0, 32, 32);
    imagedestroy($imicon);

    //Place username text
    imagettftext($im, $fsize, 0, 55, 17, $color, $fontbold, html_entity_decode($xml->steamID));

    //Place status text
    imagettftext($im, $fsize, 0, 55, 32, $color, $font, $xml->stateMessage);

    //Save image into temp folder for cache.
    imagepng($im, $sigpath);
    imagedestroy($im);

    //Let's load up the final image from the temp folder
    readfile($sigpath);
?>
