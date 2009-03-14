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
 * \brief Send a CSV file with card
 */

header('Content-type: application/csv');
header('Content-Disposition: attachment;filename="fiche.csv"',FALSE);
include_once ("ac_common.php");
include_once('class_fiche.php');
include_once ("postgres.php");
require_once('class_dossier.php');
$gDossier=dossier::id();

if (  isset ($_REQUEST['with_amount']))  include_once("class_acc_account_ledger.php");
$cn=DbConnect($gDossier);

require_once ('class_user.php');
$User=new User($cn);
$User->Check();
$User->check_dossier($gDossier);
$User->can_request(IMPFIC,0);


if  ( isset ($_POST['fd_id'])) {
  $fiche_def=new fiche_def($cn,$_POST['fd_id']);
  $fiche=new fiche($cn);
  $e=$fiche_def->GetByType();
  $o=0;
  //  Heading
  $fiche_def->GetAttribut();
  foreach ($fiche_def->attribut as $attribut) {
	  if ( $o == 0 ) {
    		printf("%s",$attribut->ad_text);
		$o=1;
	  }else {
	    printf(";%s",$attribut->ad_text);
	    if ( $attribut->ad_id == ATTR_DEF_ACCOUNT 
		 && isset ($_REQUEST['with_amount'])) 
	      echo ";debit;credit;solde";
	  }
    }
  printf("\n");
  $o=0;
  // Details
  // Save the accounting 
  foreach ($e as $detail) {
    foreach ( $detail->attribut as $dattribut ) {
	  if ( $o == 0 ) {
    		printf("%s",$dattribut->av_text);
		$o=1;
	  } else {
	    printf (";%s",$dattribut->av_text);
	    // if solde resquested
	    //--
	    if ( $dattribut->ad_id == ATTR_DEF_ACCOUNT 
		 && isset ($_REQUEST['with_amount']))  {

	      $sql_periode=sql_filter_per($cn,$_REQUEST['from_periode'],$_REQUEST['to_periode'],'p_id','j_tech_per');
	      $solde=  $detail->get_solde_detail($sql_periode);

	      
	      printf(";% 10.2f;% 10.2f;% 10.2f",
		     $solde['debit'],
		     $solde['credit'],
		     $solde['solde']
		     );
	    

	    }
	  }
      }
    printf("\n");
    $o=0;
    }


 }
  exit;
?>
