<?php require_once('template/ledger_detail_top.php'); ?>
<div class="content" style="padding:0;">
    <?php
    require_once('class_own.php');
    $owner = new Own($cn);
    ?>

    <?php if ($access == 'W') : ?>
        <form class="print" onsubmit="return op_save(this);">
        <?php endif; ?>

        <?php echo HtmlInput::hidden('whatdiv', $div) . HtmlInput::hidden('jr_id', $jr_id) . dossier::hidden(); ?>
        <table style="width:100%">
            <tr><td>
                    <table>
                        <tr>
                            
                            <td></td>
                                <?php
                                $date = new IDate('p_date');
                                $date->value = format_date($obj->det->jr_date);
                                echo td('Date') . td($date->input());
                                ?>
                        </tr>
                        <tr>
                            <td></td>
                                <?php
                                $date_ech = new IDate('p_ech');
                                $date_ech->value = format_date($obj->det->jr_ech);
                                echo td('Echeance') . td($date_ech->input());
                                ?>
                                                    <tr>
                            <td></td>
                            <td>
                                Date paiement
                            </td>
                            <td>
<?php
$date_paid = new IDate('p_date_paid');
$date_paid->value = format_date($obj->det->jr_date_paid);
echo $date_paid->input();
?>
                            </td>
                        </tr>
                        <tr>
                            <td>
<?php
$bk = new Fiche($cn, $obj->det->array[0]['qs_client']);
echo td(_('Client'));

$view_card_detail = HtmlInput::card_detail($bk->get_quick_code(), h($bk->getName()), ' class="line" ');
echo td($view_card_detail);
?>
                            </td>
                        </tr>
                        <tr>
                            <td>
<?php
$itext = new IText('npj');
$itext->value = strip_tags($obj->det->jr_pj_number);
echo td(_('Pièce')) . td($itext->input());
?>
                            </td>
                        <tr>
                            <td>
<?php
$itext = new IText('lib');
$itext->value = strip_tags($obj->det->jr_comment);
$itext->size = 40;
echo td(_('Libellé')) . td($itext->input(), ' colspan="2" ');
?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Payé</td>
                            <td>
<?php
$ipaid = new ICheckBox("ipaid", 'paid');
$ipaid->selected = ($obj->det->jr_rapt == 'paid');
echo $ipaid->input();
?>
                            </td>
                        </tr>

                    </table>
                </td>
                <td style='width:50%'>
                    <table style="width:100%;border:solid 1px yellow">
                        <tr>
                            <td>
                                Note
                            </td></tr>
                        <tr>
                            <td>
<?php
$inote = new ITextarea('jrn_note');
$inote->width = 25;
$inote->heigh = 5;
$inote->value = strip_tags($obj->det->note);
echo $inote->input();
?>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="myfieldset">
            <h1 class="legend">
