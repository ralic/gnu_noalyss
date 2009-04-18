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

/*!\file
 * \brief this file is to be included to handle the financial ledger
 */
require_once ('class_acc_ledger_fin.php');
$gDossier=dossier::id();
$p_action=(isset ($_REQUEST['p_action']))?$_REQUEST['p_action']:'';

$cn=DbConnect(dossier::id());
$menu_action="?p_action=bank&".dossier::get();
$menu=array(
	    array($menu_action.'&sa=n','Nouvel extrait','Encodage d\'un nouvel extrait',1),
	    array($menu_action.'&sa=l','Liste','Liste opération bancaire',2),
	    array($menu_action.'&sa=s','Solde','Solde des comptes',3),
	    array('?p_action=impress&type=jrn&'.dossier::get(),'Impression','Impression')
	    );
$sa=(isset($_REQUEST['sa']))?$_REQUEST['sa']:-1;

switch ($sa) {
 case 'n':
   $def=1;
   break;
case 'l':
  $def=2;
  break;
case 's':
  $def=3;
  break;
default:
  $def=1;
}
echo '<div class="lmenu">';
echo ShowItem($menu,'H','mtitle','mtitle',$def);
echo '</div>';

$href=basename($_SERVER['PHP_SELF']);
$Ledger=new Acc_Ledger_Fin($cn,0);

//--------------------------------------------------------------------------------
// Encode a new financial operation
//--------------------------------------------------------------------------------
if ( $def == 1 ) {

  $href=basename($_SERVER['PHP_SELF']);

  if ( isset($_REQUEST['p_jrn']))
    $Ledger->id=$_REQUEST['p_jrn'];
  else {
    $def_ledger=$Ledger->get_first('fin');
    $Ledger->id=$def_ledger['jrn_def_id'];
  }
  $jrn_priv=$User->get_ledger_access($Ledger->id);
  // Check privilege
  if ( isset($_REQUEST['p_jrn']) && ( $jrn_priv == 'X')) {
       NoAccess();
       exit -1;
  }

  //----------------------------------------
  // Confirm the operations
  //----------------------------------------
  if ( isset($_POST['save'])) {
    try {
      $Ledger->verify($_POST);
    } catch (Exception $e) {
      alert($e->getMessage());
      $correct=1;
    }
    if ( ! isset ($correct )) {
      echo '<div class="content">';
      echo '<form name="form_detail" enctype="multipart/form-data" ACTION="'.$href.'" METHOD="POST">';
      echo HtmlInput::hidden('p_action','bank');
      echo $Ledger->confirm($_POST);
      echo HtmlInput::submit('confirm','Confirmer');
      echo HtmlInput::submit('correct','Corriger');

      echo '</form>';
      echo '</div>';
      exit();
    }
  }
  //----------------------------------------
  // Confirm and save  the operations
  // into the database
  //----------------------------------------
  if ( isset($_POST['confirm'])) {
    try {
      $Ledger->verify($_POST);
    } catch (Exception $e) {
      alert($e->getMessage());
      $correct=1;
    }
    if ( !isset($correct)) {
      echo '<div class="content">';
      $a= $Ledger->insert($_POST);
      echo '<h2 class="info">Opération  sauvée </h2>';      
      echo HtmlInput::button_href('Nouvelle extrait',$href.'?p_action=bank&sa=n&'.dossier::get());
      echo $a;
      echo '</div>';
      exit();
    }
  }    
  //----------------------------------------
  // Correct the operations
  //----------------------------------------
  if ( isset($_POST['correct'])) {
    $correct=1;
  }
  //----------------------------------------
  // Blank form
  //----------------------------------------
  echo JS_PROTOTYPE;
  echo '<div class="content">';

  
  echo '<form name="form_detail" enctype="multipart/form-data" ACTION="'.$href.'" METHOD="POST">';
  echo HtmlInput::hidden('p_action','bank');
  echo HtmlInput::hidden('sa','n');
  $array=( isset($correct))?$_POST:null;
  // show select ledger
  echo $Ledger->input($array);
  echo HtmlInput::button('add_item','Ajout article',   ' onClick="ledger_fin_add_row()"');
  echo HtmlInput::submit('save','Sauve');
  echo HtmlInput::reset('Effacer ');


  echo '</form>';
  echo JS_CALC_LINE;
  echo '</div>';
  exit();
}
//--------------------------------------------------------------------------------
// Show the listing
//--------------------------------------------------------------------------------
if ( $def == 2) {


  echo '<div class="content">';
  if ( isset($_REQUEST['p_jrn'])) {
    $Ledger->id=$_REQUEST['p_jrn'];
    $jrn_priv=$User->get_ledger_access($Ledger->id);
    
    // Check privilege
    if ( isset($_REQUEST['p_jrn']) && $jrn_priv=='X') {
      NoAccess();
      exit -1;
    }
  }
  else {
    $Ledger->id=-1;
  }

  $Ledger->show_ledger();
  echo '</div>';
  exit();
}
//--------------------------------------------------------------------------------
// Show the saldo
//--------------------------------------------------------------------------------
if ( $def==3) {
  require_once ('class_acc_parm_code.php');
  echo '<div class="content">';
  $fiche=new fiche_def($cn);
  $array=$fiche->get_by_category(FICHE_TYPE_FIN);

  echo '<div class="content">';

  echo '<table width="50%" class="result">';
  // Filter the saldo
  //  on the current year
  $filter_year="  j_tech_per in (select p_id from parm_periode where  p_exercice='".$User->get_exercice()."')";
  // for highligting tje line
  $idx=0;
  // for each account
  for ( $i = 0; $i < count($array);$i++) {
    // get the saldo
    $m=$array[$i]->get_solde_detail($filter_year);

    $solde=$m['debit']-$m['credit'];

    // print the result if the saldo is not equal to 0
    if ( $m['debit'] != 0.0 || $m['credit'] != 0.0) {
      if ( $idx%2 != 0 ) 
	$odd="odd";
      else
	$odd="";
      $idx++;
      echo "<tr class=\"$odd\">";
      echo "<TD >".
	$array[$i]->strAttribut(ATTR_DEF_QUICKCODE).
	"</TD>";

      echo "<TD >".
	$array[$i]->strAttribut(ATTR_DEF_NAME).
	"</TD>".
	"<TD align=\"right\">".
	$solde.
	"</TD>"."</TR>";
    }
  }// for
  echo "</table>";
  echo "</div>";
  exit();
}

