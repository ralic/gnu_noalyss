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
html_page_start($g_UserProperty['use_theme']);
if ( ! isset ( $g_dossier ) ) {
  echo "You must choose a Dossier ";
  phpinfo();
  exit -2;
}
include_once ("postgres.php");
/* Admin. Dossier */
CheckUser();


include_once ("top_menu_compta.php");
include_once ("check_priv.php");

ShowMenuCompta($g_dossier,$g_UserProperty);

if ( $g_UserProperty['use_admin'] == 0 ) {
  $r=CheckAction($g_dossier,$g_user,MPCMN);
  if ($r == 0 ){
    /* Cannot Access */
    NoAccess();
    exit -1;			

  }

}

/* Store the p_start parameter */
if ( isset ($g_start) ) {
  echo_debug(__FILE__,__LINE__,"g_start is defined [ $g_start ]");
}
if ( ! isset ( $g_start) ) {
  $g_start="";
}
if ( isset ($p_start)) { 
  echo_debug(__FILE__,__LINE__,"PCMN p_start : $p_start");
  echo_debug(__FILE__,__LINE__,"p_start[$p_start] and g_start  don't exist");
  session_register("g_start"); 
  $g_start=$p_start;
}

ShowMenuPcmn($g_start);
$l_Db=sprintf("dossier%d",$g_dossier);
$cn=DbConnect($l_Db);
echo '<DIV CLASS="ccontent">';
/* Analyse ce qui est demand� */
/* Effacement d'une ligne */
if (isset ($action)) {
  if ( $action=="del" ) {
    if ( isset ($l) ) {
      /* Ligne a enfant*/
      $R=ExecSql($cn,"select pcm_val from tmp_pcmn where pcm_val_parent=$l");
      if ( pg_NumRows($R) != 0 ) {
	echo "<SCRIPT> alert(\"Ne peut pas effacer le poste: d'autres postes en d�pendent\");</SCRIPT>";
      } else {
	/* V�rifier que le poste n'est pas utilis� qq part dans les journaux */
	$Res=ExecSql($cn,"select * from jrnx where j_poste=$l");
	if ( pg_NumRows($R) != 0 ) {
	  echo "<SCRIPT> alert(\"Ne peut pas effacer le poste: il est utilis� dans les journaux\");</SCRIPT>";
	}
	else {
	  $Del=ExecSql($cn,"delete from tmp_pcmn where pcm_val=$l");
	} // if pg_NumRows
      } // if pg_NumRows
    } // isset ($l)
  } //$action == del
} // isset action
/* Ajout d'une ligne */
if ( isset ( $_POST["Add"] ) ) {
  if ( isset ( $p_val) && isset ( $p_lib ) ) {
    $p_val=trim($p_val);
    $p_lib=trim($p_lib);
    $p_parent=$_POST["p_parent"];
    if ( strlen ($p_val) != 0 && strlen ($p_lib) != 0 ) {
      if (strlen ($p_val) == 1 ) {
	$p_parent=0;
      } else {
	if ( strlen(trim($p_parent))==0 && 
	     (string) $p_parent != (string)(int) $p_parent) {
	  $p_parent=substr($p_val,0,strlen($p_val)-1);
	}
	echo_debug(__FILE__,__LINE__,"Ajout valeur = $p_val parent = $p_parent");
      }
      /* Parent existe */
      $Ret=ExecSql($cn,"select pcm_val from tmp_pcmn where pcm_val=$p_parent");
      if ( pg_NumRows($Ret) == 0 ) {
	echo '<SCRIPT> alert(" Ne peut pas modifier; aucune poste parent"); </SCRIPT>';
      } else {
	$Ret=ExecSql($cn,"insert into tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent) values ('$p_val','$p_lib',$p_parent)");
      }
    } else {
      echo '<H2 class="error"> Valeurs invalides </H3>';
    }
  }
}
/* Modif d'une ligne */
if ( isset ($_POST["update"] ) ) {
  foreach ($HTTP_POST_VARS as $name => $element) {
    echo_debug(__FILE__,__LINE__,"name $name $element");
  }
    $p_val=trim($_POST["p_val"]);
    $p_lib=FormatString($_POST["p_name"]);
    $p_parent=trim($_POST["p_val_parent"]);
    $old_line=trim($_POST["p_old"]);
    echo_debug(__FILE__,__LINE__,"Update old : $old_line News = $p_val $p_lib");
    if ( strlen ($p_val) != 0 && strlen ($p_lib) != 0 && strlen($old_line)!=0 ) {
      if (strlen ($p_val) == 1 ) {
	$p_parent=0;
      } else {
	if ( strlen($p_parent)==0 ) {
	  $p_parent=substr($p_val,0,strlen($p_val)-1);
	  echo_debug(__FILE__,__LINE__,"Modif valeur = $p_val parent = $p_parent");
	}
      }
      /* Parent existe */
      $Ret=ExecSql($cn,"select pcm_val from tmp_pcmn where pcm_val=$p_parent");
      if ( pg_NumRows($Ret) == 0 || $p_parent==$old_line ) {
	echo '<SCRIPT> alert(" Ne peut pas modifier; aucune poste parent"); </SCRIPT>';
      } else {
	$Ret=ExecSql($cn,"update tmp_pcmn set pcm_val=$p_val, pcm_lib='$p_lib',pcm_val_parent=$p_parent where pcm_val=$old_line");
      }
    } else {
      echo '<script> alert(\'Update Valeurs invalides\'); </script>';
    }
  

}

$Ret=ExecSql($cn,"select pcm_val,pcm_lib,pcm_val_parent from tmp_pcmn where substr(pcm_val::text,1,1)='$g_start' order by pcm_val::text");
$MaxRow=pg_NumRows($Ret);

?>

<TABLE ALIGN="center" BORDER=0 CELLPADDING=0 CELLSPACING=0> 
<TR>
<TH> Classe </TH>
<TH> Libell� </TH>
<TH> Parent </TH>
</TR>
<TR>

<FORM ACTION="pcmn_update.php" METHOD="POST">
<TD>
<INPUT TYPE="TEXT" NAME="p_val" SIZE=7>
</TD>
<TD>
<INPUT TYPE="TEXT" NAME="p_lib" size=50>
</TD>
<TD>
<INPUT TYPE="TEXT" NAME="p_parent" size=5>
</TD>
<TD>
<INPUT TYPE="SUBMIT" Value="Add" Name="Add">
</TD>
</FORM>
</TR>
<?
for ($i=0; $i <$MaxRow; $i++) {
  $A=pg_fetch_array($Ret,$i);

  if ( $i%2 == 0 ) {
    $td ='<TD class="odd">';
  } else {
    $td='<TD class="even">';
  }
  echo "<TR> $td";
  echo $A['pcm_val'];

  echo "</td> $td";
  printf ("<A HREF=line_update.php?l=%d&n=%s&p=%s>",$A['pcm_val'],urlencode($A['pcm_lib']),$A['pcm_val_parent']);
  echo $A['pcm_lib'];
  echo '</A>';
  echo "</TD>";

  echo $td;
  echo $A['pcm_val_parent'];
  echo '</TD>';

  echo $td;
  printf ('<A href="pcmn_update.php?l=%d&action=del">Delete</A>',$A['pcm_val']);
  echo "</TD>";
  
  echo "</TR>";

}
echo "</DIV>";
html_page_stop();
?>
