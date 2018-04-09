<?php
    $url = $_GET["url"];
    
    if (strpos($url, "https://congressus.partipirate.org/") !== false) {
        $url = str_replace("https://congressus.partipirate.org/", "http://127.0.0.1/", $url);
    }
    
    $doc = new DOMDocument();
    $doc->loadHTMLFile($url);
    
    $data = array("status" => "ko");
    
    $destinationArea = $doc->getElementById("motion-json");

/*
    if ($motionTitle) {
        $data["title"] = trim($motionTitle->textContent);
    }    
*/

    if ($destinationArea) {
        $motion = json_decode(trim($destinationArea->textContent), true);
        
/*
        $data["motion"] = $motion;
*/        
        $motion["mot_description"] = str_replace("#lt;", "<", $motion["mot_description"]);

        $data["content"] = $motion["mot_description"];
        $data["title"] = $motion["mot_title"];
        $data["status"] = "ok";
    }

    echo json_encode($data);
?>