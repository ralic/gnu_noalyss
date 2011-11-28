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
 * \brief Page for the personal preference (theme, password,...)
 */

include_once ("ac_common.php");
require_once('class_database.php');
/* Admin. Dossier */
$Rep=new Database();
include_once ("class_user.php");
require_once('class_dossier.php');

/* if the dossier is not set then we have to connect to the
 * account_repository database
 */

if ( isset ($_REQUEST['gDossier']))
{
    $cn=new Database($_REQUEST['gDossier']);
}
else
{
    $cn=new Database();
}


$User=new User($cn);
$User->Check();

// Met a jour le theme utilisateur (style)
if ( isset ( $_POST['style_user']) )
{
    $User->update_global_pref('THEME',$_POST['style_user']);
    $_SESSION['g_theme']=$_POST['style_user'];

}
// Update the language
if ( isset($_POST ['lang']))
{
    $User->update_global_pref('LANG',$_POST['lang']);
    $_SESSION['g_lang']=$_POST['lang'];
}

html_page_start($_SESSION['g_theme']);

// Met a jour le pagesize
if ( isset ( $_POST['p_size']) )
{
    $User->update_global_pref('PAGESIZE',$_POST['p_size']);
    $_SESSION['g_pagesize']=$_POST['p_size'];

}

// Topmenu
//   not used for the moment
//---------------------------------------------------------------------------
// Here we put all the changes
//---------------------------------------------------------------------------

if ( isset ($_POST['val']) )
{
    // Change the mini report
    if ( isset ($_POST['minirap']))   $User->set_mini_report($_POST['minirap']);
}
// show the top menu depending of the use_style
// comta style

include_once ("user_menu.php");
echo "<h2 class=\"info\"> Pr&eacute;f&eacute;rences</h2>";

require_once("pref.inc.php");
html_page_stop();
?>
