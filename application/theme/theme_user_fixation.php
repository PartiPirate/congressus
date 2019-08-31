<?php /*
	Copyright 2015-2018 Cédric Levieux, Parti Pirate

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

<!-- Fixation part -->

<?php if (!$fixation || $theme["the_delegate_only"] == "1") return; ?>

<div class="panel panel-default currentFixation">
	<div class="panel-heading">
		<?php echo lang("theme_mandates_label"); ?>&nbsp;
	</div>
	<table class="table no-pagination">
		<thead>
			<tr>
				<th><?php echo lang("theme_mandates_name"); ?></th>
				<th><?php echo lang("theme_mandates_power"); ?></th>
				<!--
				<th>Actions</th>
				 -->
			</tr>
		</thead>
		<tbody>
<?php		foreach($fixation["members"] as $memberId => $member) { ?>
			<tr>
				<td><a href="member.php?id=<?php echo $memberId; ?>"><?php echo GaletteBo::showIdentity($member); ?></a></td>
				<td style="text-align: right;"><?php echo $member["fme_power"]?></td>
				<!--
				<td>Voir <?php if ($isElegible) {?>Déléguer<?php }?></td>
				 -->
			</tr>
<?php 		}?>
		</tbody>
	</table>
	<div class="panel-footer text-right">
		<span class="glyphicon glyphicon-time"></span> <span clasœs="date"><?php echo $fixation["fix_until_date"]; ?>
	</div>
</div>

