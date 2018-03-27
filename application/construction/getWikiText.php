<?php
    $url = $_GET["url"];
    $doc = new DOMDocument();
    $doc->loadHTMLFile($url);
    
    $data = array("status" => "ko");
    $data["articles"] = array();
    
    $title = $doc->getElementById("firstHeading");

    if ($title) {
        $data["title"] = trim($title->textContent);
        $data["status"] = "ok";
    }

    $tags = $doc->getElementById("mw-content-text")->firstChild->childNodes;

    $article = null;

    foreach($tags as $tag) {

        if (strtolower($tag->tagName) == "h1") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 1, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h2") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 2, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h3") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 3, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h4") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 4, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h5") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 5, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h6") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 6, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "h7") {
            if ($article) $data["articles"][] = $article;
            $article = array("level" => 7, "title" => $tag->textContent, "content" => "");
        }
        else if (strtolower($tag->tagName) == "p") {
            $article["content"] .= $tag->textContent . "\n";
        }
        else if (strtolower($tag->tagName) == "ul") {
            foreach($tag->getElementsByTagName("li") as $childNode) {
                $article["content"] .= "* " . $childNode->textContent . "\n";
            }
            $article["content"] .= "\n";
        }
        else if (strtolower($tag->tagName) == "ol") {
            foreach($tag->getElementsByTagName("li") as $childNode) {
                $article["content"] .= "# " . $childNode->textContent . "\n";
            }
            $article["content"] .= "\n";
        }
        
        
    }

    if ($article) $data["articles"][] = $article;

    echo json_encode($data);
?>