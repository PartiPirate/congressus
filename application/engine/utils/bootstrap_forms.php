<?php /*
	Copyright 2014 Cédric Levieux, Parti Pirate

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

function addShareButton($id, $btnClass, $btnStyle, $url, $text, $hashtags) {
	$twitterUrl = "https://twitter.com/intent/tweet/?text=" . urlencode($text) . "&url=" . urlencode($url) . "&hashtags=" . urlencode($hashtags);
	$facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url);
	$googlePlusUrl = "https://plus.google.com/share?url=" . urlencode($url);
	$emailUrl = "mailto:?subject=".urlencode($text)."&body=" . urlencode($url);
?>
	<div class="dropdown share-container" style="display: inline-block;">
		<div class="btn <?php echo $btnClass; ?> dropdown-toggle" data-toggle="dropdown" type="button" style="<?php echo $btnStyle; ?>" id="<?php echo $id; ?>">
			<i class="fa fa-share-alt" aria-hidden="true"></i> <?php echo lang("common_share"); ?> <span class="caret"></span>
		</div>
		<div class="dropdown-menu" aria-labelledby="<?php echo $id; ?>">
			<a class="dropdown-item social-link" href="<?php echo $twitterUrl; ?>" data-popup-width="550" data-popup-height="285" ><i class="fa fa-twitter" aria-hidden="true"></i>Twitter</a><br>
			<a class="dropdown-item social-link" href="<?php echo $facebookUrl; ?>" data-popup-width="550" data-popup-height="269"><i class="fa fa-facebook" aria-hidden="true"></i>Facebook</a><br>
			<a class="dropdown-item social-link" href="<?php echo $googlePlusUrl; ?>"><i class="fa fa-google-plus" aria-hidden="true"></i>Google+</a><br>
			<a class="dropdown-item direct-link" href="<?php echo $url; ?>" data-popup-text=<?php echo json_encode($text); ?>  data-popup-width="550" data-popup-height="60"><i class="fa fa-link" aria-hidden="true"></i>Lien direct</a><br>
			<a class="dropdown-item" href="<?php echo $emailUrl; ?>"><i class="fa fa-envelope" aria-hidden="true"></i>Email</a>
		</div>
	</div>
<?php
}
?>