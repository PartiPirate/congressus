<?php /*
	Copyright 2015-2019 Cédric Levieux, Parti Pirate

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
include_once("header.php");


?>

<div class="container theme-showcase" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_my_page"); ?></li>
	</ol>
</div>

<div class="container">
    
#<?php echo $sessionUserId; ?>#
    
<pre>
<?php

foreach($config["modules"]["groupsources"] as $groupSourceId) {
    echo $groupSourceId;
    echo "\n";
    
    $groupSource = GroupSourceFactory::getInstance($groupSourceId);
    
    print_r($groupSource);
    echo "\n";
    
    echo "getGroups : " . (method_exists($groupSource, "getGroups") ? "oui" : "non");
    echo "\n";

    if (method_exists($groupSource, "getGroups")) {
        print_r($groupSource->getGroups($sessionUserId));
    }
    echo "\n";
}

?>
</pre>
    
</div>

<div class="lastDiv"></div>

<script>
</script>
<?php include("footer.php");?>

</body>
</html>