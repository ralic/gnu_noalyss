<?

/*
 *   This file is part of WCOMPTA.
 *
 *   WCOMPTA is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   WCOMPTA is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with WCOMPTA; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Auteur Dany De Bontridder ddebontridder@yahoo.fr
// $Revision$

    include_once("jrn.php");
    include_once("ac_common.php");
    include_once("postgres.php");
    include_once("class.ezpdf.php");
    include_once("impress_inc.php");
include("poste.php");
    echo_debug("imp pdf journaux");
    $l_Db=sprintf("dossier%d",$g_dossier);
    $cn=DbConnect($l_Db);
foreach ($HTTP_POST_VARS as $key=>$element) {
  ${"$key"}=$element;
}


    $ret="";
    $pdf=& new Cezpdf();
    $pdf->selectFont('./addon/fonts/Helvetica.afm');
$cond=CreatePeriodeCond($periode);
//$rap_deb=0;$rap_cred=0;
for ( $i =0;$i<count($poste);$i++) {
  
    $Libelle=sprintf("(%s) %s ",$poste[$i],GetPosteLibelle($cn,$poste[$i],1));
    list($array,$tot_deb,$tot_cred)=GetDataPoste($cn,$poste[$i],$cond);
    
    //  $pdf->ezText($Libelle,30);
    $pdf->ezTable($array,
		  array ('jr_internal'=>'Op�ration',
		       'j_date' => 'Date',
		       'jrn_name'=>'Journal',
		       'debit'=> 'Type',
		       'montant'=> 'Montant',
		       ),$Libelle,
		array('shaded'=>0,'showHeadings'=>1,'width'=>500,
		      'cols'=>array('montant'=> array('justification'=>'right'),
				    )));
$str_debit=sprintf("D�bit  % 12.2f",$tot_deb);
$str_cred=sprintf("Cr�dit % 12.2f",$tot_cred);
 $pdf->ezText($str_debit,14,array('justification'=>'right'));
 $pdf->ezText($str_cred,14,array('justification'=>'right'));

  //New page
  $pdf->ezNewPage();
}    

$pdf->ezStream();

?>
