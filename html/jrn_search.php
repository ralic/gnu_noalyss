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
/* $Revision$ */
include_once ("ac_common.php");
html_page_start();
if ( ! isset ( $g_dossier ) ) {
  echo "You must choose a Dossier ";
  phpinfo();
  exit -2;
}
include_once ("postgres.php");
/* Admin. Dossier */
CheckUser();
echo '<SCRIPT LANGUAGE="javascript" SRC="win_search_jrn.js"></SCRIPT>';

if ( isset( $p_jrn )) {
  session_register("g_jrn");
  $g_jrn=$p_jrn;
}
$opt='<OPTION VALUE="<="> <=';
$opt.='<OPTION VALUE="<"> <';
$opt.='<OPTION VALUE="="> =';
$opt.='<OPTION VALUE=">"> >';
$opt.='<OPTION VALUE=">="> >=';
$c_comment="";
$c_montant="";
$c_date="";
$l_Db=sprintf("dossier%d",$g_dossier);
$condition="";
$part=" where ";
$cn=DbConnect($l_Db);
if ( isset ($_POST["search"]) ) {
  $c1=0;
  foreach( $HTTP_POST_VARS as $key=>$element){
    echo_debug("$key = $element");
    ${"$key"}=$element;
  }

  if ( strlen(trim($p_comment)) != 0 ) {
    $c_comment=" $part pcm_lib like '%$p_comment%'";
    $part=" and ";
  }
  if ( ereg ("^[0-9]*\.[0-9]*$",$p_montant) ||
        ereg ("^[0-9]*$",$p_montant) )
      { 
    $c_montant=sprintf(" $part j_montant %s %s",$p_montant_sel,$p_montant);
    $part="  and ";
    }
  if ( strlen(trim($p_date)) != 0 ) {
      $c_montant=sprintf(" $part j_date %s '%s'",$p_date_sel,$p_date);
      $part=" and ";
  }
$condition=$c_comment.$c_montant.$c_date;
echo_debug("condition = $condition");
}
$condition=$condition." $part uj_priv in ('W','R') and uj_login='".$user."'" ;
echo '<FORM ACTION="jrn_search.php" METHOD="POST">';
echo '<TABLE>';
echo '<TR>';
if ( ! isset ($p_date)) $p_date="";
if ( ! isset ($p_montant)) $p_montant="";
if ( ! isset ($p_comment)) $p_comment="";

echo '<TD> Date </TD>';
echo '<TD> <SELECT NAME="p_date_sel">'.$opt.' </TD>';
echo '<TD> <INPUT TYPE="text" name="p_date" VALUE="'.$p_date.'"></TD>';

echo '<TD> Montant </TD>';
echo '<TD> <SELECT NAME="p_montant_sel">'.$opt.' </TD>';
echo '<TD> <INPUT TYPE="text" name="p_montant" VALUE="'.$p_montant.'"></TD>';

echo '<TD> Commentaire </TD>';
echo '<TD> contient </TD>';
echo '<TD> <INPUT TYPE="text" name="p_comment" VALUE="'.$p_comment.'"></TD>';
echo "</TR>";

echo '</TABLE>';
echo '<INPUT TYPE="submit" name="search" value="cherche">';
echo '</FORM>';

$Res=ExecSql($cn,"select j_id,j_date,j_montant,j_poste,j_debit,j_tech_per,jr_id,jr_comment,j_grpt,pcm_lib,jr_internal from jrnx inner join 
                 jrn on jr_grpt_id=j_grpt inner join tmp_pcmn on j_poste=pcm_val ".
	     " inner join user_sec_jrn on uj_jrn_id=j_jrn_def".
	     $condition." order by j_id");

$MaxLine=pg_NumRows($Res);
if ( $MaxLine==0) { 
  html_page_stop();
  return;
}
$col_vide="<TD></TD>";
echo '<TABLE ALIGN="center" BORDER="0" CELLSPACING="O">';
$l_id="";
if ( $MaxLine > 250 ) {
  echo "Trop de lignes red�finir la recherche";
  html_page_stop();
  return;
}
for ( $i=0; $i < $MaxLine; $i++) {
  $l_line=pg_fetch_array($Res,$i);
    if ( $l_id == $l_line['j_grpt'] ) {
      echo $col_vide.$col_vide;
    } else {
      echo "<TR><TD>";
      echo '<INPUT TYPE="CHECKBOX" onClick="GetIt(\''.$l_line['jr_internal']."')\" >";
      echo "</TD>";

      echo "<TD>";
      echo $l_line['j_date'];
      echo "</TD>";
      
      echo "<TD>";
      echo $l_line['jr_internal'];
      echo "</TD>";
      $l_id=$l_line['j_grpt'];
      echo '<TD COLSPAN="4">'.$l_line['jr_comment'].'</TD></TR>';
    }
  if ( $l_line['j_debit'] == 't' ) {
    echo '<TR style="background-color:lightblue;">';
  }
  else {
    echo '<TR style="background-color:lightgreen;">';
  }
  echo $col_vide;
    if ( $l_line['j_debit']=='f')
      echo $col_vide;

    echo '<TD>';
    echo $l_line['j_poste'];
    echo '</TD>';

    if ( $l_line['j_debit']=='t')
      echo $col_vide;

    echo '<TD>';
    echo $l_line['pcm_lib'];
    echo '</TD>';

    if ( $l_line['j_debit']=='f')
      echo $col_vide;

    echo '<TD>';
    echo $l_line['j_montant'];
    echo '</TD>';

    echo "</TR>";

}
  
  echo '</TABLE>';

html_page_stop();
?>
