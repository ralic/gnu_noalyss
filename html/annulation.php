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
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
/* $Revision$ */
include_once ("ac_common.php");
include_once ("poste.php");
include_once("preference.php");
include_once("central_inc.php");
include_once("user_common.php");

html_page_start($g_UserProperty['use_theme']);
if ( ! isset ( $g_dossier ) ) {
  echo "You must choose a Dossier ";
  exit -2;
}
include_once ("postgres.php");
include_once("jrn.php");
/* Admin. Dossier */
CheckUser();

if ( isset( $p_jrn )) {
  session_register("g_jrn");
  $g_jrn=$p_jrn;
}

/* TODO Security check if the user can access and write here */
/*************************************************************/
$l_Db=sprintf("dossier%d",$g_dossier);
$cn=DbConnect($l_Db);

list ($l_array,$max_deb,$max_cred)=GetData($cn,$jrn_op);
foreach ($l_array as $key=>$element) {
  ${"e_$key"}=$element;
  echo_debug("e_$key =$element");
}

// annulate a operation
if ( isset ($annul) ) {
  if ( isset ($_POST['p_id'])) {
    // Get the current periode
 $period=GetUserPeriode($cn,$g_user);

 // Check if it a centralize operation
 if ( isCentralize($cn,$p_id) == 0 ) {
 
 // get the next op id
  $seq=GetNextId($cn,'j_grpt')+1;

  // build the sql stmt for jrnx
  $sql="insert into jrnx  (
 j_montant, j_poste,j_grpt,j_jrn_def, j_debit,
 j_text,j_internal,j_tech_user,j_tech_per
 )
 select 
 j_montant, j_poste, $seq, j_jrn_def, case when j_debit=false then true else false end, 
 j_text,j_internal,'$g_user',$period
 from jrnx where j_grpt=".$_POST['p_id'];
  $Res=ExecSql($cn,$sql);
 

  // build the sql stmt for jrn
  $sql= "insert into jrn (
 jr_def_id,jr_montant,jr_comment,               jr_date,jr_grpt_id,jr_internal                 ,jr_tech_per
 ) select 
 jr_def_id,jr_montant,'Annulation '||jr_comment,jr_date,$seq       ,'ANNUL',               $period
 from 
 jrn
 where   jr_grpt_id=".$_POST['p_id'];
  $Res=ExecSql($cn,$sql);

 // Get the internal code
  $internalcode=SetInternalCode($cn,$seq,$g_jrn);
  // also in the stock table
  $sql="insert into stock_goods (
 j_id,f_id,sg_quantity,sg_type
 ) select
 j_id,f_id,sg_quantity, case when sg_type='c' then 'd' else 'c' end
 from stock_goods natural join jrnx  where j_grpt=".$_POST['p_id'];
  $Res=ExecSql($cn,$sql);
 }
 echo '<h2 class="info"> Op�ration annul�e</h2>';
?>
<script>
   //window.close();
 self.opener.RefreshMe();
</script>
<?
    } // 
}
echo '<div align="center"> Op�ration '.$l_array['jr_internal']."</div>";

echo 'Date : '.$e_op_date;
echo '<div style="border-style:solid;border-width:1pt;">';
echo $e_comment;
echo '</DIV>';

if ( isset ($e_ech) ) {
  echo "<DIV> Echeance $e_ech </DIV>";
}
for ( $i = 0; $i < $max_deb;$i++) {
  $lib=GetPosteLibelle($g_dossier,${"e_class_deb$i"}); 
  echo '<div style="background-color:#BFC2D5;">';
  echo ${"e_class_deb$i"}." $lib    "."<B>".${"e_mont_deb$i"}."</B>";
  echo "</div>";
}
for ( $i = 0; $i < $max_cred;$i++) {
  $lib=GetPosteLibelle($g_dossier,${"e_class_cred$i"});
  echo '<div style="background-color:#E8F4FF;">';
  echo ${"e_class_cred$i"}."  $lib   "."<B>".${"e_mont_cred$i"}."</B>";
  echo '</div>';
}
//echo "operation concern�e $e_rapt<br><br>
//";
$a=GetConcerned($cn,$e_jr_id);

if ( $a != null ) {
  foreach ($a as $key => $element) {
    echo "operation concern�e <br>";

    echo "<A HREF=\"jrn_op_detail.php?jrn_op=".GetGrpt($cn,$element)."\"> ".GetInternal($cn,$element)."</A><br>";
  }//for
}// if ( $a != null ) {

echo '
<form action="'.$_SERVER['REQUEST_URI'].'" method="post" >
<input type="hidden" name="p_id" value="'.$_GET['jrn_op'].'">
<input type="submit" name="annul"  value="Effacer">
<input type="button" name="cancel" value="Escape">
</form>';

html_page_stop();
?>
