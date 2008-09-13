<?php
/*
 *   This file is part of PhpCompta.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
/* $Revision$ */
/*! \file
 * \brief Popup window to let the user choose the vat
 */

include_once ("ac_common.php");
include_once ("postgres.php");
/* Admin. Dossier */
$rep=DbConnect();
include_once ("class_user.php");
$User=new User($rep);
$User->Check();
require_once('class_dossier.php');
$gDossier=dossier::id();

html_min_page_start($User->theme,"onLoad='window.focus();'");
?>
<script>
function GetIt(ctl,tva_id) {
           self.opener.SetValue(ctl,tva_id)
	   window.close();	
	} 
</script>
<?php

$condition="";
$cn=DbConnect($gDossier);
$Res=ExecSql($cn,"select * from tva_rate order by tva_rate desc");
$Max=pg_NumRows($Res);
echo "<TABLE BORDER=\"1\">";
for ($i=0;$i<$Max;$i++) {
  $row=pg_fetch_array($Res,$i);
  $set=sprintf( '<INPUT TYPE="BUTTON" Value="select" onClick="GetIt(\'%s\',\'%s\');">',
	     $_GET['ctl'],$row['tva_id']);
  printf("<tr><TD BGCOLOR=\LIGHTGREEN\" >%s %d</TD><TD>%s</TD><TD>%s</TD></TR>",
	 $set,
	 $row['tva_id'],
	 $row['tva_label'],
	 $row['tva_comment']);
}
echo '</TABLE>';
?>
<input type='button' Value="fermer" onClick='window.close();'>
<?php
html_page_stop();
?>
