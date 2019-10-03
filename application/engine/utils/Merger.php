<?php
/*
    Copyright 2019 Cédric Levieux, Parti Pirate

    This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

function mb_str_split($str, $len = 1) {

    $arr = array();
    $length	= mb_strlen($str, 'UTF-8');

    for ($i = 0; $i < $length; $i += $len) {

        $arr[] = mb_substr($str, $i, $len, 'UTF-8');

    }

    return $arr;
}

function uniformize($diff) {
    $uniDiff = array();

    for($index = 0; $index < count($diff); $index++) {
        if (is_array($diff[$index])) {
            foreach($diff[$index] as $symbol => $letters) {
                foreach($letters as $letter) {
                    $chunk = array();
                    $chunk[0] = $symbol;
                    $chunk[1] = $letter;
        
                    $uniDiff[] = $chunk;
                }
            }
        }
        else {
            $chunk = array();
            $chunk[0] = "=";
            $chunk[1] = $diff[$index];

            $uniDiff[] = $chunk;
        }
    }
    
    return $uniDiff;
}

function charDiff($a, $b) {
    $a = mb_str_split($a);
    $b = mb_str_split($b);

    $diff = diff($a, $b);

    return $diff;    
}

function merge($source, $changes) {
    $mergers = array();

    foreach($changes as $change) {
        $diff = charDiff($source, $change);
        $diff = uniformize($diff);
        $merger = array("offset" => 0, "diff" => $diff);
        $mergers[] = $merger;
    }

    $composing = true;
    $composed = "";

    do {
        $endOfComposition = 0;
        for($index = 0; $index < count($mergers); ++$index) {
            $currentComposer = $mergers[$index];
            if (count($currentComposer["diff"]) <= $currentComposer["offset"]) {
                $endOfComposition++;
            }
        }

        if ($endOfComposition == count($mergers)) {
            $composing = false;
            break;
        }

        $allEqual = true;
        $hasMinus = false;

        for($index = 0; $index < count($mergers); $index++) {
            do {
                $currentComposer = $mergers[$index];
                $symbol = null;

                if (count($currentComposer["diff"]) <= $currentComposer["offset"]) {
                    // This composer may be arrived at its end
                    continue;
                }

                $symbol = $currentComposer["diff"][$currentComposer["offset"]][0];
                
                if ($symbol != "=") {
                    if ($symbol == "-") {
                        $allEqual = false;
                        $hasMinus = true;
                    }
                    else {
                        $letter = $currentComposer["diff"][$currentComposer["offset"]][1];
//                        echo("On ajoute " . $letter . " du " . $index . " composer \n");
                        $composed .= $letter;
                        $mergers[$index]["offset"]++;
                    }
                }
            }
            while($symbol == "+");
        }

        $endOfComposition = 0;
        for($index = 0; $index < count($mergers); $index++) {
            $currentComposer = $mergers[$index];
            if (count($currentComposer["diff"]) <= $currentComposer["offset"]) {
                $endOfComposition++;
            }
        }

        if ($endOfComposition == count($mergers)) {
            $composing = false;
            break;
        }

        if ($allEqual) {
            $currentComposer = $mergers[0];
            $letter = $currentComposer["diff"][$currentComposer["offset"]][1];
//            echo("On garde " . $letter . "\n");

            $composed .= $letter;

            for($index = 0; $index < count($mergers); $index++) {
                $mergers[$index]["offset"]++;
            }
        }
        else if ($hasMinus) {
            $currentComposer = $mergers[0];
            $letter = $currentComposer["diff"][$currentComposer["offset"]][1];
//            echo("On oublie " . $letter . "\n");

            for($index = 0; $index < count($mergers); $index++) {
                $mergers[$index]["offset"]++;
            }
        }
        else {
            $composing = false;
        }
    }
    while($composing);



    return $composed;
}