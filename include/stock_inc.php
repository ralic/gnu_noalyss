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


/* function ViewStock ($p_cn)
 **************************************************
 * Purpose : show the listing of all goods 
 *        
 * parm : 
 *	- database connection
 * gen :
 *	-
 * return: string containing the table
 */
function ViewStock($p_cn,$p_year) {
 // build sql
$sql=" select e.sg_code,sum(deb_sum) as deb_sum,sum(cred_sum) as cred_sum
from ( select C.sg_code,sum(deb) as deb_sum,sum(cred) as cred_sum
      from stock_goods 
     join  jrnx using (j_id)
     join
    ( select sg_code,j_id,
                case when sg_type='d' then sg_quantity else 0 end as deb,
                case when sg_type='c' then sg_quantity else 0 end as cred
               from stock_goods
       where sg_code is not null
       and sg_code != 'null'
     ) as C on (C.j_id=jrnx.j_id)
    where
    ( to_char(j_date,'YYYY') = '$p_year'
      or to_char(sg_date,'YYYY') = '$p_year'
    )
group by c.sg_code
union
select D.sg_code,sum(deb) as deb_sum,sum(cred) as cred_sum
      from stock_goods
    left outer join
    ( select sg_code,j_id,
                case when sg_type='d' then sg_quantity else 0 end as deb,
                case when sg_type='c' then sg_quantity else 0 end as cred
               from stock_goods
       where f_id=0
     ) as D using (sg_code)
    where
      to_char(sg_date,'YYYY') = '2003'

group by d.sg_code ) as E
group by e.sg_code 
";


  // send the sql
  $Res=ExecSql($p_cn,$sql);

  
  if ( ( $M = pg_NumRows($Res)) == 0 ) return null;
  // store it in a HTLM table
  $result="<table>";
  $result.="<tr>";
  $result.='<th>Code</th>';
  $result.='<th>Noms</th>';
  $result.='<th>Entr�e</th>';
  $result.='<th>Sortie</th>';
  $result.='<th>Solde</th>';
  $result.="</tr>";

  // Sql result => table
  for ($i = 0; $i < $M ; $i++ ) {
    $r=pg_fetch_array($Res,$i);
    $result.="<TR>";

    // sg_code  and link to details
    $result.="<td>".'<a class="one" 
              HREF="stock.php?action=detail&sg_code='.$r['sg_code'].'&year='.$p_year.'">'. 
              $r['sg_code']."</A></td>";

    // name
    $a_name=getFicheNameCode($p_cn,$r['sg_code']);
    $name="";
    if ( $a_name != null ) {
      foreach ($a_name as $key=>$element) {
	$name.=$element['av_text'].",";
      }
    }// if ( $a_name
    $result.="<td> $name </td>";

    // Debit (in)
    $result.="<td>".$r['deb_sum']."</td>";

    // Credit (out)
    $result.="<td>".$r['cred_sum']."</td>";


    // diff
    $diff=$r['deb_sum']-$r['cred_sum'];
    $result.="<td>".$diff."</td>";
    $result.="</tr>";

  }
      $result.="</table>";

  return $result;
}
/* function  getFicheNameCode ($p_cn,$p_sg_code)
 ************************************************************
 * Purpose : return an array of f_id and f_name
 *        
 * parm : 
 *	- p_cn database connection
 *      - stock_goods.sg_code
 * gen :
 *	- none
 * return:
 *      - array (f_id, f_label) or null if nothing is found 
 */
function getFicheNameCode ($p_cn,$p_sg_code) {
  // Sql stmt
$sql="select f_id,av_text
         from stock_goods
         join jnt_fic_att_value using (f_id )
         join attr_value using (jft_id)
         where 
          ad_id=".ATTR_DEF_NAME." 
          and sg_code='$p_sg_code'
           ";
// Execute
 $Res=ExecSql($p_cn,$sql);
 if ( ( $M=pg_NumRows($Res)) == 0 ) return null;
 
 // Store in an array
 for ( $i=0; $i<$M;$i++) {
   $r=pg_fetch_array($Res,$i);
   $a['f_id']=$r['f_id'];
   $a['av_text']=$r['av_text'];
   $result[$i]=$a;

 }

 return $result;
  
}
/* function ViewDetailStock($cn,$sg_code,$year)
 **************************************************
 * Purpose : 
 *        
 * parm : 
 *	- cn database connection
 *      - sg_code
 *      - year
 * gen :
 *	-
 * return: HTML code
 */
