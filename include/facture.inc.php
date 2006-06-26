<?
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
require_once('class_jrn.php');
require_once('user_form_ven.php');
require_once('jrn.php');
require_once("class_document.php");
require_once("class_fiche.php");
/*!\file
 * \brief the purpose off this file is to create invoices, to record them and to generate
 *        them, and of course to save them into the database
 *
 */


// First we show the menu
// If nothing is asked the propose a blank form
// to enter a new invoice
if ( ! isset ($_REQUEST['p_jrn'])) {
  // no journal are selected so we select the first one
  $p_jrn=GetFirstJrnIdForJrnType($_SESSION['g_dossier'],'VEN'); 
} else
{
  $p_jrn=$_REQUEST['p_jrn'];
}
// for the back button
$retour="";
$h_url="";

if ( isset ($_REQUEST['url'])) 
{
  $retour=sprintf('<A class="mtitle" HREF="%s"><input type="button" value="Retour"></A>',urldecode($_REQUEST['url']));
  $h_url=sprintf('<input type="hidden" name="url" value="%s">',urldecode($_REQUEST['url']));
}

$sub_action=(isset($_REQUEST['sa']))?$_REQUEST['sa']:"";
//-----------------------------------------------------
// If a list of invoice is asked
// 
if ( $sub_action == "list") 
{

  // show the menu with the list item selected
  echo '<div class="u_subtmenu">';
  echo ShowMenuJrnUser($_SESSION['g_dossier'],'VEN',0,'<td class="selectedcell">Liste</td>');
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
	  //	  echo "Mise � jour $id";
	  $paid=($paid=='on')?'paid':'';
	  $sql="update jrn set jr_rapt='$paid' where jr_id=$id";
	  $Res=ExecSql($cn,$sql);
	}
      
      }
  echo $retour;
  echo '<div class="u_redcontent">';

  

  echo '<form method= "GET" action="commercial.php">';

  $hid=new widget("hidden");
  
  $hid->name="p_action";
  $hid->value="facture";
  echo $hid->IOValue();


  $hid->name="sa";
  $hid->value="list";
  echo $hid->IOValue();



  $w=new widget("select");

  $periode_start=make_array($cn,"select p_id,to_char(p_start,'DD-MM-YYYY') from parm_periode order by p_id");
  // User is already set User=new cl_user($cn);
  $current=(isset($_GET['p_periode']))?$_GET['p_periode']:$User->GetPeriode();
  $w->selected=$current;

  echo 'P�riode  '.$w->IOValue("p_periode",$periode_start).$w->Submit('gl_submit','Valider');
  $qcode=(isset($_GET['qcode']))?$_GET['qcode']:"";
  printf ('<span>Tiers QuickCode: <input type="text" name="qcode" value="%s"></span>',
	   $qcode);

  // Show list of sell
  // Date - date of payment - Customer - amount
  $sql=SQL_LIST_ALL_INVOICE." and jr_tech_per=".$current." and jr_def_type='VEN'" ;
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

  list($max_line,$list)=ListJrn($cn,0,"where jrn_def_type='VEN' and jr_tech_per=$current $l "
				,null,$offset,1);
  $bar=jrn_navigation_bar($offset,$max_line,$step,$page);

  echo "<hr> $bar";
  echo $list;
  echo "$bar <hr>";
  if ( $max_line !=0 )
    echo $hid->Submit('paid','Mise � jour paiement');
  echo '</FORM>';
  echo $retour;

  echo '</div>';

 exit();
} 
//-----------------------------------------------------
echo '<div class="u_subtmenu">';
echo ShowMenuJrnUser($_SESSION['g_dossier'],'VEN',$p_jrn,'<td class="cell"><A class="mtitle" HREF="commercial.php?liste&p_action=facture&sa=list">Liste</A></td>');
echo '</div>';
//-----------------------------------------------------
// if we request to add an item 
// the $_POST['add_item'] is set
// or if we ask to correct the invoice
if ( isset ($_POST['add_item']) || isset ($_POST["correct_new_invoice"])  ) 
{
  $nb_item=$_POST['nb_item'];
  if ( isset ($_POST['add_item']))
    $nb_item++;
  $form=FormVenInput($cn,$_GET['p_jrn'],$User->GetPeriode(),$_POST,false,$nb_item);
  echo '<div class="u_redcontent">';
  echo $form;
  echo '</div>';
  exit();
}
//-----------------------------------------------------
// we want to save the invoice and to generate a invoice
//
if ( isset($_POST['record_and_print_invoice'])) 
{
  // First we save the invoice, the internal code will be used to change the description
  // and upload the file
  list ($internal,$e)=RecordInvoice($cn,$_POST,$User,$p_jrn);

  
  $form=FormVenteView($cn,$_GET['p_jrn'],$User->GetPeriode(),$_POST,$_POST['nb_item'],'noform','');

  echo '<div class="u_redcontent">';
  echo '<h2 class="info"> Op&eacute;ration '.$internal.' enregistr&eacute;</h2>';
  echo $form;
  echo '<hr>';
  
  // Show the details of the encoded invoice 
  // and the url of the invoice
  if ( isset($_POST['gen_invoice'])) 
    {
      	  $doc=new Document($cn);
	  $doc->f_id=$_POST['e_client'];
	  $doc->md_id=$_POST['gen_doc'];
	  $doc->ag_id=0;
	  $str_file=$doc->Generate();
	  // Move the document to the jrn
	  $doc->MoveDocumentPj($internal);
	  // Update the comment with invoice number
	  $sql="update jrn set jr_comment='Facture ".$doc->d_number."' where jr_internal='$internal'";
	  ExecSql($cn,$sql);
	  echo $str_file;
    }
  echo '</form>';
  exit();
}
//-----------------------------------------------------
// we show the confirmation screen it is proposed here to generate the
// invoice
if ( isset ($_POST['view_invoice']) ) 
{
  $nb_number=$_POST["nb_item"];
  if ( form_verify_input($cn,$_GET['p_jrn'],$User->GetPeriode(),$HTTP_POST_VARS,$nb_number) == true)
    {
      $form=FormVenteView($cn,$_GET['p_jrn'],$User->GetPeriode(),$HTTP_POST_VARS,$nb_number);

    } else {
      // Check failed : invalid date or quantity
      echo_error("Cannot validate ");
      $form=FormVenInput($cn,$_GET['p_jrn'],$User->GetPeriode(),$HTTP_POST_VARS,false,$nb_number);
    }

  echo '<div class="u_redcontent">';
  echo         $form;
  echo '</div>';
  exit();

}



//-----------------------------------------------------
// By default we add a new invoice
if ( $p_jrn != -1 ) 
{
  $jrn=new jrn($cn,  $p_jrn);
  echo_debug('facture.inc.php.php',__LINE__,"Blank form");
  // Show an empty form of invoice
  $form=FormVenInput($cn,$p_jrn,$User->GetPeriode(),null,false,$jrn->GetDefLine('cred'));
  echo '<div class="u_redcontent">';
  echo $form;
  echo '</div>';
}
