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
 *
 *
 * \brief to write directly into the ledgers,the stock and the tables
 * quant_purchase and quant_sold are not changed by this
 *
 */
require_once("class_icheckbox.php");
require_once ('class_acc_ledger.php');
require_once ('class_acc_reconciliation.php');
require_once('ac_common.php');
require_once('class_periode.php');
require_once('function_javascript.php');
require_once('class_ipopup.php');

echo ICard::ipopup('ipopcard');
echo ICard::ipopup('ipop_newcard');
echo IPoste::ipopup('ipop_account');
$search_card=new IPopup('ipop_card');
$search_card->title=_('Recherche de fiche');
$search_card->value='';
echo $search_card->input();
$pop_tva=new IPopup('popup_tva');
$pop_tva->title=_('Choix TVA');
$pop_tva->value='';
echo $pop_tva->input();


$cn=new Database(dossier::id());
$id=(isset ($_REQUEST['p_jrn']))?$_REQUEST['p_jrn']:-1;
/*!\brief show a form for quick_writing */
function show_qw_menu($def=0)
{
    echo '<div class="lmenu">';
    $id=(isset ($_REQUEST['p_jrn']))?$_REQUEST['p_jrn']:-1;
    echo ShowItem(
		  array(
			array('?p_action='.$_REQUEST['p_action'].'&'.dossier::get().'&p_jrn='.$id.'&show_form',_('Encoder une Operation'),
			      _('Encoder une operation dans  ce journal'),0),
			array('?p_action='.$_REQUEST['p_action'].'&'.dossier::get().'&p_jrn='.$id.'&sa=l',_('Voir Operation'),
			      _('Voir les operations de ce journal'),1),
			array('?p_action='.$_REQUEST['p_action'].'&'.dossier::get(),
                              _('Autre journal'),
                              _('Choisir un autre journal')
			      )),
                    'H',"mtitle","mtitle",$def,' style="width:80%;margin-left:5;"'
                );

    echo '</div>';
}
/*!\brief show a form for quick_writing */
function show_direct_form($cn,$ledger,$p_array)
{
    echo '<div class="content">';

    $id=(isset ($_REQUEST['p_jrn']))?$_REQUEST['p_jrn']:-1;

    // Show the predef operation
    // Don't forget the p_jrn
    echo '<form method="get">';
    echo HtmlInput::hidden('p_action',$_REQUEST['p_action']);
    echo dossier::hidden();
    echo HtmlInput::hidden('p_jrn',$_REQUEST['p_jrn']);
    $op=new Pre_operation($cn);
    $op->p_jrn=$_REQUEST['p_jrn'];
    $op->od_direct='t';
    if ( $op->count() > 0 )
    {
        echo _("Utilisez une operation ");
        echo $op->show_button();
        echo HtmlInput::submit('use_opd',_('Utilisez une operation'));
    }
    echo '</form>';



    echo '<form method="post" action="?">';
    echo dossier::hidden();
    echo HtmlInput::hidden('p_action',$_REQUEST['p_action']);

    echo $ledger->show_form($p_array);


    echo HtmlInput::button('add',_('Ajout d\'une ligne'),'onClick="quick_writing_add_row()"');

    echo HtmlInput::submit('summary',_('Sauvez'));
    echo '<div class="info">'.
    _('Débit').' = <span id="totalDeb"></span>'.
    _('Crédit').' = <span id="totalCred"></span>'.
    _('Difference').' = <span id="totalDiff"></span></div> ';


    echo '</form>';

    echo "<script>checkTotalDirect();</script>";

    echo create_script(" get_last_date()");

    echo '</div>';

}

$ledger=new Acc_Ledger($cn,$id);

$ledger->with_concerned=true;
// no ledger selected, propose one
// Only ODS
if ($id == -1 )
{
    echo '<div class="content">';

    // Vide
    echo '<FORM method="get" action="?">';
    echo dossier::hidden();
    echo HtmlInput::hidden('p_action',$_REQUEST['p_action']);
    echo $ledger->select_ledger('ODS',3)->input();
    echo HtmlInput::submit('show_form',_('Choix du journal'));
    echo '</form>';
    echo '</div>';
    exit();
}
if ( $User->check_jrn($id) == 'X' )
{
    alert(_("L'acces a ce journal est interdit, \n contactez votre responsable"));
    exit();
}
echo '<div class="content">';
echo '<div style="float:right;margin-right:30">';
echo '<h2 class="info"> '._('Journal').' : '.$ledger->get_name().'</h2>';
echo '</div>';
$sa=( isset ($_REQUEST['sa']))?$_REQUEST['sa']:'';
//======================================================================
// See the ledger listing
if ($sa == 'l' && $id != -1)
{
// Check privilege

    if (  $User->check_jrn($id) == 'X')
    {
        NoAccess();
        exit -1;
    }
    show_qw_menu(1);
    echo '<div class="content">';
    $Ledger=new Acc_Ledger($cn,$id);
    $type=$Ledger->get_type();
    $href=basename($_SERVER['PHP_SELF']);

    echo '<form method="GET" action="'.$href.'">';
    echo HtmlInput::hidden("sa","l");
    echo HtmlInput::hidden("p_jrn",$id);
    echo HtmlInput::hidden("p_action","quick_writing");
    echo dossier::hidden();
    echo $Ledger->search_form($type,0);
    echo HtmlInput::submit("qwlist",_("Recherche"));
    echo '</form>';
    $array=$_GET;
    list($sql,$where)=$Ledger->build_search_sql($array);

    $max_line=$cn->count_sql($sql);

    $step=$_SESSION['g_pagesize'];
    $page=(isset($_GET['offset']))?$_GET['page']:1;
    $offset=(isset($_GET['offset']))?$_GET['offset']:0;
    $bar=jrn_navigation_bar($offset,$max_line,$step,$page);


    echo $bar;
    list($count,$html)= $Ledger->list_operation($sql,$offset,0);
    echo $html;
    echo $bar;
   /*
     * Export to csv
     */
    $r=HtmlInput::get_to_hidden(array('l','date_start','date_end','desc','amount_min','amount_max','qcode','accounting','unpaid','gDossier','ledger_type','p_action'));
    if (isset($_GET['r_jrn'])) {
      foreach ($_GET['r_jrn'] as $k=>$v)
	$r.=HtmlInput::hidden('r_jrn['.$k.']',$v);
    }
    echo '<form action="histo_csv.php" method="get">';
    echo $r;
    echo HtmlInput::submit('viewsearch','Export vers CSV');

    echo '</form>';

    echo '</div>';
    exit();
}

