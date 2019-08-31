<?php /*
	Copyright 2015-2019 Cédric Levieux, Parti Pirate

	This file is part of Personae.

    Personae is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Personae is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Personae.  If not, see <http://www.gnu.org/licenses/>.
*/
?>

<!-- DELEGATION ADVANCED part -->

<?php if (!$isVoting || $theme["the_voting_method"] != "demliq") return; ?>

<div class="panel panel-default voting">
	<div class="panel-heading">
		<?php echo lang("theme_demliq_label"); ?>&nbsp;
		
		<a href="#delegation-advanced" id="advanced-link" class="pull-right no-collapse"><?php echo lang("theme_demliq_delegation_advanced"); ?></a>
		<a href="#delegation-standard" id="standard-link" class="pull-right no-collapse soft-hidden"><?php echo lang("theme_demliq_delegation_standard"); ?></a>
	</div>
	<div class="panel-body tabs-bottom">

        <!-- Tab panes -->
        <div class="tab-content">
        	<div role="tabpanel" class="tab-pane active" id="delegation-standard">
        <?php include("theme/theme_user_delegation_standard.php"); ?>
            </div>
        	<div role="tabpanel" class="tab-pane" id="delegation-advanced">
        <?php include("theme/theme_user_delegation_advanced.php"); ?>
            </div>
        </div

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist" style="margin: 0 -16px -15px -16px;">
        	<li role="presentation" class="active">
        		<a href="#delegation-standard" aria-controls="delegation-standard" role="tab" data-toggle="tab" id="standard-tab"><?php echo lang("theme_demliq_delegation_standard"); ?></a>
        	</li>
        	<li role="presentation">
        		<a href="#delegation-advanced" aria-controls="delegation-advanced" role="tab" data-toggle="tab" id="advanced-tab"><?php echo lang("theme_demliq_delegation_advanced"); ?></a>
        	</li>
        </ul>
        

	</div>
</div>
