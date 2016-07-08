<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/

function addAlertDialog($id, $text, $level = "default") {
	$formElement = "";
	$formElement .= "<div id='$id' class='alert alert-$level otbHidden' role='alert'>$text</div>";

	return $formElement;
}

function addPagination($numberOfElements, $numberOfElementsPerPage, $currentPage = 1, $paginate = true) {

	if ($numberOfElements < $numberOfElementsPerPage) return "";

	$formElement = "";

	$formElement .= "<nav class=\"text-center\">";
	$formElement .= "	<ul class=\"pagination ".($paginate ? "" : "no-pagination")."\">";
	$formElement .= "		<li class=\"" . (($currentPage == 1) ? "disabled" :"") ."\"><a href=\"#\"><span aria-hidden=\"true\">&laquo;</span><span class=\"sr-only\">Previous</span> </a></li>";

	for($page = 1; $page <= ceil($numberOfElements / $numberOfElementsPerPage); $page++) {
		if ($page == $currentPage) {
			$formElement .= "		<li class=\"active\"><a href=\"#\">$page</a></li>";
		}
		else {
			$formElement .= "		<li><a href=\"#\">$page</a></li>";
		}
	}

	$formElement .= "		<li class=\"" . (($currentPage == $page - 1) ? "disabled" :"") ."\"><a href=\"#\"><span aria-hidden=\"true\">&raquo;</span><span class=\"sr-only\">Next</span> </a></li>";
	$formElement .= "	</ul>";
	$formElement .= "</nav>";

	return $formElement;
}
?>