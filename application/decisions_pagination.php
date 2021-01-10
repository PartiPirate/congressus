<?php /*
    Copyright 2020 Cédric Levieux, Parti Pirate

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
?>

<?php   if ($numberOfPages > 1) { ?>
    		    <div class="text-center pagination-container">
<?php       if ($currentPage > 0) { ?>
    		        <a class="pagination-link btn btn-info"                 href="?page=0"                       data-page="0" title="La page premiere"><i class="glyphicon glyphicon-fast-backward"></i></a>
    		        <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage - 1?>"   data-page="<?=$currentPage - 1?>" title="La page précédente"><i class="glyphicon glyphicon-backward"></i></a>
<?php       } 

            if ($currentPage > 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage - 2?>"   data-page="<?=$currentPage - 2?>"><?=$currentPage - 1?></a>
<?php       } 

            if ($currentPage > 0) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage - 1?>"   data-page="<?=$currentPage - 1?>"><?=$currentPage - 0?></a>
<?php       } ?>
                    <!-- page en cours -->
                    <!--
                    <a class="pagination-link btn btn-primary active-page"  href="?page=<?=$currentPage    ?>"   data-page="<?=$currentPage    ?>">
                    -->
                        <select class="page-select">
<?php       for($possiblePage = 0; $possiblePage < $numberOfPages; $possiblePage++) { ?>
                            <option <?=($possiblePage == $currentPage) ? 'selected="selected"' : ''?> data-page="<?=$possiblePage?>"><?=$possiblePage + 1?></option>
<?php       } ?>
                        </select>
                        /
                        <?=$numberOfPages?>
                    <!--
                    </a>
                    -->

<?php       if ($currentPage < $numberOfPages - 1) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage + 1?>"   data-page="<?=$currentPage + 1?>"><?=$currentPage + 2?></a>
<?php       } 

            if ($currentPage < $numberOfPages - 2) { ?>
                    <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage + 2?>"   data-page="<?=$currentPage + 2?>"><?=$currentPage + 3?></a>
<?php       }

            if ($currentPage < $numberOfPages - 1) { ?>
    		        <a class="pagination-link btn btn-info"                 href="?page=<?=$currentPage + 1?>"   data-page="<?=$currentPage + 1?>" title="La page suivante"><i class="glyphicon glyphicon-forward"></i></a>
    		        <a class="pagination-link btn btn-info"                 href="?page=<?=$numberOfPages - 1?>" data-page="<?=$numberOfPages - 1?>" title="La page dernière"><i class="glyphicon glyphicon-fast-forward"></i></a>
<?php       } ?>
    		    </div>
<?php   }