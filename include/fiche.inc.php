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
/* $Revision$ */
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
/*! \file
 * \brief module to manage the card (removing, listing, creating, modify attribut)
 */
include_once ("ac_common.php");
require_once("class_itext.php");
require_once("class_ihidden.php");
require_once('class_fiche.php');
require_once('class_database.php');
include_once ("user_menu.php");
require_once('class_dossier.php');
$gDossier=dossier::id();
$str_dossier=dossier::get();
echo JS_SEARCH_POSTE;
echo JS_AJAX_FICHE;

if ( !isset($sessid)) 
  {
    $sessid=$_REQUEST["PHPSESSID"];
  } 
$search='<INPUT TYPE="BUTTON" VALUE="Cherche" OnClick="SearchPoste(\''.$sessid."',".dossier::id().",'class_base','')\">";



$cn=new Database($gDossier);
echo_debug(__FILE__,__LINE__,"Connected");
// Security check
$write=$User->check_action(FICADD);
if ($write == 0 ){
  /* Cannot Access */
  NoAccess();
}

function ShowRecherche() {
  echo '<DIV class="u_redcontent">';
  echo '<form method="GET" action="?">';
  echo dossier::hidden();
  $w=new IText();
  $search_text=(isset($_REQUEST['search_text']))?$_REQUEST['search_text']:"";
  $h=new IHidden();
  echo $h->input('p_action','fiche');
  echo $h->input('action','search');
  echo "Recherche :".$w->input('search_text',$search_text);
  echo HtmlInput::submit('submit','Rechercher');
  echo '</form>';
  echo '</div>';
}
function ShowFicheDefInput($p_fiche_def)
{
  $r="";
  // Save the label
  
  $p_fiche_def->Get();
  $p_fiche_def->GetAttribut();
  if (isset ($_REQUEST['label']) )
    $p_fiche_def->SaveLabel($_REQUEST['label']);
  $r.= '<H2 class="info">'.h($p_fiche_def->label).'</H2>';
  
  $r.= '<FORM action="?p_action=fiche" method="POST">';
  $r.=dossier::hidden();
  $r.=HtmlInput::hidden("fd_id",$p_fiche_def->id);
  
  $r.= $p_fiche_def->DisplayAttribut("remove");
  $r.= HtmlInput::submit('add_line','Ajoutez cet élément');
  $r.= HtmlInput::submit("save_line","Sauvez");
  $r.=HtmlInput::submit('remove_cat','Effacer cette catégorie','onclick="return confirm(\'Vous confirmez ?\')"');
  // if there is nothing to remove then hide the button
  if ( strpos ($r,"chk_remove") != 0 ) {
    $r.=HtmlInput::submit('remove_line',"Enleve les &eacute;l&eacute;ments coch&eacute;s" );
  }
  $r.= "</form>";
  $r.=" <p class=\"notice\"> Attention : il n'y aura pas de demande de confirmation pour enlèver les 
attributs sélectionnés. Il ne sera pas possible de revenir en arrière</p>";

  return $r;
}

$recherche=true;
// Creation of a new model of card
// in the database
if ( isset($_POST['add_modele'])    )
  {
    $User->can_request(FICCAT);
    // insert the model of card in database
    $fiche_def=new fiche_def($cn);
    $fiche_def->Add($_POST);
  }
$r="";

if ( isset ($_POST['remove_cat'] )  ) {
    $User->can_request(FICCAT);

  $fd_id=new fiche_def($cn,$_POST['fd_id']);
  $remains=$fd_id->remove();
  if ( $remains != 0 ) 
    /* some card are not removed because it is used */
    alert('Impossible d\'enlever cette catégorie, certaines fiches sont encore utilisées'."\n".
	  'Les fiches non utilisées ont cependant été effacées');
}
// Add a line in the card model
if ( isset ($_POST["add_line"])  ) {
    $User->can_request(FIC);

  $r= '<DIV class="u_redcontent">';
  if ( $write ==0)  
    $r.= "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche_def=new fiche_def($cn,$_REQUEST['fd_id']);
      // Insert Line
      $fiche_def->InsertAttribut($_REQUEST['ad_id']);
		
      $r.=ShowFicheDefInput($fiche_def);

    }
  $r.= '</DIV>';
  $recherche=false;
}
/* ------------------------------------------------- */
/* SAVE ORDER */
/* ------------------------------------------------- */

