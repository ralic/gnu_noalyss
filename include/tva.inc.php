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
 * \brief included file for customizing with the vat (account,rate...)
 */
require_once('class_own.php');
require_once('class_html_input.php');
require_once('class_ihidden.php');
require_once('class_itextarea.php');
echo '<div class="content">';
  // Confirm remove
  if ( isset ($_POST['confirm_rm'])) 
  {
    if ( CountSql($cn,'select * from tva_rate') > 1 )
      ExecSqlParam($cn,'select tva_delete($1)',array($_POST['tva_id']));
    else 
      echo '<p class="notice">Vous ne pouvez pas effacer tous taux'.
	' Si votre soci&eacute;t&eacute; n\'utilise pas la TVA, changer dans le menu soci&eacute;t&eacute</p>';
    
  }
//-----------------------------------------------------
// Record Change
  if ( isset ($_POST['confirm_mod'])
       || isset ($_POST['confirm_add'])) 
    {
      extract($_POST);
      $tva_label=FormatString($tva_label);
      $tva_rate=FormatString($tva_rate);
      $tva_comment=FormatString($tva_comment);
      $tva_poste=FormatString($tva_poste);
	  // remove space
	  $tva_poste=str_replace (" ","",$tva_poste);
      $err=0; // Error code

      if ( isNumber($tva_rate) == 0 ) {
	$err=2;
      } 

      if ( $err == 0 ) 
	{
	  if (  isset ($_POST['confirm_add']) ) 
	    {
	      $sql="select tva_insert($1,$2,$3,$4)";
	  
	      $res=ExecSqlParam($cn,
			    $sql,
			    array($tva_label,
				  $tva_rate,
				  $tva_comment,
				  $tva_poste)
		 );
	      $ret_sql=pg_fetch_row($res);
	      $err=$ret_sql[0];
	    }
	  if (  isset ($_POST['confirm_mod']) ) 
	    {
	      $Res=ExecSql($cn,
		       "select tva_modify($tva_id,'$tva_label',
                       '$tva_rate','$tva_comment','$tva_poste')");
	      $ret_sql=pg_fetch_row($Res);
	      $err=$ret_sql[0];
	    }

	}
      if ( $err != 0 ) 
	{
	  $err_code=array(1=>"Tva id n\'est pas un nombre",
			  2=>"Taux tva invalide",
			  3=>"Label ne peut être vide",
			  4=>"Poste invalide",
			  5=>"Tva id doit être unique");
	  $str_err=$err_code[$err];
	  echo "<script>alert ('$str_err'); </script>";;
	}
  }
// If company not use VAT
$own=new Own($cn);
if ( $own->MY_TVA_USE=='N' ){
  echo '<h2 class="error"> Vous n\'êtes pas assujetti à la TVA</h2>';
  exit();
}
  //-----------------------------------------------------
  // Display
  $sql="select tva_id,tva_label,tva_rate,tva_comment,tva_poste from tva_rate order by tva_rate";
  $Res=ExecSql($cn,$sql);
  ?>
<TABLE>
<TR>
<th>Label</TH>
<th>Taux</th>
<th>Commentaire</th>
<th>Poste</th>
</tr>
<?php  
  $val=pg_fetch_all($Res);
  echo_debug('parametre',__LINE__,$val);
  foreach ( $val as $row)
    {
      // load value into an array
      $index=$row['tva_id']     ;
      $tva_array[$index]=array(
		       'tva_label'=> $row['tva_label'],
		       'tva_rate'=>$row['tva_rate'],
		       'tva_comment'=>$row['tva_comment'],
		       'tva_poste'=>$row['tva_poste']
		       );

      echo "<TR>";
      echo '<FORM METHOD="POST">';



      echo "<TD>";
      echo HtmlInput::hidden('tva_id',$row['tva_id']);
      echo h($row['tva_label']);
      echo "</TD>";

      echo "<TD>";
      echo $row['tva_rate'];
      echo "</TD>";

      echo "<TD>";
      echo h($row['tva_comment']);
      echo "</TD>";

      echo "<TD>";
      echo $row['tva_poste'];
      echo "</TD>";

      echo "<TD>";
      echo '<input type="submit" name="rm" value="Efface">';
      echo '<input type="submit" name="mod" value="Modifie">';
      $w=new IHidden();
      $w->name="tva_id";
      $w->value=$row['tva_id'];
      echo $w->input();
      $w=new IHidden();
      $w->name="p_action";
      $w->value="tva";
      echo $w->input();

      echo "</TD>";

      echo '</FORM>';
      echo "</TR>";
    }
