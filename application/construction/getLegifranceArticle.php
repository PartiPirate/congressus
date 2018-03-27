<?php
    $url = $_GET["url"];
    $doc = new DOMDocument();
    $doc->loadHTMLFile($url);
    
    $data = array("status" => "ko");
    
    $divs = $doc->getElementsByTagName('div');
    $h2s  = $doc->getElementsByTagName('h2');
    

    foreach ($h2s as $h2) {
        $data["title"] = trim($h2->textContent);
    }
    
    foreach ($divs as $div) {

        $classAttr = $div->getAttributeNode("class");
        
        if (!$classAttr) continue;
        
//        print_r($classAttr);
        
        if ($classAttr->value == "titreArt") {
            $data["articleTitle"] = trim($div->textContent);
        }
        if ($classAttr->value == "corpsArt") {
            $data["content"] = trim($div->textContent);
            $data["status"] = "ok";
        }
    }
    
    echo json_encode($data);
?>