if ( isset($_POST['save_line'])) {
  $User->can_request(FICCAT);
  $fiche_def=new fiche_def($cn,$_REQUEST['fd_id']);
  $fiche_def->save_order($_POST);
  $r= '<DIV class="u_redcontent">';
  if ( $write ==0)  
    $r.= "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche_def=new fiche_def($cn,$_REQUEST['fd_id']);
      // Insert Line
      $r.=ShowFicheDefInput($fiche_def);

    }
  $r.= '</DIV>';
  $recherche=false;

}
// Remove lines from a card model
if ( isset ($_POST['remove_line'])   ) 
  {
    $User->can_request(FICCAT);
    $r= '<DIV class="u_redcontent">';
    if ( $write ==0)  
      $r.= "<h2 class=\"error\"> Pas d'accès </h2>";
    else
      {
	$fiche_def=new fiche_def($cn,$_REQUEST['fd_id']);
	// Insert Line
	// demander confirmation 

	$fiche_def->RemoveAttribut($_REQUEST['chk_remove']);
	$r.=ShowFicheDefInput($fiche_def);

      }
    $r.= '</DIV>';
    $recherche=false;
  }
// Change the name of the card  model
if ( isset ($_POST["change_name"] )   ) {
  $User->can_request(FICCAT);
  $r= '<DIV class="u_redcontent">';
  if ( $write ==0)  
    $r.= "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche_def=new fiche_def($cn,$_REQUEST['fd_id']);
      $r.=ShowFicheDefInput($fiche_def);
    }
  $r.= '</DIV>';
  $recherche=false;
}

ShowMenuFiche($gDossier);
echo $r;

