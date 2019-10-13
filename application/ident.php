<?php /*
    Copyright 2015 Cédric Levieux, Parti Pirate

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
<div style="clear: both;"></div>
<div>
<?php

//print_r($_SESSION);

if ($isConnected) {
	echo $sessionUser["pseudo_adh"];
}
else {
?>
<form action="do_connect.php" method="post">
	<input name="login" type="text" placeholder="Login ou email" />
	<input name="password" type="password" placeholder="Mot de passe" />
	<button type="submit">S'identifier</button>
</form>
<?php
}
?>
</div>