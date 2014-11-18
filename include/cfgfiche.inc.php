<?php
/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu
/*! \file
 * \brief module to manage the card (removing, listing, creating, modify attribut)
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
include_once ("ac_common.php");
require_once("class_itext.php");
require_once("class_ihidden.php");
require_once('class_fiche.php');
require_once('class_database.php');
include_once ("user_menu.php");
require_once('class_dossier.php');
require_once 'class_sort_table.php';
require_once 'class_fiche_def.php';
require_once 'class_tool_uos.php';

$retour=HtmlInput::button_anchor("Retour à la liste", HtmlInput::get_to_string(array("gDossier","ac")));

/*******************************************************************************************/
// Add an attribut
/*******************************************************************************************/
if ( isset($_POST['add_line']))
{
	 $fiche_def=new Fiche_Def($cn,$_REQUEST['fd_id']);
     $fiche_def->InsertAttribut($_REQUEST['ad_id']);
     echo $fiche_def->input_detail();
	 echo $retour;
	 return;
}
/*******************************************************************************************/
// Remove an attribut
/*******************************************************************************************/
if ( isset ($_POST['remove_line']))
{
	$fiche_def=new Fiche_Def($cn,$_REQUEST['fd_id']);
	$fiche_def=new Fiche_Def($cn,$_REQUEST['fd_id']);
	$fiche_def->RemoveAttribut($_REQUEST['chk_remove']);
    echo $fiche_def->input_detail();
	echo $retour;
	return;
}
/*******************************************************************************************/
// Try to remove a category
/*******************************************************************************************/
if ( isset ($_POST['remove_cat']))
{
    $post_id=HtmlInput::default_value_post('fd_id', 0);
    if ($post_id == 0 || $post_id >= 500000)
    {
        alert(_('Impossible d\'enlever cette catégorie'));
    } else {
        $fd_id=new Fiche_Def($cn,$post_id);
        $remains=$fd_id->remove();
        if ( $remains != 0 ) {
            /* some card are not removed because it is used */
            alert(_('Impossible d\'enlever cette catégorie, certaines fiches sont encore utilisées'."\n".
                  'Les fiches non utilisées ont cependant été effacées'));
        }
    }
}
/*******************************************************************************************/
// Change some basis info
/*******************************************************************************************/
if ( isset ($_POST['change_name']))
{
    if (isset ($_REQUEST['label']) )
    {
	$fiche_def=new Fiche_Def($cn,$_REQUEST['fd_id']);
        $fiche_def->SaveLabel($_REQUEST['label']);
        if ( isset($_REQUEST['create']))
        {
            $fiche_def->set_autocreate(true);
        }
        else
        {
            $fiche_def->set_autocreate(false);
        }
        $fiche_def->save_class_base($_REQUEST['class_base']);
	$fiche_def->save_description($_REQUEST['fd_description']);

    }
	echo $fiche_def->input_detail();
	echo $retour;
	return;
}
/*******************************************************************************************/
// Save order of the attributes
/*******************************************************************************************/
if ( isset($_POST['save_line']))
{
    $fiche_def=new Fiche_Def($cn,$_REQUEST['fd_id']);
    $fiche_def->save_order($_POST);
	echo $fiche_def->input_detail();
	echo $retour;
	return;
}
/*******************************************************************************************/
// Save a new category of card
/*******************************************************************************************/
if ( isset($_POST['add_modele']))
{
	$single=new Tool_Uos("dup");
	if ($single->get_count()==0)
	{
		$single->save();
		$fiche_def=new Fiche_Def($cn);
		if ( $fiche_def->Add($_POST) == 0 )
		{
			echo $fiche_def->input_detail();
			echo $retour;
			return;
		}
		else
		{
			$fiche_def->input_new();
			echo $retour;
			return;
		}
	}
	else
	{
		alert('Doublon');
	}
}
$fiche_def=new Fiche_def($cn);

$fiche_def->Display();
$dossier=Dossier::id();
?>
