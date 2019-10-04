<?php /*
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

require_once("language/language.php");

function languageToPHPQuorum($quorumFormula) {
    // Don't call me crazy
    $quorumFormula = str_replace('$',             '', $quorumFormula);
    $quorumFormula = str_replace('eval',          '', $quorumFormula);
    $quorumFormula = str_replace('return',        '', $quorumFormula);
    $quorumFormula = str_replace('base64_decode', '', $quorumFormula);
    // Normal stuff
    $quorumFormula = str_replace(lang("setQuorum_numberOfConnected"), '$numberOfConnected', $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_numberOfPresents"),  '$numberOfPresents',  $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_numberOfVoters"),    '$numberOfVoters',    $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_numberOfNoticed"),   '$numberOfNoticed',   $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_numberOfPowers"),    '$numberOfPowers',    $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_sqrt"),              'sqrt',               $quorumFormula);
    $quorumFormula = str_replace(lang("setQuorum_percentage"),        ' / 100',             $quorumFormula);

    return $quorumFormula;
}

function phpToLanguageQuorum($quorumFormula) {
    $quorumFormula = str_replace('$numberOfConnected', lang("setQuorum_numberOfConnected"), $quorumFormula);
    $quorumFormula = str_replace('$numberOfPresents',  lang("setQuorum_numberOfPresents"),  $quorumFormula);
    $quorumFormula = str_replace('$numberOfVoters',    lang("setQuorum_numberOfVoters"),    $quorumFormula);
    $quorumFormula = str_replace('$numberOfNoticed',   lang("setQuorum_numberOfNoticed"),   $quorumFormula);
    $quorumFormula = str_replace('$numberOfPowers',    lang("setQuorum_numberOfPowers"),    $quorumFormula);
    $quorumFormula = str_replace('sqrt',               lang("setQuorum_sqrt"),              $quorumFormula);
    $quorumFormula = str_replace(' / 100',             lang("setQuorum_percentage"),        $quorumFormula);

    return $quorumFormula;
}