//======================================================================
// User can write ?
// Write into the ledger

if ( $User->check_jrn($id) == 'X' )
{
    alert(_("Vous ne pouvez pas accèder à ce journal, contactez votre responsable"));
    exit -1;
}

if ( $User->check_jrn($id)=='W' )
{
    if ( isset($_GET['show_form']) || isset($_POST['correct_it']) )
    {
        $array=$_POST;
        $default_periode=$User->get_periode();
        /* check if the ledger is closed */
        if ( $ledger->is_closed($default_periode)==1)
        {
            echo '<h2 class="error">'._('Désolé mais cette periode est fermee pour ce journal').'</h2>';
            exit();
        }
        $periode=new Periode($cn);
        list($date,$devnull)=$periode->first_day($default_periode);
        $array['date']=$date;
        show_qw_menu();
        show_direct_form($cn,$ledger,$array);
        exit();
    }

// reload with a predefined operation
//
    if ( isset ($_GET['use_opd']))
    {
        $op=new Pre_op_advanced($cn);
        $p_post=null;
        if ( isset($_REQUEST['pre_def']) && $_REQUEST['pre_def'] != '')
        {
            $op->set_od_id($_REQUEST['pre_def']);
            //$op->p_jrn=$id;

            $p_post=$op->compute_array();
        }
        show_qw_menu();
        show_direct_form($cn,$ledger,$p_post);

        exit();

    }
    if ( isset($_POST['summary']))
    {
        try
        {
            $ledger->verify($_POST );
        }
        catch (Exception $e)
        {
            alert($e->getMessage());
            show_qw_menu();
            show_direct_form($cn,$ledger,$_POST);
            exit();
        }

        echo '<form method="post"  action="?">';
        echo $ledger->show_form($_POST,1);
        echo dossier::hidden();
        echo HtmlInput::hidden('p_action',$_REQUEST['p_action']);

	echo '<hr>';
	$chk=new ICheckBox();
	$chk->selected=false;
	echo $chk->input('opd_save');
	echo "Sauvez cette op&eacute;ration comme modèle d'opération ?";
	echo '<br/>';
	$opd_name=new IText('opd_name');
	echo "Nom du modèle ".$opd_name->input();

	echo '<br/>';
        echo HtmlInput::submit('save_it',"Sauver",'onClick="return verify_ca();"');
        echo HtmlInput::submit('correct_it','Corriger');

        echo '</form>';
        exit();

    }
    if ( isset($_POST['save_it' ]))
    {
        $array=$_POST;

        try
        {
            $ledger->save($array);
            $jr_id=$cn->get_value('select jr_id from jrn where jr_internal=$1',array($ledger->internal));

            echo '<h2> Op&eacute;ration enregistr&eacute;e  Piece '.h($ledger->pj).'</h2>';
            if ( strcmp($ledger->pj,$_POST['e_pj']) != 0 )
            {
                echo '<h3 class="notice">'._('Attention numéro pièce existante, elle a du être adaptée').'</h3>';
            }
            printf ('<a class="detail" style="display:inline" href="javascript:modifyOperation(%d,%d)">%s</a><hr>',
                    $jr_id,dossier::id(),$ledger->internal);

            echo HtmlInput::button_anchor(_('Autre opération dans ce journal'),
                                          "?".dossier::get().
                                              '&show_form'.
                                              '&p_action=quick_writing&p_jrn='.
                                              $_REQUEST['p_jrn']);

        }
        catch (Exception $e)
        {
            alert ($e->getMessage());
            show_qw_menu();
            show_direct_form($cn,$ledger,$_POST);
        }
        exit();
    }
} // if check_jrn=='W'
else
{
    show_qw_menu();
}
