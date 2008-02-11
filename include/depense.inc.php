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
require_once('class_acc_ledger.php');
require_once('user_form_ach.php');
require_once('jrn.php');
require_once("class_document.php");
require_once("class_fiche.php");
require_once("check_priv.php");
require_once ('class_pre_op_ach.php');
/*!\brief the purpose off this file encode expense and  to record them
 *
 */
$tag_list='<td class="mtitle"><A class="mtitle" HREF="commercial.php?liste&p_action=depense&sa=list&'.$str_dossier.'">Liste</A></td>';
$tag_list_sel='<td class="selectedcell">Liste</td>';
$tag_unpaid='<td class="mtitle"><A class="mtitle" HREF="commercial.php?liste&p_action=depense&sa=unpaid&'.$str_dossier.'">Non Paye</A></td>';
$tag_unpaid_sel='<td class="selectedcell">Non Paye</td>';

$msg_tva='<i>Si le montant de TVA est &eacute;gal &agrave; 0, il sera automatiquement calcul&eacute;</i>';

// First we show the menu
// If nothing is asked the propose a blank form
// to enter a new invoice
if ( ! isset ($_REQUEST['p_jrn'])) {
  // no journal are selected so we select the first one
  $p_jrn=GetFirstJrnIdForJrnType(dossier::id(),'ACH'); 

} else
{
  $p_jrn=$_REQUEST['p_jrn'];
}
// for the back button
$retour="";
$h_url="";

if ( isset ($_REQUEST['url'])) 
{
  $retour=widget::button_href('Retour',urldecode($_REQUEST['url']));

  $h_url=sprintf('<input type="hidden" name="url" value="%s">',urldecode($_REQUEST['url']));
}

$sub_action=(isset($_REQUEST['sa']))?$_REQUEST['sa']:"";
//--------------------------------------------------------------------------------
// use a predefined operation
//--------------------------------------------------------------------------------
if ( $sub_action=="use_opd" ) {
echo '<div class="u_subtmenu">';
echo ShowMenuJrnUser($gDossier,
		     'ACH',
		     $p_jrn,
		     $tag_list.$tag_unpaid);
echo '</div>';

  $op=new Pre_op_ach($cn);
  $op->set_od_id($_REQUEST['pre_def']);
  $p_post=$op->compute_array();
  echo_debug(__FILE__.':'.__LINE__.'- ','p_post = ',$p_post);
 // Submit button in the form
  $submit='<INPUT TYPE="SUBMIT" NAME="add_item" VALUE="Ajout article">
          <INPUT TYPE="SUBMIT" NAME="view_invoice" VALUE="Enregistrer" ID="SubmitButton">';

  $form=FormAchInput($cn,$_GET['p_jrn'],$User->get_periode(),$p_post,$submit,false,$p_post['nb_item']);
  //  $form=FormAchInput($cn,$p_jrn,$User->get_periode(),$_POST,$submit,false,$nb_item);

  echo '<div class="u_content">';
  echo   $form;
  //--------------------
  // predef op.
  echo '<form method="GET">';
  $op=new Pre_operation($cn);
  $op->p_jrn=$p_jrn;
  $op->od_direct='f';

  $hid=new widget("hidden");
  echo $hid->IOValue("p_action","depense");
  echo dossier::hidden();
  echo $hid->IOValue("p_jrn",$p_jrn);
  echo $hid->IOValue("jrn_type","ACH");
  echo $hid->IOValue("sa","use_opd");
  
  if ($op->count() != 0 )
	echo widget::submit('use_opd','Utilisez une op.pr�d�finie');
  echo $op->show_button();

  echo '</form>';

  echo '</div>';
  exit();
 }

