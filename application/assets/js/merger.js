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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/

/* global stringDiff */

var uniformize = function(diff) {
    var uniDiff = [];

    for(var index = 0; index < diff.length; ++index) {
        for(var jndex = 0; jndex < diff[index][1].length; ++jndex) {
            var chunk = [];
            chunk[0] = diff[index][0];
            chunk[1] = diff[index][1][jndex];

            uniDiff.push(chunk);
        }
    }

    return uniDiff;
}

function merge(source, mergers) {

//    console.log(composer);

    var composer = [];
    for(var index = 0; index < mergers.length; ++index) {
        composer.push([uniformize(stringDiff(source, mergers[index], true)), 0]);
    }

    var composed = "";
    var composing = true;

    do {
        var endOfComposition = 0;
        for(var index = 0; index < composer.length; ++index) {
            var currentComposer = composer[index];
            if (currentComposer[0].length <= currentComposer[1]) {
                endOfComposition++;
            }
        }

        if (endOfComposition == composer.length) {
            composing = false;
            break;
        }

        var allEqual = true;
        var hasMinus = false;

        for(var index = 0; index < composer.length; ++index) {
            do {
                var currentComposer = composer[index];
                var symbol = null;

                if (currentComposer[0].length <= currentComposer[1]) {
                    // This composer may be arrived at its end
                    continue;
                }

                symbol = currentComposer[0][currentComposer[1]][0];
                
                if (symbol != "=") {
                    if (symbol == "-") {
                        allEqual = false;
                        hasMinus = true;
                    }
                    else {
                        var letter = currentComposer[0][currentComposer[1]][1];
//                        console.log("On ajoute " + letter + " du " + index + " composer");
                        composed += letter;
                        composer[index][1]++;
                    }
                }
            }
            while(symbol == "+");
        }

        var endOfComposition = 0;
        for(var index = 0; index < composer.length; ++index) {
            var currentComposer = composer[index];
            if (currentComposer[0].length <= currentComposer[1]) {
                endOfComposition++;
            }
        }

        if (endOfComposition == composer.length) {
            composing = false;
            break;
        }

        if (allEqual) {
            var currentComposer = composer[0];
            var letter = currentComposer[0][currentComposer[1]][1];
//            console.log("On garde " + letter);

            composed += letter;

            for(var index = 0; index < composer.length; ++index) {
                composer[index][1]++;
            }
        }
        else if (hasMinus) {
            var currentComposer = composer[0];
            var letter = currentComposer[0][currentComposer[1]][1];
//            console.log("On oublie " + letter);

            for(var index = 0; index < composer.length; ++index) {
                composer[index][1]++;
            }
        }
        else {
            composing = false;
        }
    }
    while(composing);

    return composed;
}