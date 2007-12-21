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
 * \brief create, modify parameter ledger
 */
require_once('class_dossier.php');
$gDossier=dossier::id();

include_once ("ac_common.php");
html_page_start($_SESSION['g_theme']);

include_once ("postgres.php");
/* Admin. Dossier */
$rep=DbConnect();
include_once ("class_user.php");
$User=new cl_user($rep);
$User->Check();

include_once ("check_priv.php");

include_once ("user_menu.php");

echo '<div class="u_tmenu">';
echo ShowMenuCompta("user_advanced.php?".dossier::get());
echo '</div>';


$cn=DbConnect($gDossier);



echo '<div class="u_subtmenu">';
echo ShowMenuAdvanced("jrn_update.php?".dossier::get());
echo '</div>';
$User->can_request($cn,GJRN);


echo '<div class="lmenu">';
MenuJrn($gDossier);
echo '</div>';
html_page_stop();
?>
