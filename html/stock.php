
<?
/*
 *   This file is part of PHPCOMPTA.
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
 *   along with PHPCOMPTA; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Auteur Dany De Bontridder ddebontridder@yahoo.fr
/* $Revision$ */
include_once("preference.php");
include_once ("ac_common.php");
include_once("postgres.php");
include_once("stock_inc.php");
include_once("check_priv.php");

html_page_start($_SESSION['use_theme']);

if ( ! isset ( $_SESSION['g_dossier'] ) ) {
  echo "You must choose a Dossier ";
  exit -2;
}
include_once ("postgres.php");
/* Admin. Dossier */
$rep=DbConnect();
include_once ("class_user.php");
$User=new cl_user($rep);
$User->Check();

// Synchronize rights
SyncRight($_SESSION['g_dossier'],$_SESSION['g_user']);

// Get The priv on the selected folder
if ( $User->admin == 0 ) {
  
  $r=GetPriv($_SESSION['g_dossier'],$_SESSION['g_user']);
  if ($r == 0 ){
    /* Cannot Access */
    NoAccess();
  }

}
$cn=DbConnect($_SESSION['g_dossier']);

//Show the top menu
include_once ("user_menu.php");

ShowMenuCompta($_SESSION['g_dossier']);

// Show Menu Left
$left_menu=ShowMenuAdvanced("stock.php");
//echo '<div class="lmenu">';
echo $left_menu;
//echo '</DIV>';
$action= ( isset ($_GET['action']))? $_GET['action']:"";
include_once("stock_inc.php");

// Adjust the stock
if ( isset ($_POST['sub_change'])) {
  $change=$_POST['stock_change'];
  $sg_code=$_POST['sg_code'];
  $sg_date=$_POST['sg_date'];
  if ( isDate($sg_date) == null 
       or isNumber($change) == 0 ) {
    $msg="Stock donn�es non conformes";
    echo "<script> alert('$msg');</script>";
    echo_error($msg);
  } else {
    // Check if User Can change the stock 
    if ( CheckAction($g_dossier,$g_user,STOCK_WRITE) == 0 ) {
      NoAccess();
      exit (-1);
    }

    // if neg the stock decrease => credit
    $type=( $change < 0 )?'c':'d';
    if ( $change != 0)
      $Res=ExecSql($cn,"insert into stock_goods
                     (  j_id,
                        f_id, 
                        sg_code,
                        sg_quantity,
                        sg_type,
                        sg_date,
                         sg_tech_user)
                    values (
                        null,
                        0,
                        '$sg_code',
                        abs($change),
                        '$type',
                        to_date('$sg_date','DD.MM.YYYY'),
                        '$g_user');
                     ");
  // to update the view
  $action="detail";
  }
}

// View the summary

// if year is not set then use the year of the user's periode
if ( ! isset ($_GET['year']) ) {
  // get defaut periode
  $a=GetUserPeriode($cn,$_SESSION['g_user']);
  // get exercice of periode
  $year=GetExercice($cn,$a);
  } else
  { 
    $year=$_GET['year'];
  }

// View details
if ( $action == 'detail' ) {
  // Check if User Can see the stock 
  if ( CheckAction($_SESSION['g_dossier'],$_SESSION['g_user'],STOCK_READ) == 0 ) {
    NoAccess();
    exit (-1);
  }
  $sg_code=(isset ($_GET['sg_code'] ))?$_GET['sg_code']:$_POST['sg_code'];
  $year=(isset($_GET['year']))?$_GET['year']:$_POST['year'];
  $a=ViewDetailStock($cn,$sg_code,$year);
  $b=ChangeStock($sg_code,$year);
    echo '<div class="u_redcontent">' ;
    echo $a;
    echo 'Entrer la valeur qui doit augmenter ou diminuer le stock';
    echo '<form action="stock.php" method="POST">';
    echo $b;
    echo '<input type="submit" name="sub_change" value="Ok">';
    echo '</form>';
    echo '</div>';
    exit();
}

// Show the possible years
$sql="select distinct (p_exercice) as exercice from parm_periode ";
$Res=ExecSql($cn,$sql);
$r="";
for ( $i = 0; $i < pg_NumRows($Res);$i++) {
  $l=pg_fetch_array($Res,$i);
  $r.=sprintf('<A class="one" HREF="stock.php?year=%d">%d</a> - ',
	      $l['exercice'],
	      $l['exercice']);
 
}
// Check if User Can see the stock 
if ( CheckAction($_SESSION['g_dossier'],$_SESSION['g_user'],STOCK_READ) == 0 ) {
  NoAccess();
  exit (-1);
}

// Show the current stock
echo '<div class="u_redcontent">';
echo $r;
$a=ViewStock($cn,$year);
if ( $a != null ) {
  echo $a;
}
echo '</div>';
html_page_stop();
?>
