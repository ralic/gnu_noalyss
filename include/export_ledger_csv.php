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
/*! \file
 * \brief Send a ledger in CSV format
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$fDate = date('dmy-Hi');
header('Pragma: public');
header('Content-type: application/csv');
header('Content-Disposition: attachment;filename="jrn-'.$fDate.'.csv"',FALSE);
include_once ("ac_common.php");
require_once('class_own.php');
require_once 'class_acc_ledger_sold.php';
require_once 'class_acc_ledger_purchase.php';
require_once('class_dossier.php');
$gDossier=dossier::id();

require_once('class_database.php');
require_once("class_acc_ledger.php");


require_once ('class_user.php');
$g_user->Check();
$g_user->check_dossier($gDossier);

if ($_GET['jrn_id']!=0 &&  $g_user->check_jrn($_GET['jrn_id']) =='X')
{
    NoAccess();
    exit();
}

$Jrn=new Acc_Ledger($cn,$_GET['jrn_id']);

$Jrn->get_name();
$jrn_type=$Jrn->get_type();

//
// With Detail per item which is possible only for VEN or ACH
// 
if ($_GET['p_simple'] == 2)
{
    if ($jrn_type != 'ACH' && $jrn_type != 'VEN' || $Jrn->id == 0)
    {
        $_GET['p_simple'] = 0;
    }
    else
    {
        switch ($jrn_type)
        {
            case 'VEN':
                $ledger = new Acc_Ledger_Sold($cn, $_GET['jrn_id']);
                $ret_detail = $ledger->get_detail_sale($_GET['from_periode'], $_GET['to_periode']);
                break;
            case 'ACH':
                $ledger = new Acc_Ledger_Purchase($cn, $_GET['jrn_id']);
                $ret_detail = $ledger->get_detail_purchase($_GET['from_periode'], $_GET['to_periode']);
                
                break;
            default:
                die(__FILE__ . ":" . __LINE__ . 'Journal invalide');
                break;
        }
        if ($ret_detail == null)
            return;
        $nb = Database::num_row($ret_detail);
        $output=fopen("php://output","w");
        
        for ($i = 0;$i < $nb ; $i++) {
            $row=Database::fetch_array($ret_detail, $i);
            if ( $i == 0 ) {
              foreach ($row as $key=>$value) {
                  if (isNumber($key) == 0 )$array_key[]=$key;
              }
              fputcsv($output,$array_key,';');
            }
            $a_row=array();
            for ($j=0;$j < count($row) / 2;$j++) {
                $a_row[]=$row[$j];
            }
            fputcsv($output,$a_row,';');
            unset($a_row);
        }
    }
}
// Detailled printing
//---
if  ( $_GET['p_simple'] == 0 )
{
    $Jrn->get_row( $_GET['from_periode'],
                   $_GET['to_periode']
                 );

    if ( count($Jrn->row) == 0)
        exit;
    foreach ( $Jrn->row as $op )
    {
        // should clean description : remove <b><i> tag and '; char
        $desc=$op['description'];
        $desc=str_replace("<b>","",$desc);
        $desc=str_replace("</b>","",$desc);
        $desc=str_replace("<i>","",$desc);
        $desc=str_replace("</i>","",$desc);
        $desc=str_replace('"',"'",$desc);
        $desc=str_replace(";",",",$desc);

        printf("\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";%s;%s\n",
               $op['j_id'],
               $op['jr_pj_number'],
               $op['internal'],
               $op['j_date'],
               $op['poste'],
               $desc,
               nb($op['deb_montant']),
               nb($op['cred_montant'])
              );

    }
    exit;
}
else if  ($_GET['p_simple'] == 1)
{
    $Row=$Jrn->get_rowSimple($_GET['from_periode'],
                             $_GET['to_periode'],
                             0);
//-----------------------------------------------------
     if ( $jrn_type == 'ODS' || $jrn_type == 'FIN' || $jrn_type=='GL')
       {
	 printf ('" operation";'.
		 '"Date";'.
		 '"N° Pièce";'.
		 '"Tiers";'.
		 '"commentaire";'.
		 '"internal";'.
		 '"montant";'.
		 "\r\n");
	 foreach ($Row as $line)
	   {

	     echo $line['num'].";";
	     echo $line['date'].";";
	     echo $line['jr_pj_number'].";";
	     echo $Jrn->get_tiers($line['jrn_def_type'],$line['jr_id']).";";
	     echo $line['comment'].";";
	     echo $line['jr_internal'].";";
	     //	  echo "<TD>".$line['pj'].";";
	     // If the ledger is financial :
	     // the credit must be negative and written in red
	     // Get the jrn type
	     if ( $line['jrn_def_type'] == 'FIN' ) {
	       $positive = $cn->get_value("select qf_amount from quant_fin  ".
					  " where jr_id=".$line['jr_id']);

	       echo nb($positive);
	       echo ";";
	     }
	     else
	       {
		 echo nb($line['montant']).";";
	       }

	     printf("\r\n");
	   }
       }

//-----------------------------------------------------
    if ( $jrn_type=='ACH' || $jrn_type=='VEN')
    {
        $cn->prepare('reconcile_date',"select to_char(jr_date,'DD.MM.YY') as str_date,* from jrn where jr_id in (select jra_concerned from jrn_rapt where jr_id = $1 union all select jr_id from jrn_rapt where jra_concerned=$1)");

        $own=new Own($cn);
        $col_tva="";

        if ( $own->MY_TVA_USE=='Y')
        {
            $a_Tva=$cn->get_array("select tva_id,tva_label from tva_rate where tva_rate != 0.0000 order by tva_rate");
            foreach($a_Tva as $line_tva)
            {
                $col_tva.='"Tva '.$line_tva['tva_label'].'";';
            }
        }
        echo '"Date";"Paiement";"operation";"Client/Fourn.";"Commentaire";"inter.";"HTVA";privé;DNA;tva non ded.;opérations liées'.$col_tva.'"TVAC"'."\n\r";
        foreach ($Row as $line)
        {
            printf('"%s";"%s";"%s";"%s";"%s";%s;%s;%s;%s;',
                   $line['date'],
                   $line['date_paid'],
                   $line['num'],
                   $Jrn->get_tiers($line['jrn_def_type'],$line['jr_id']),
                   $line['comment'],
                   $line['jr_internal'],
                   nb($line['HTVA']),
                   nb($line['dep_priv']),
                   nb($line['dna']),
                   nb($line['tva_dna'])
                   );
            $a_tva_amount=array();
            foreach ($line['TVA'] as $lineTVA)
            {
                foreach ($a_Tva as $idx=>$line_tva)
                {

                    if ($line_tva['tva_id'] == $lineTVA[1][0])
                    {
                        $a=$line_tva['tva_id'];
                        $a_tva_amount[$a]=$lineTVA[1][2];
                    }
                }
            }
            if ($own->MY_TVA_USE == 'Y' )
            {
                foreach ($a_Tva as $line_tva)
                {
                    $a=$line_tva['tva_id'];
                    if ( isset($a_tva_amount[$a]))
                        echo nb($a_tva_amount[$a]).';';
                    else
                        printf("0;");
                }
            }
            echo nb ($line['TVAC']);
            /**
             * Retrieve payment if any
             */
             $ret_reconcile=$cn->execute('reconcile_date',array($line['jr_id']));
             $max=Database::num_row($ret_reconcile);
            if ($max > 0) {
                $sep=";";
                for ($e=0;$e<$max;$e++) {
                    $row=Database::fetch_array($ret_reconcile, $e);
                    echo $sep.$row['str_date'].'; '. $row['jr_internal'];
                }
            }
	    printf("\r\n");

        }
    }
}
?>
