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
 * \brief concerns the centralisation of the operations
 */
include_once ("ac_common.php");

html_page_start($_SESSION['g_theme']);

require_once('class_dossier.php');
$gDossier=dossier::id();

include_once ("postgres.php");
/* Admin. Dossier */
$rep=DbConnect();
include_once ("class_user.php");
$User=new cl_user($rep);
$User->Check();
include ("check_priv.php");
include_once("preference.php");


include_once ("user_menu.php");
echo ShowMenuCompta("user_advanced.php?".dossier::get());

$cn=DbConnect($gDossier);

include_once("central_inc.php");

echo '<div class="u_subtmenu">';
echo ShowMenuAdvanced("central.php?".dossier::get());
echo '</div>';
$User->AccessRequest($cn,CENTRALIZE);



echo '<DIV CLASS="u_redcontent">';
echo '<H2 CLASS="info"> Centralise </H2><BR>';
if ( isset ($_POST["central"] )) {

  //demande centralise
if ( $_POST["periode"] != "" ) {
    $ret=Centralise($cn,$_POST["periode"]);
    if ($ret==NOERROR) {
      echo '<H2 class="info">La p�riode '.$_POST["periode"].' est centralis�e</H2>';
    } else {
      echo '<H2 class="error">La p�riode '.$_POST["periode"].' n\' a pu �tre centralis�e</H2>';
    }
  } 
}// if ( isset ($_POST["central"] ))

$ret=FormPeriode($cn,0,NOTCENTRALIZED);
if ( $ret != null) {
  echo '<FORM METHOD="POST" action="central.php">';
  echo dossier::hidden();
  echo $ret;
  echo '<INPUT TYPE="SUBMIT" name="central" VALUE="Centralise">';
  echo '</FORM>';
} else {
  echo '<H2 class="info"> Aucune p�riode � centraliser</H2>';
}

echo "</DIV>";
html_page_stop();
?>