//-----------------------------------------------------
// If a list of depense is asked
// 
if ( $sub_action == "list") 
{
  // show the menu with the list item selected
  echo '<div class="u_subtmenu">';
  echo ShowMenuJrnUser($gDossier,'ACH',0,$tag_list_sel.$tag_unpaid);
  echo '</div>';
  // Ask to update payment
  if ( isset ( $_GET['paid'])) 
    {
      // reset all the paid flag because the checkbox is post only
      // when checked
      foreach ($_GET as $name=>$paid) 
	{
	    list($ad) = sscanf($name,"set_jr_id%d");
 	    if ( $ad == null ) continue;
 	    $sql="update jrn set jr_rapt='' where jr_id=$ad";
 	    $Res=ExecSql($cn,$sql);
	    
	}
	// set a paid flag for the checked box
      foreach ($_GET as $name=>$paid) 
	{
	  list ($id) = sscanf ($name,"rd_paid%d");
	  
	  if ( $id == null ) continue;

	  $paid=($paid=='on')?'paid':'';
	  $sql="update jrn set jr_rapt='$paid' where jr_id=$id";
	  $Res=ExecSql($cn,$sql);
	}
      
      }

  echo '<div class="u_content">';

  

  echo '<form method= "GET" action="commercial.php">';
  echo dossier::hidden();
  $hid=new widget("hidden");
  
  $hid->name="p_action";
  $hid->value="depense";
  echo $hid->IOValue();


  $hid->name="sa";
  $hid->value="list";
  echo $hid->IOValue();



  $w=new widget("select");
  // filter on the current year
  $filter_year=" where p_exercice='".$User->get_exercice()."'";

  $periode_start=make_array($cn,"select p_id,to_char(p_start,'DD-MM-YYYY') from parm_periode $filter_year order by p_start,p_end",1);
  // User is already set User=new User($cn);
  $current=(isset($_GET['p_periode']))?$_GET['p_periode']:$User->get_periode();
  $w->selected=$current;

  echo 'P�riode  '.$w->IOValue("p_periode",$periode_start);
  $qcode=(isset($_GET['qcode']))?$_GET['qcode']:"";
  echo JS_SEARCH_CARD;
  $w=new widget('js_search_only');
  $w->name='qcode';
  $w->value=$qcode;
  $w->label='';
  $w->extra='all';
  $w->table=0;
  $sp= new widget("span");
  echo $w->IOValue();
  echo $sp->IOValue("qcode_label","QuickCode",$qcode);

  echo widget::submit('gl_submit','Rechercher');

  echo '<br>'.$retour;

  // Show list of sell
  // Date - date of payment - Customer - amount
  if ( $current != -1 )
    {
      $filter_per=" and jr_tech_per=".$current;
    }
  else 
    {
      $filter_per=" and jr_tech_per in (select p_id from parm_periode where p_exercice=".
	$User->get_exercice().")";
    }

  $sql=SQL_LIST_ALL_INVOICE." $filter_per  and jr_def_type='ACH'" ;

  $step=$_SESSION['g_pagesize'];
  $page=(isset($_GET['offset']))?$_GET['page']:1;
  $offset=(isset($_GET['offset']))?$_GET['offset']:0;

  $l="";
  // check if qcode contains something
  if ( $qcode != "" )
    {
      // add a condition to filter on the quick code
      $l=" and jr_grpt_id in (select j_grpt from jrnx where j_qcode='$qcode') ";
    }
  /* security  */
  $available_ledger=" and ".$User->get_ledger_sql();

  list($max_line,$list)=ListJrn($cn,0,
				"where jrn_def_type='ACH'   $filter_per  $l $available_ledger"
				,null,$offset,1);
  $bar=jrn_navigation_bar($offset,$max_line,$step,$page);

  echo "<hr> $bar";
  echo $list;
  echo "$bar <hr>";
  if ( $max_line !=0 )
    echo widget::submit('paid','Mise � jour paiement');
  echo '</FORM>';
  echo $retour;

  echo '</div>';

 exit();
} 
//----------------------------------------------------------------------
// Unpaid
if ( $sub_action == 'unpaid' ) {
echo '<div class="u_subtmenu">';
echo ShowMenuJrnUser($gDossier,
		     'ACH',
		     0,
		     $tag_list.$tag_unpaid_sel);
echo '</div>';

  // Ask to update payment
  if ( isset ( $_POST['paid'])) 
    {
      // reset all the paid flag because the checkbox is post only
      // when checked
      foreach ($_POST as $name=>$paid) 
	{
	  list($ad) = sscanf($name,"set_jr_id%d");
	  if ( $ad == null ) continue;
	  $sql="update jrn set jr_rapt='' where jr_id=$ad";
	  $Res=ExecSql($cn,$sql);
	  
	}
      // set a paid flag for the checked box
      foreach ($_POST as $name=>$paid) 
	  {
	    list ($id) = sscanf ($name,"rd_paid%d");
	    
	    if ( $id == null ) continue;
	    $paid=($paid=='on')?'paid':'';
	    $sql="update jrn set jr_rapt='$paid' where jr_id=$id";
	    $Res=ExecSql($cn,$sql);
	  }
      
    }
  /* security put a filter on the ledger */
  $available_ledger=$User->get_ledger_sql();

// Show list of unpaid sell
// Date - date of payment - Customer - amount
  $sql=SQL_LIST_UNPAID_INVOICE_DATE_LIMIT." and $available_ledger ".
    " and jrn_def_type='ACH'";
  $offset=(isset($_GET['offset']))?$_GET['offset']:0;
  $step=$_SESSION['g_pagesize'];
  $page=(isset($_GET['offset']))?$_GET['page']:1;

  list ($max_line,$list)=ListJrn($cn,0,$sql,null,$offset,1);
  //  $bar=jrn_navigation_bar($offset,$max_ligne,$step,$page);


  $sql=SQL_LIST_UNPAID_INVOICE." and jrn_def_type='ACH' and $available_ledger";
  list ($max_line2,$list2)=ListJrn($cn,0,$sql,null,$offset,1);

  // Get the max line
  $m=($max_line2>$max_line)?$max_line2:$max_line;
  $bar2=jrn_navigation_bar($offset,$m,$step,$page);

    echo '<div class="u_redcontent">';
    echo '<h2 class="info"> Echeance d�pass�e </h2>';
    echo '<FORM METHOD="POST">';
	echo dossier::hidden();
    echo $bar2;
    echo $list;


    echo  '<h2 class="info"> Non Pay�e </h2>';
    echo $list2;
    echo $bar2;
    $hid=new widget();
    echo '<hr>';
   if ( $m != 0 )
     echo widget::submit('paid','Mise � jour paiement');
    echo '</form>';

    echo '</div>';
    exit();
 }