function ViewDetailStock($p_cn,$p_sg_code,$p_year) {
$sql="select sg_code,
             j_montant,
             j_date,
             sg_quantity,
             sg_type,
             jr_comment,
             jr_internal,
             jr_id,
        case when sg_date is not null then sg_date else j_date end as stock_date
      from stock_goods
      left outer join jrnx using (j_id)
      left outer join jrn on jr_grpt_id=j_grpt
           where 
      sg_code='$p_sg_code' and (
          to_char(sg_date,'YYYY') = '$p_year'
       or to_char(j_date,'YYYY') = '$p_year'
       )
      order by stock_date
 " ;
// $r.=sprintf('<input TYPE="button" onClick="modifyOperation(\'%s\',\'%s\')" value="%s">',
// 		    $row['jr_id'],$l_sessid,$row['jr_internal']);
    // name


  $r="";
  $a_name=getFicheNameCode($p_cn,$p_sg_code);
  $name="";
  if ( $a_name != null ) {
    foreach ($a_name as $key=>$element) {
      $name.=$element['av_text'].",";
    }
  }// if ( $a_name
  // Add java script for detail
  $r.=JS_VIEW_JRN_DETAIL;

 $r.='<H2 class="info">'.$p_sg_code."  Noms : ".$name.'</H2>';
  
  $Res=ExecSql($p_cn,$sql);
  if ( ($M=pg_NumRows($Res)) == 0 ) return "no rows";
  $r.="<table>";
  $r.="<TR>";
  $r.="<th>Date </th>";
  $r.="<th>Entr�e / Sortie </th>";
  $r.="<th></th>";
  $r.="<th>Description</th>";
  $r.="<th>Montant</th>";
  $r.="<th>Quantit�</th>";
  $r.="<th>Prix/Cout Unitaire</th>";
  $r.="</TR>";
  // compute sessid
  $l_sessid=(isset($_POST['PHPSESSID']))?$_POST['PHPSESSID']:$_GET['PHPSESSID'];


  for ( $i=0; $i < $M;$i++) {
    $l=pg_fetch_array($Res,$i);
    $r.="<tR>";

    // date
    $r.="<TD>";
    $r.=$l['j_date'];
    $r.="</TD>";

    //type (deb = out cred=in)
    $r.="<TD>";
    $r.=($l['sg_type']=='c')?'OUT':'IN';
    $r.="</TD>";

    // jr_internal
    $r.="<TD>";
    $r.="</TD>";


    // comment
    $r.="<TD>";
    $r.=$l['jr_comment'];
    $r.="</TD>";

    //amount
    $r.="<TD>";
    $r.=$l['j_montant'];
    $r.="</TD>";

    //quantity
    $r.="<TD>";
    $r.=$l['sg_quantity'];
    $r.="</TD>";

    // Unit Price
    $r.="<TD>";
    $up=$l['j_montant']/$l['sg_quantity'];
    $r.=$up;
    $r.="</TD>";

    $r.="</TR>";
  }// for ($i
  $r.="</table>";



  return $r;
	 
}
/* function ChangeStock($cn,$sg_code,$sg_date)
 **************************************************
 * Purpose : 
 *        
 * parm : 
 *	-
 * gen :
 *	-
 * return:
 */
function ChangeStock($p_sg_code,$p_year){
$sg_date=date("d.m.Y");
$r='
<input type="text" name="stock_change" value="0">
<input type="hidden" name="sg_code" value="'.$p_sg_code.'">
<input type="text" name="sg_date" value="'.$sg_date.'">
<input type="hidden" name="year" value="'.$p_year.'">
<br>
 ';
 return $r;

}
