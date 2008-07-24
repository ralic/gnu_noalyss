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
 * \brief file included to manage all the sold operation
 */
require_once("class_acc_ledger_sold.php");
require_once ('check_priv.php');
require_once ('class_pre_op_ven.php');
$p_action=(isset($_REQUEST['p_action']))?$_REQUEST['p_action']:'';
require_once ('check_priv.php');
$gDossier=dossier::id();

$cn=DbConnect(dossier::id());
  //menu = show a list of ledger
$str_dossier=dossier::get();
$array=array( 
	     array('?p_action=ven&sa=n&'.$str_dossier,'Nouvelle vente','Nouvelle vente',1),
	     array('?p_action=ven&sa=l&'.$str_dossier,'Liste ventes','Liste des ventes',2),
	     array('?p_action=ven&sa=lnp&'.$str_dossier,'Liste vente non payées','Liste des ventes non payées',3),
	     array('?p_action=impress&type=jrn&'.$str_dossier,'Impression','Impression')
 	     ,array('?p_action=client&sa=f&'.$str_dossier,'Clients','Solde des clients',5)
	      );

$sa=(isset ($_REQUEST['sa']))?$_REQUEST['sa']:-1;
$def=1;
switch ($sa) {
 case 'n':
   $def=1;
   $use_predef=0;
   break;
 case 'p':
   $def=1;
   $use_predef=1;
   break;
 case 'l':
   $def=2;
   break;
 case 'lnp':
   $def=3;
   break;
 case 'f':
   $def=5;
   break;
 }