//------------------------------------------------------------------------------
// Get action
if ( isset ( $_GET["action"]) ) {
  $action=$_GET["action"];
  // View the details of the selected cat. of cards
  if ( isset ($_GET["fiche"]) && $action=="vue" 
       && ! isset ($_POST['add_fiche']) 
       && ! isset ($_POST['update_fiche'])
       && ! isset ($_POST['delete'])) {
     $User->can_request(FICADD);

    echo '<DIV class="u_redcontent">';
    $fiche_def=new fiche_def($cn,$_GET['fiche']);
    $fiche_def->myList();

    echo '</DIV>';

    $recherche=false;
  }
  //_________________________________________________________________________
  // Display the detail of a card
  if ($action== "detail" ) {
    echo '<DIV class="u_redcontent">';
    $t=false;
    if ( $User->check_action(FICADD)==0) 
      { 
	echo '<H2 class="info"> Vos changements ne seront pas sauvés</h2>';
	$t=true;
      }
    $str="&".dossier::get();
    $fiche=new fiche($cn,$_GET["fiche_id"]);
    $fiche->get_categorie();
    $fiche_def=new fiche_def($cn,$fiche->fd_id);
    $fiche_def->Get();
    echo '<h2 class="info">'.$fiche_def->label.'</h2>';

    if ( $_SESSION['g_pagesize'] != -1 ){
      // retrieve value
      // with offet &offset=15&step=15&page=2&size=15
      if ( isset($_GET['offset']) && $_SESSION['g_pagesize'] != -1) {
	$str=sprintf("&offset=%s&step=%s&page=%s&size=%s",
		     $_GET['offset'],
		     $_GET['step'],
		     $_GET['page'],
		     $_GET['size']);
      }
		       

    }
    if ( $write != 0 )
      echo '<form method="post" action="?p_action=fiche&action=vue&fiche='.$_GET['fiche'].$str.'">';
    echo dossier::hidden();
    echo $fiche->Display($t);
    echo HtmlInput::hidden("f_id",$_GET['fiche_id']);
    if ( $write != 0 ) {
      echo HtmlInput::submit("update_fiche","Mise &agrave; jour");
      echo HtmlInput::submit("delete" ,"Effacer cette fiche");
    }
    $str="";

    echo '<a class="mtitle" href="?p_action=fiche&action=vue&'.$str_dossier.'&fiche='.$fiche->fiche_def.$str.
      '"><input type="button" value="Annuler"></A>';
    if ( $write != 0 ) echo '</form>';
    echo '</DIV>';
    $recherche=false;
  }
  //_________________________________________________________________________
  // Display the form where you can enter
  // the property of the card model
  if ($action == "add_modele" ) {
    $User->can_request(FICCAT);
    echo '<DIV class="u_redcontent">';
    echo '<form method="post">';
    $oFiche_Def=new fiche_def($cn);	
    echo HtmlInput::hidden("p_action","fiche");
    echo dossier::hidden();
    echo $oFiche_Def->input($search); //    CreateCategory($cn,$search);
    echo HtmlInput::submit("add_modele" ,"Sauve");
    
    echo '</form>';
    echo '</DIV>';
    $recherche=false;
  }
  //_________________________________________________________________________
  // Modify a card Model
  if ($action == "modifier" ) {
    $User->can_request(FICCAT);
    echo '<DIV class="u_redcontent">';
    $fiche_def=new fiche_def($cn,$_GET['fiche']);
    
    echo ShowFicheDefInput($fiche_def);
    echo '</DIV>';
    $recherche=false;
  }
  //_________________________________________________________________________
  // Search a card
  if ( $action == "search" ) 
    {
      ShowRecherche();
      $sql="select distinct f_id,fd_id from fiche join jnt_fic_att_value using (f_id) 
            join attr_value using (jft_id) where
            upper(av_text) like upper('%".FormatString($_GET["search_text"])."%') order by f_id";

      $all=$cn->get_array($sql);
      // test on the size
      //
      if ( sizeof($all) != 0 )
	{
	  echo '<DIV class="u_redcontent">';
	  echo "Résultat : ".sizeof($all).'éléments trouvés <br>';
	  foreach ($all as $f_id){
	    $fiche=new fiche($cn,$f_id['f_id']);
	    echo '<A class="mtitle" href="?p_action=fiche&'.$str_dossier.'&action=detail&fiche_id='.$f_id['f_id'].
	      '&fiche='.$f_id['fd_id'].'">'.
	      $fiche->getName().'</A><br>';
	  }
	  echo '</div>';
	}
      else 
	{
	  echo '<DIV class="u_redcontent">';
	  echo "Aucun résultat trouvé";
		  
	  echo '</div>';
		  
	}
    }
  $recherche=false;
}
// Display a blank  card from the selected category
if ( isset ($_POST["fiche"]) && isset ($_POST["add"] ) ) {
  $User->can_request(FICADD);

  echo '<DIV class="u_redcontent">';
  if ( $write ==0)  
    echo "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche_def=new fiche_def($cn,$_POST['fiche']);
      $fiche_def->Get();
      echo '<h2 class="info">'.$fiche_def->label.'</h2>';
      $url=$_SERVER['REQUEST_URI'];
      $fiche=new fiche($cn,0);
		
      echo '<form method="post" action="'.$url.'&fiche='.$_POST['fiche'].'">';
      echo dossier::hidden();
      echo $fiche->blank($_POST['fiche']);
      echo HtmlInput("add_fiche","Ajout");
      echo '<a class="mtitle" href="'.$url.'&fiche='.$_POST['fiche'].'&'.$str_dossier.'">'.
	'<input type="button" value="Annuler"></A>';
		

      echo '</form>';
    }
  echo '</DIV>';
  $recherche=false;
  exit();
}
//------------------------------------------------------------------------------
// delete a card
if (isset($_POST['delete']) ) {
  $User->can_request(FIC);
  ShowRecherche();
  echo '<DIV class="u_redcontent">';
  if ( $write ==0)  
    echo "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche=new fiche($cn,$_POST["f_id"]);
      $fiche->remove();
    }
  $fiche_def=new fiche_def($cn,$_GET['fiche']);
  $fiche_def->myList();

  echo "</DIV>";
  exit();
}  
//------------------------------------------------------------------------------
// Add the data (attribute) of the card
if ( isset ($_POST["add_fiche"]) ) {
  $User->can_request(FICADD);
  if ( $write ==0)  {  
    echo '<DIV class="u_redcontent">';
    echo "<h2 class=\"error\"> Pas d'accès </h2>";
  }
  else
    {
      ShowRecherche();
      echo '<DIV class="u_redcontent">';
      $fiche=new fiche($cn);
      $fiche->Save($_REQUEST['fiche']);
      $fiche_def=new fiche_def($cn,$_GET['fiche']);
      $fiche_def->myList();

	
    }
  echo '</DIV>';
  $recherche=false;
}
//------------------------------------------------------------------------------
// Update a card
if ( isset ($_POST["update_fiche"])  ) {
  $User->can_request(FIC);
  echo '<DIV class="u_redcontent">';
  if ( $write ==0)  
    echo "<h2 class=\"error\"> Pas d'accès </h2>";
  else
    {
      $fiche=new fiche($cn,$_POST['f_id']);
      $fiche->Save();

    }
  $fiche_def=new fiche_def($cn,$_GET['fiche']);
  $fiche_def->myList();





  echo '</DIV>';
  $recherche=false;
}
//--Search menu
if ( $recherche==true) {
  ShowRecherche();
}
html_page_stop();
?>
