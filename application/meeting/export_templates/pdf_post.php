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

//$content = "BOUH";

include("mpdf/mpdf.php");

$mpdf = new mPDF();
$mpdf->WriteHTML($content);

$content = $mpdf->Output('', 'S');

$filename = "export.pdf";

header("Content-type:application/pdf");
header("Content-Disposition:inline;filename='$filename");
header('Content-Length: '.strlen( $content ));

?>