if ( $_REQUEST['p_action'] == 'client') $def=5;
echo '<div class="lmenu">';
echo ShowItem($array,'H','mtitle','mtitle',$def);
echo '</div>';
$href=basename($_SERVER['PHP_SELF']);
//----------------------------------------------------------------------
// Encode a new invoice
// empty form for encoding
//----------------------------------------------------------------------
if ( $def==1 || $def == 4 ) {
 // Check privilege
  if ( isset($_REQUEST['p_jrn']) && 
	     CheckJrn($gDossier,$_SESSION['g_user'],$_POST['p_jrn']) != 2 )    
    {
       NoAccess();
       exit -1;
    }

  /* if a new invoice is encoded, we display a form for confirmation */
  if ( isset ($_POST['view_invoice'] ) ) {
    $Ledger=new Acc_Ledger_Sold($cn,$_POST['p_jrn']);
    try { 
      $Ledger->verify($_POST);
    } catch (AcException $e){
      echo '<script> alert("'.$e->getMessage().'");</script>';
      $correct=1;
    }
    // if correct is not set it means it is correct
    if ( ! isset($correct)) {
      echo '<div class="content">';
      
      echo '<form action="'.$href.'"  enctype="multipart/form-data" method="post">';
      echo widget::hidden('sa','n');
      echo widget::hidden('p_action','ven');
      echo dossier::hidden();
      echo $Ledger->confirm($_POST );
      
      $chk=new widget('checkbox');
      $chk->selected=false;
      echo "Sauvez cette op&eacute;ration comme modèle ?";
      echo $chk->IOValue('opd_save');
      echo '<hr>';      
      echo widget::submit("record","Enregistrement",'onClick="return verify_ca(\'error\');"');
      echo widget::submit('correct',"Corriger");
      echo '</form>';
      
      echo '</div>';
      exit();
    }
  }
  //------------------------------
  /* Record the invoice */
  //------------------------------

  if ( isset($_POST['record']) ){
 // Check privilege
  if ( CheckJrn($gDossier,$_SESSION['g_user'],$_REQUEST['p_jrn']) != 2 )    {
       NoAccess();
       exit -1;
  }

    $Ledger=new Acc_Ledger_Sold($cn,$_POST['p_jrn']);
    try { 
      $Ledger->verify($_POST);
    } catch (AcException $e){
      echo '<script> alert("'.$e->getMessage().'");</script>';
      $correct=1;
    }
    if ( ! isset($correct)) {
      echo '<div class="content">';
      $Ledger=new Acc_Ledger_Sold($cn,$_POST['p_jrn']);
      $internal=$Ledger->insert($_POST);
      
      
      /* Save the predefined operation */
      if ( isset($_POST['opd_save'])) {
	$opd=new Pre_op_ven($cn);
	$opd->get_post();
	$opd->save();
      }
      
      /* Show button  */
      echo "<h2 class=\"info\">Opération sauvée $internal </h2>";
      echo widget::button_href('Nouvelle vente',$href.'?p_action=ven&sa=n&'.dossier::get());
      echo '</div>';
      exit();
    }
  }
  //  ------------------------------
  /* Display a blank form or a form with predef operation */
  //  ------------------------------

  echo '<div class="content">';
  echo JS_PROTOTYPE;

  echo "<FORM NAME=\"form_detail\" METHOD=\"POST\">";

  $array=(isset($_POST['correct'])||isset ($correct))?$_POST:null;
  $Ledger=new Acc_Ledger_Sold($cn,0);
 //
 // pre defined operation
 //

  if ( !isset($_REQUEST ['p_jrn'])) {
    $def_ledger=$Ledger->get_first('ven');
    $Ledger->id=$def_ledger['jrn_def_id'];
  } else 
    $Ledger->id=$_REQUEST ['p_jrn'];




  /* request for a predefined operation */
  if ( isset($use_predef) && $use_predef == 1 && isset($_REQUEST['pre_def']) ) {
    // used a predefined operation
    //
    $op=new Pre_op_ven($cn);
    $op->set_od_id($_REQUEST['pre_def']);
    $p_post=$op->compute_array();
    $Ledger->id=$_REQUEST ['p_jrn_predef'];

    echo $Ledger->display_form($p_post);
    echo '<script>';
    echo 'compute_all_sold();';
    echo '</script>';
  }
  else {
    echo widget::hidden("p_action","ven");
    echo widget::hidden("sa","p");
    echo $Ledger->display_form($array);
  }
  echo "</FORM>";

  echo '<form method="GET" action="'.$href.'">';
  echo widget::hidden("sa","p");
  echo widget::hidden("p_action","ven");
  echo dossier::hidden();
  echo widget::hidden('p_jrn_predef',$Ledger->id);
  $op=new Pre_op_ven($cn);
  $op->set('ledger',$Ledger->id);
  $op->set('ledger_type',"VEN");
  $op->set('direct','f');
  echo $op->form_get();
  echo '</form>';
  echo '<form onsubmit="cal();return false;" name="calc_line" method="get">';
  echo JS_CALC_LINE;
  echo '</form>';
  echo '</div>';
  exit();
}
//-------------------------------------------------------------------------------
// Listing
//--------------------------------------------------------------------------------
if ( $def == 2 ) {
  echo '<div class="content">';
 // Check privilege
  if ( isset($_REQUEST['p_jrn']) && 
       CheckJrn($gDossier,$_SESSION['g_user'],$_REQUEST['p_jrn']) ==0 )    {
       NoAccess();
       exit -1;
  }

  $Ledger=new Acc_Ledger_Sold($cn,0);
  if ( !isset($_REQUEST['p_jrn'])) {
    $def_ledger=$Ledger->get_first('ven');
    $Ledger->id=$def_ledger['jrn_def_id'];
  } else 
    $Ledger->id=$_REQUEST['p_jrn'];

  //------------------------------
  // UPdate the payment
  //------------------------------
  if ( isset ( $_GET ['paid']))    {
    $Ledger->update_paid($_GET);
  }
  


  echo '<form method="GET" action="'.$href.'">';
  echo widget::hidden("sa","l");
  echo widget::hidden("p_action","ven");
  echo dossier::hidden();
  $Ledger->show_ledger();
  echo '</form>';

  echo '</div>';
  exit();

}
//---------------------------------------------------------------------------
// Listing unpaid
//---------------------------------------------------------------------------
if ( $def==3 ) {
 // Check privilege
  if ( isset($_REQUEST['p_jrn']) && 
       CheckJrn($gDossier,$_SESSION['g_user'],$_REQUEST['p_jrn']) ==0 )    {
       NoAccess();
       exit -1;
  }

  $Ledger=new Acc_Ledger_Sold($cn,0);
  if ( !isset($_REQUEST['p_jrn'])) {
    $def_ledger=$Ledger->get_first('ven');
    $Ledger->id=$def_ledger['jrn_def_id'];
  } else 
    $Ledger->id=$_REQUEST['p_jrn'];

    // Ask to update payment
  if ( isset ( $_GET['paid']))      {
    $Ledger->update_paid($_GET);
  }
  echo '<div class="content">';

  echo '<FORM METHOD="GET" action="'.$href.'">';
  $wLedger=$Ledger->select_ledger('VEN',2);
  $wLedger->javascript="onChange=submit()";
  echo "Journal ".$wLedger->IOValue();
  echo widget::submit ('search','Recherche');
  echo widget::hidden("p_action","ven");
  echo widget::hidden('sa','lnp');
  echo dossier::hidden();  

  $Ledger->show_unpaid();
  echo '</FORM>';
  echo '</div>';
  exit();

}
if ( $p_action == 'client') {
  require_once ('client.inc.php');
}
