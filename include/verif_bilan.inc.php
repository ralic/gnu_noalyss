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
 * \brief Verify the saldo of ledger: independant file
 */

require_once ('class_user.php');

$cn=DbConnect(dossier::id());
$User=new User($cn);

  echo '<div class="content">';
  $User->db=$cn;
  $sql_year=" and c_periode in (select p_id from parm_periode where p_exercice='".$User->get_exercice()."')";

  echo '<ol>';
  $deb=getDbValue($cn,"select sum (c_montant) from centralized where c_debit='t' $sql_year ");
  $cred=getDbValue($cn,"select sum (c_montant) from centralized where c_debit='f' $sql_year ");

  if ( $cred == $deb ) { 
    $result ='<span style="color:green;font-size:120%;font-weight:bold;"> OK </span>';}
  else  { 
    $result ='<span style="color:red;font-size:120%;font-weight:bold;"> NON OK </span>';}

  printf ('<li> Solde Grand Livre centralis&eacute;: debit %f credit %f %s</li>',$deb,$cred,$result);

  $sql="select jrn_def_id,jrn_def_name from jrn_def";
  $res=ExecSql($cn,$sql);
  $jrn=pg_fetch_all($res);
  foreach ($jrn as $l) {
    $id=$l['jrn_def_id'];
    $name=$l['jrn_def_name'];
    $deb=getDbValue($cn,"select sum (c_montant) from centralized where c_debit='t' and c_jrn_def=$id $sql_year ");
    $cred=getDbValue($cn,"select sum (c_montant) from centralized where c_debit='f' and c_jrn_def=$id  $sql_year ");

  if ( $cred == $deb ) { 
    $result ='<span style="color:green;font-size:120%;font-weight:bold;"> OK </span>';}
  else  { 
    $result ='<span style="color:red;font-size:120%;font-weight:bold;"> NON OK </span>';}

  printf ('<li> Journal %s Solde   centralis&eacute;: debit %f credit %f %s</li>',$name,$deb,$cred,$result);
    
  }
  echo '</ol>';
  echo '<ol>';
  $sql_year=" and j_tech_per in (select p_id from parm_periode where p_exercice='".$User->get_exercice()."')";

  $deb=getDbValue($cn,"select sum (j_montant) from jrnx where j_debit='t' $sql_year ");
  $cred=getDbValue($cn,"select sum (j_montant) from jrnx where j_debit='f' $sql_year ");

  if ( $cred == $deb ) { 
    $result ='<span style="color:green;font-size:120%;font-weight:bold;"> OK </span>';}
  else  { 
    $result ='<span style="color:red;font-size:120%;font-weight:bold;"> NON OK </span>';}

  printf ('<li> Total solde Grand Livre : debit %f credit %f %s</li>',$deb,$cred,$result);
  $sql="select jrn_def_id,jrn_def_name from jrn_def";
  $res=ExecSql($cn,$sql);
  $jrn=pg_fetch_all($res);
  foreach ($jrn as $l) {
    $id=$l['jrn_def_id'];
    $name=$l['jrn_def_name'];
    $deb=getDbValue($cn,"select sum (j_montant) from jrnx where j_debit='t' and j_jrn_def=$id $sql_year ");
    $cred=getDbValue($cn,"select sum (j_montant) from jrnx where j_debit='f' and j_jrn_def=$id  $sql_year ");

  if ( $cred == $deb ) { 
    $result ='<span style="color:green;font-size:120%;font-weight:bold;"> OK </span>';}
  else  { 
    $result ='<span style="color:red;font-size:120%;font-weight:bold;"> NON OK </span>';}

  printf ('<li> Journal %s total : debit %f credit %f %s</li>',$name,$deb,$cred,$result);
    
  }

  echo '</div>';
?>
