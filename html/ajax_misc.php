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
 * \brief this file respond to an ajax request 
 * The parameters are
 * - PHPSESSID
 * - gDossier
 * - $op operation the file has to execute
 * Part 1
 * dsp_tva fill a ipopup with a choice of possible VAT
 *     - if code is set then fill the field code
 *     - if compute is set then add event to call clean_tva and compute_ledger
@Acc_Ledger_Sold::input
* Part 2
* dl : display form to modify, add and delete lettering for a given operation
 *
 */
require_once('class_database.php');
require_once ('class_fiche.php');
require_once('class_iradio.php');
require_once('function_javascript.php');
require_once('ac_common.php');
require_once ('class_user.php');

$var=array('PHPSESSID','gDossier');
$cont=0;
/*  check if mandatory parameters are given */
foreach ($var as $v) {
  if ( ! isset ($_REQUEST [$v] ) ) {
    echo "$v is not set ";
    $cont=1;
  }
}
if ( $cont != 0 ) exit();
extract($_GET );
set_language();

$cn=new Database($gDossier);
$user=new User($cn); $user->check(true);$user->check_dossier($gDossier,true);
$html=var_export($_REQUEST,true);
switch($op) 
  { 
    // display new calendar
  case 'cal':
    require_once('class_calendar.php');
    /* others report */
    $cal=new Calendar();
    $cal->set_periode($per);

    $html="";
    $html=$cal->display();
    $html=escape_xml($html);

header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>$html</code>
</data>
EOF;
 break;
 /* remove a cat of document */
  case 'rem_cat_doc':
    require_once('class_document_type.php');
    // if user can not return error message
    if(     $user->check_action(PARCATDOC)==0 ) {
      $html="nok";
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
return;
    }
    // remove the cat if no action 
    $count_md=$cn->get_value('select count(*) from document_modele where md_type=$1',array($dt_id));
   $count_a=$cn->get_value('select count(*) from action_gestion where ag_type=$1',array($dt_id));		      

    if ( $count_md != 0 || $count_a != 0 ) {
      $html="nok";
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
exit;
  }
$cn->exec_sql('delete from document_type where dt_id=$1',array($dt_id));
	 $html=$dt_id;
      header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<dtid>$html</dtid>
</data>
EOF;
	 return;
	 break;
  case 'dsp_tva':
    $cn=new Database($gDossier);
    $Res=$cn->exec_sql("select * from tva_rate order by tva_rate desc");
    $Max=Database::num_row($Res);
    $r="";
    $r='<div style="margin-left:10%;margin-right:10%">';
    $r= "<TABLE BORDER=\"1\">";
    $r.=th('');
    $r.=th(_('code'));
    $r.=th(_('Taux'));
    $r.=th(_('Symbole'));
    $r.=th(_('Explication'));

    for ($i=0;$i<$Max;$i++) {
      $row=Database::fetch_array($Res,$i);
      if ( ! isset($compute)) {
	  if ( ! isset($code) ) {
	    $script="onclick=\"$('$ctl').value='".$row['tva_id']."';hideIPopup('".$popup."');\"";
	  } else {
	    $script="onclick=\"$('$ctl').value='".$row['tva_id']."';set_value('$code','".$row['tva_label']."');hideIPopup('".$popup."');\"";
	  }
	} else {
	  if ( ! isset($code) ) {
	    $script="onclick=\"$('$ctl').value='".$row['tva_id']."';hideIPopup('".$popup."');clean_tva('$compute');compute_ledger('$compute');\"";
	  } else {
	    $script="onclick=\"$('$ctl').value='".$row['tva_id']."';set_value('$code','".$row['tva_label']."');hideIPopup('".$popup."');clean_tva('$compute');compute_ledger('$compute');\"";
	  }

	}
      $set= '<INPUT TYPE="BUTTON" Value="select" '.$script.'>';
      $r.='<tr>';
      $r.=td($set);
      $r.=td($row['tva_id']);
      $r.=td($row['tva_rate']);
      $r.=td($row['tva_label']);
      $r.=td($row['tva_comment']);
    $r.='</tr>';
    }
    $r.='</TABLE>';
    $r.='</div>';
    $html=escape_xml($r);
      
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>$html</code>
<popup>$popup</popup>
</data>
EOF;
break;
  case 'label_tva':
    $cn=new Database($gDossier);
    if ( isNumber($id) == 0 ) 
      $value = _('tva inconnue');
    else {
      $Res=$cn->get_array("select * from tva_rate where tva_id = $1",array($id));
      if ( count($Res) == 0 ) 
	$value=_('tva inconnue');
      else 
	$value=$Res[0]['tva_label'];
    }
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>$code</code>
<value>$value</value>
</data>
EOF;

    break;
    /**
     *display the lettering
     */
case 'dl':
  require_once('class_lettering.php');
       $ret=new IButton('return');
       $ret->label=_('Retour');
       $ret->javascript="$('detail').hide();$('list').show();";
       $r="";
       // retrieve info for the given j_id (date, amount,side and comment)
       $sql="select j_date,to_char(j_date,'DD.MM.YYYY') as j_date_fmt,J_POSTE,j_qcode,
jr_comment,j_montant, j_debit,jr_internal from jrnx join jrn on (j_grpt=jr_grpt_id)
     where j_id=$1";
       $arow=$cn->get_array($sql,array($j_id));
       $row=$arow[0];

       $r.='<fieldset><legend>'._('Lettrage').'</legend>';
       $r.='Poste '.$row['j_poste'].'  '.$row['j_qcode'].'<br>';
       $r.='Date : '.$row['j_date_fmt'].' ref :'.$row['jr_internal'].' <br>  ';
       $r.=h($row['jr_comment'])." montant: ".($row['j_montant'])." --  ".(($row['j_debit']=='t')?'D':'C');
       $r.='</fieldset>';
     
       // display a list of operation from the other side + box button
       if ( $ot == 'account') {
	 $obj=new Lettering_Account($cn,$row['j_poste']);
	 $r.=$obj->show_letter($j_id);
       } else if ($ot=='card') {
	 $obj=new Lettering_Card($cn,$row['j_qcode']);
	 $r.=$obj->show_letter($j_id);
       } else {
	 $r.='Mauvais type objet';
       }
       $html='<FORM METHOD="post">';
       $html.=HtmlInput::phpsessid();
       $html.=dossier::hidden();
       if ( isset($_REQUEST['p_action']))       $html.=HtmlInput::hidden('p_action',$_REQUEST['p_action']);
       if ( isset($_REQUEST['sa']))       $html.=HtmlInput::hidden('sa',$_REQUEST['sa']);
       if ( isset($_REQUEST['acc']))       $html.=HtmlInput::hidden('acc',$_REQUEST['acc']);

       $html.=$r;
       $html.=HtmlInput::submit('record',_('Sauver')).$ret->input();
       $html.='</FORM>';
       //       echo $html;exit;
        $html=escape_xml($html);

       header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<code>detail</code>
<value>$html</value>
</data>
EOF;

       break;
  }
