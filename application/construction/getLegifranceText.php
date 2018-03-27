<?php
    $url = $_GET["url"];
    $doc = new DOMDocument();
    $doc->loadHTMLFile($url);
    
    $data = array("status" => "ko");
    
    $divs = $doc->getElementsByTagName('div');
    $h2s  = $doc->getElementsByTagName('h2');
    
    foreach ($h2s as $h2) {
        $data["title"] = trim($h2->textContent);
        $data["status"] = "ok";
    }

    $data["articles"] = array();

    foreach ($divs as $div) {

        $classAttr = $div->getAttributeNode("class");
        if (!$classAttr) continue;
        if ($classAttr->value != "article") continue;

        $article = array();

        $idivs = $div->getElementsByTagName('div');
        foreach ($idivs as $idiv) {
            $iclassAttr = $idiv->getAttributeNode("class");
            if (!$iclassAttr) continue;
            if ($iclassAttr->value != "titreArt") continue;
            
            $article["title"] = trim($idiv->firstChild->textContent);
        }

        // Les articles abrogés n'apparaissent pas
        if (!isset($article["title"])) continue;

        $ips = $div->childNodes;

        $content = "";
        $contentSeparator = "";
        foreach ($ips as $ip) {
            if ($ip->nodeType == XML_ELEMENT_NODE && strtolower($ip->tagName) == "div") continue;

            $text = trim($ip->textContent);
            
            if (!$text) continue;

            $content .= $contentSeparator . $text;
            $contentSeparator = "\n";
        }

        $article["content"] = trim($content);

        $data["articles"][] = $article;
    }

    echo json_encode($data);
?>