//-----------------------------------------------------
echo '<div class="u_subtmenu">';
echo ShowMenuJrnUser($gDossier,
		     'ACH',
		     $p_jrn,
		     $tag_list.$tag_unpaid);
echo '</div>';
//-----------------------------------------------------
// if we request to add an item 
// the $_POST['add_item'] is set
// or if we ask to correct the invoice
if ( isset ($_POST['add_item']) || isset ($_POST["correct"])  ) 
{
 if ( CheckJrn($gDossier,$_SESSION['g_user'],$p_jrn) != 2 )    {
        NoAccess();
        exit -1;
   }

  $nb_item=$_POST['nb_item'];
  if ( isset ($_POST['add_item']))
    $nb_item++;
 // Submit button in the form
  $submit='<INPUT TYPE="SUBMIT" NAME="add_item" VALUE="Ajout article">
          <INPUT TYPE="SUBMIT" NAME="view_invoice" VALUE="Enregistrer" ID="SubmitButton">';

  $form=FormAchInput($cn,$p_jrn,$User->get_periode(),$_POST,$submit,false,$nb_item);
  echo '<div class="u_content">';
  echo $form;
  echo $msg_tva;
  echo JS_CALC_LINE;

  echo '</div>';
  exit();
}
//-----------------------------------------------------
// we want to save the invoice 
//
if ( isset($_POST['save'])) 
{
 if ( CheckJrn($gDossier,$_SESSION['g_user'],$p_jrn) != 2 )    {
        NoAccess();
        exit -1;
   }
  $nb_number=$_POST["nb_item"];
  if ( form_verify_input ($cn,$p_jrn,$User->get_periode(),$_POST,$nb_number) == true ) {
    // we save the expense
    list ($internal,$c)=RecordSell($cn,$_POST,$User,$p_jrn);
    $form=FormAchView($cn,$p_jrn,$User->get_periode(),$_POST,"",$_POST['nb_item'],false);
    echo '<div class="u_content">';
    echo '<h2 class="info"> Op&eacute;ration '.$internal.' enregistr&eacute;</h2>';
    echo $form;
    echo '<hr>';
    echo '</form>';
    echo '<A class="mtitle" href="commercial.php?p_action=depense&p_jrn='.$p_jrn.'&'.dossier::get().'">
    <input type="button" Value="Autre d�pense"></A>';
    exit();
  }
  else 
    {
      $submit='<INPUT TYPE="SUBMIT" name="save" value="Confirmer" onClick="return verify_ca(\'error\');" >';
	  if ( $own->MY_ANALYTIC != "nu" )
		$submit.='<input type="button" value="verifie CA" onClick="verify_ca(\'ok\');">';
      $submit.='<INPUT TYPE="SUBMIT" name="correct" value="Corriger">';
      $form=FormAchView($cn,$p_jrn,$User->get_periode(),$_POST,$submit,$nb_number,true);
      echo '<div class="u_content">';
      echo $form;
      echo '<hr>';
      echo '</form>';
      return;
    }
}
//-----------------------------------------------------
// we show the confirmation screen
// 
if ( isset ($_POST['view_invoice']) ) 
{
   // Check privilege
   if ( CheckJrn($gDossier,$_SESSION['g_user'],$p_jrn) < 1 )    {
        NoAccess();
        exit -1;
   }
  $nb_number=$_POST["nb_item"];
  $submit='<INPUT TYPE="SUBMIT" name="save" value="Confirmer">';
  $submit.='<INPUT TYPE="SUBMIT" name="correct" value="Corriger">';
  if ( form_verify_input ($cn,$p_jrn,$User->get_periode(),$_POST,$nb_number) == true ) {
    // Should use a read only view instead of FormAch
    // where we can check
    $form=FormAchView($cn,$p_jrn,$User->get_periode(),$_POST,$submit,$nb_number);
  } else {
    // if something goes wrong, correct it
    $submit='<INPUT TYPE="SUBMIT" NAME="add_item" VALUE="Ajout article">
                    <INPUT TYPE="SUBMIT" NAME="view_invoice" VALUE="Enregistrer">';
    $form=FormAchInput($cn,$p_jrn,$User->get_periode(),$_POST,$submit, false, $nb_number);
  }
  
  echo '<div class="u_content">';
  echo         $form;
  echo '</div>';
  exit();

}