<?php echo _('Détail') ?>
            </h1>
            <table class="result">
                <?php
                bcscale(2);
                $total_htva = 0;
                $total_tvac = 0;
                echo th(_('Quick Code'));
                echo th(_('Description'));
                echo th(_('Prix/Un'), 'style="text-align:right"');
                echo th(_('Quantité'), 'style="text-align:right"');
                if ($owner->MY_TVA_USE == 'Y')
                    echo th(_('Taux TVA'), 'style="text-align:right"');
                else
                    echo th('');
                if ($owner->MY_TVA_USE == 'Y')
                {
                    echo th(_('HTVA'), 'style="text-align:right"');
                    echo th(_('TVA'), 'style="text-align:right"');
                    echo th(_('TVAC'), 'style="text-align:right"');
                } else
                    echo th(_('Total'), 'style="text-align:right"');

                if ($owner->MY_ANALYTIC != 'nu' && $div == 'popup')
                {
                    $anc = new Anc_Plan($cn);
                    $a_anc = $anc->get_list(" order by pa_id ");
                    $x = count($a_anc);
                    /* set the width of the col */
                    echo '<th colspan="' . $x . '">' . _('Compt. Analytique') . '</th>';

                    /* add hidden variables pa[] to hold the value of pa_id */
                    echo Anc_Plan::hidden($a_anc);
                }

                echo '</tr>';
                for ($e = 0; $e < count($obj->det->array); $e++)
                {
                    $row = '';
                    $q = $obj->det->array[$e];
                    $fiche = new Fiche($cn, $q['qs_fiche']);
                    $view_card_detail = HtmlInput::card_detail($fiche->strAttribut(ATTR_DEF_QUICKCODE), "", ' class="line" ');
                    $row.=td($view_card_detail);
                    if ($owner->MY_UPDLAB == 'Y')
                    {
                        $l_lib = ($q['j_text'] == '') ? $fiche->strAttribut(ATTR_DEF_NAME) : $q['j_text'];
                        $hidden = HtmlInput::hidden("j_id[]", $q['j_id']);
                        $input = new IText("e_march" . $q['j_id'] . "_label", $l_lib);
                        $input->css_size = "100%";
                    } else
                    {
                        $input = new ISpan("e_march" . $q['j_id'] . "_label");
                        $hidden = HtmlInput::hidden("j_id[]", $q['j_id']);
                        $input->value = $fiche->strAttribut(ATTR_DEF_NAME);
                    }

                    $row.=td($input->input() . $hidden);
                    $sym_tva = '';
                    $pu = 0;
                    if ($q['qs_quantite'] != 0)
                        $pu = bcdiv($q['qs_price'], $q['qs_quantite']);
                    $row.=td(nbm($pu), 'class="num"');
                    $row.=td(nbm($q['qs_quantite']), 'class="num"');
                    $sym_tva = '';
                    if ($owner->MY_TVA_USE == 'Y' && $q['qs_vat_code'] != '')
                    {
                        /* retrieve TVA symbol */
                        $tva = new Acc_Tva($cn, $q['qs_vat_code']);
                        $tva->load();
                        $sym_tva = (h($tva->get_parameter('label')));
                        //     $sym_tva=$sym
                    }

                    $row.=td($sym_tva, 'style="text-align:center"');

                    $htva = $q['qs_price'];

                    $row.=td(nbm($htva), 'class="num"');
                    $tvac = bcadd($htva, $q['qs_vat']);
                    if ($owner->MY_TVA_USE == 'Y')
                    {
                        $class = "";
                        if ($q['qs_vat_sided'] != 0)
                        {
                            $class = ' style="text-decoration:line-through"';
                            $tvac = bcsub($tvac, $q['qs_vat']);
                        }
                        $row.=td(nbm($q['qs_vat']), 'class="num"' . $class);
                        $row.=td(nbm($tvac), 'class="num"');
                    }
                    $total_tvac = bcadd($total_tvac, $tvac);
                    $total_htva = bcadd($total_htva, $htva);
                    /* Analytic accountancy */
                    if ($owner->MY_ANALYTIC != "nu" && $div == 'popup')
                    {
                        $poste = $fiche->strAttribut(ATTR_DEF_ACCOUNT);
                        if (preg_match('/^(6|7)/', $poste))
                        {
                            $anc_op = new Anc_Operation($cn);
                            $anc_op->j_id = $q['j_id'];
                            echo HtmlInput::hidden('op[]', $anc_op->j_id);
                            /* compute total price */
                            bcscale(2);

                            $row.=$anc_op->display_table(1, $htva, $div);
                        } else
                        {
                            $row.=td('');
                        }
                    }
                    echo tr($row);
                }
                if ($owner->MY_TVA_USE == 'Y')
                    $row = td(_('Total'), ' style="font-style:italic;text-align:right;font-weight: bolder;" colspan="5"');
                else
                    $row = td(_('Total'), ' style="font-style:italic;text-align:right;font-weight: bolder;" colspan="5"');
                $row.=td(nbm($total_htva), 'class="num" style="font-style:italic;font-weight: bolder;"');
                if ($owner->MY_TVA_USE == 'Y')
                    $row.=td("") . td(nbm($total_tvac), 'class="num" style="font-style:italic;font-weight: bolder;"');
                echo tr($row);
                ?>
            </table>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
        </div>
        <div class="myfieldset">
            <h1 class="legend">
            <?php echo _('Ecritures comptables') ?>
            </h1>
            <?php
            /* if it is not in a popup, the details are hidden */
            if ($div != 'popup')
            {
                $ib = new IButton("a" . $div);
                $ib->label = 'Afficher';
                $ib->javascript = "g('detail_" . $div . "').style.display='block';g('a" . $div . "').style.display='none';";
                echo $ib->input();
                echo '<div id="detail_' . $div . '" style="display:none">';
                $ib = new IButton("h" . $div);
                $ib->label = 'Cacher';
                $ib->javascript = "g('detail_" . $div . "').style.display='none';g('a" . $div . "').style.display='block';";
                echo $ib->input();
            } else
                echo '<div>';

            $detail = new Acc_Misc($cn, $obj->jr_id);
            $detail->get();
            ?>

            <table class="result">
                <tr>
                    <?php
                    echo th(_('Poste Comptable'));
                    echo th(_('Quick Code'));
                    echo th(_('Libellé'));
                    echo th(_('Débit'), ' style="text-align:right"');
                    echo th(_('Crédit'), ' style="text-align:right"');
                    echo '</tr>';
                    for ($e = 0; $e < count($detail->det->array); $e++)
                    {
                        $row = '';
                        $q = $detail->det->array[$e];
                        $view_history = sprintf('<A class="detail" style="text-decoration:underline" HREF="javascript:view_history_account(\'%s\',\'%s\')" >%s</A>', $q['j_poste'], $gDossier, $q['j_poste']);

                        $row.=td($view_history);
                        if ($q['j_qcode'] != '')
                        {
                            $fiche = new Fiche($cn);
                            $fiche->get_by_qcode($q['j_qcode']);
                            $view_history = sprintf('<A class="detail" style="text-decoration:underline" HREF="javascript:view_history_card(\'%s\',\'%s\')" >%s</A>', $fiche->id, $gDossier, $q['j_qcode']);
                        } else
                            $view_history = '';
                        $row.=td($view_history);

                        if ($q['j_qcode'] != '')
                        {
                            // nom de la fiche
                            $ff = new Fiche($cn);
                            $ff->get_by_qcode($q['j_qcode']);
                            $row.=td($ff->strAttribut(h(ATTR_DEF_NAME)));
                        } else
                        {
                            // libellé du compte
                            $name = $cn->get_value('select pcm_lib from tmp_pcmn where pcm_val=$1', array($q['j_poste']));
                            $row.=td(h($name));
                        }
                        $montant = td(nbm($q['j_montant']), 'class="num"');
                        $row.=($q['j_debit'] == 't') ? $montant : td('');
                        $row.=($q['j_debit'] == 'f') ? $montant : td('');
                        echo tr($row);
                    }
                    ?>
            </table>
        </div>
</div>
<?php
require_once('ledger_detail_bottom.php');
?>
</div>