?>
</TABLE>
    <?php   // if we add / remove or modify a vat we don't show this button
if (   ! isset ($_POST['add'])
  &&   ! isset ($_POST['mod'])
  &&   ! isset ($_POST['rm'])

) { ?>
    <form method="post">
    <input type="submit" name="add" value="Ajouter un taux de tva">
    <input type="hidden" name="p_action" value="tva">
     </form>
<?php  
       } 


    //-----------------------------------------------------
    // remove
    if ( isset ( $_REQUEST['rm'])) 
      {
	echo_debug("parametre",__LINE__,"efface ".$_POST['tva_id']);
	echo "Voulez-vous vraiment effacer ce taux ? ";
	$index=$_POST['tva_id'];
	
?>
<table>
   <TR>
   <th>Label</TH>
   <th>Taux</th>
   <th>Commentaire</th>
   <th>Poste</th>
   </tr>
<tr>
   <td> <?php   echo $tva_array[$index]['tva_label']; ?></td>
   <td> <?php   echo $tva_array[$index]['tva_rate']; ?></td>
   <td> <?php   echo $tva_array[$index]['tva_comment']; ?></td>
   <td> <?php   echo $tva_array[$index]['tva_poste']; ?></td>
</Tr>
</table>
<?php  
    echo '<FORM method="post">';
    echo '<input type="hidden" name="tva_id" value="'.$index.'">';
    echo '<input type="submit" name="confirm_rm" value="Confirme">';
    echo '<input type="submit" value="Cancel" name="no">';
    echo "</form>"; 

  }
  //-----------------------------------------------------
  // add
  if ( isset ( $_REQUEST['add'])) 
  {
    echo_debug("parametre",__LINE__,"add a line ");
    echo "<fieldset><legend>Ajout d'un taux de tva </legend>";
    echo '<FORM method="post">';


?>
<table >
   <tr> <td align="right"> Label (ce que vous verrez dans les journaux)</td>
   <td> <?php   $w=new IText();$w->size=20; echo $w->input('tva_label','') ?></td>
</tr>
   <tr><td  align="right"> Taux de tva </td>
   <td> <?php   $w=new IText();$w->size=5; echo $w->input('tva_rate','') ?></td>
</tr>
<tr>
<td  align="right"> Commentaire </td>
   <td> <?php   $w=new ITextarea; $w->heigh=2;$w->width=20;echo $w->input('tva_comment','') ?></td>
</tr>
<tr>
   <td  align="right">Poste comptable utilisés format :debit,credit</td>
   <td> <?php   $w=new IText(); $w->size=10;echo $w->input('tva_poste','') ?></td>
</Tr>
</table>
<input type="submit" value="Confirme" name="confirm_add">
<input type="submit" value="Cancel" name="no">

 </FORM>
</fieldset>
<?php    }

  //-----------------------------------------------------
  // mod
  if ( isset ( $_REQUEST['mod'])) 
    {

      echo_debug("parametre",__LINE__,"modifie ".$_POST['tva_id']);
      echo "Tva à modifier";
      $index=$_POST['tva_id'];
      echo "<fieldset><legend>Modification d'un taux de tva </legend>";
      echo '<FORM method="post">';
      echo '<input type="hidden" name="tva_id" value="'.$index.'">';
?>
<table>
   <tr> <td align="right"> Label (ce que vous verrez dans les journaux)</td>
   <td> <?php   $w=new Itext();$w->size=20; echo $w->input('tva_label',$tva_array[$index]['tva_label']) ?></td>
</tr>
   <tr><td  align="right"> Taux de tva </td>

     <td> <?php   $w=new Itext();$w->size=5; echo $w->input('tva_rate',$tva_array[$index]['tva_rate']) ?></td>
</tr>
<tr>
<td  align="right"> Commentaire </td>
   <td> <?php   $w=new ITextarea(); $w->heigh=2;$w->width=20;
   echo $w->input('tva_comment',$tva_array[$index]['tva_comment']) ?></td>
</tr>
<tr>
   <td  align="right">Poste comptable utilisés format :debit,credit</td>

     <td> <?php   $w=new IText();$w->size=5; echo $w->input('tva_poste',$tva_array[$index]['tva_poste']) ?></td>
</Tr>
</table>
<input type="submit" value="Confirme" name="confirm_mod">
<input type="submit" value="Cancel" name="no">
 </FORM>
</fieldset>
<?php   
}
 echo '</div>';    

?>