//-----------------------------------------------------
// By default we add a new invoice
if ( $p_jrn != -1 ) 
{
 if ( CheckJrn($gDossier,$_SESSION['g_user'],$p_jrn) != 2 )    {
        exit -1;
   }

  $jrn=new Acc_Ledger($cn,  $p_jrn);
  echo_debug('depense.inc.php',__LINE__,"Blank form");
 // Submit button in the form
  $submit='<INPUT TYPE="SUBMIT" NAME="add_item" VALUE="Ajout article">
          <INPUT TYPE="SUBMIT" NAME="view_invoice" VALUE="Enregistrer" ID="SubmitButton">';
  // Show an empty form of invoice
  $form=FormAchInput($cn,$p_jrn,$User->get_periode(),null,$submit,false,$jrn->getDefLine());
  echo '<div class="u_content">';
  echo $form;
  echo $msg_tva;
  //--------------------
  // predef op.
  echo '<form method="GET">';
  $op=new Pre_operation($cn);
  $op->p_jrn=$p_jrn;
  $op->od_direct='f';

  $hid=new widget("hidden");
  echo $hid->IOValue("p_action","depense");
  echo dossier::hidden();
  echo $hid->IOValue("p_jrn",$p_jrn);
  echo $hid->IOValue("jrn_type","ACH");
  echo $hid->IOValue("sa","use_opd");
  
  if ($op->count() != 0 )
	echo widget::submit('use_opd','Utilisez une op.pr�d�finie');
  echo $op->show_button();

  echo '</form>';

  echo JS_CALC_LINE;
  echo '</div>';

}
