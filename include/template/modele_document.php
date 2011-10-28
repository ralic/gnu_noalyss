<div style="float:right">
<A style="background-color:blue;color:white;text-decoration:none" HREF="javascript:void(0)" onclick="removeDiv('mod_doc');">Fermer</A>
</div>
<h2 class="info">Modèle de document</h2>
<form  method="post" enctype="multipart/form-data">
<table>

<tr>
<td>Nom du document</td>

<td>
<?php
$a=new IText('md_name');
$a->value=$doc->md_name;
echo $a->input();
?>
</td>
</tr>

<tr>
<td>
Catégorie de document
</td>
<td>
<?php
// Load all the category
	  $w=new ISelect();
     $w->name="md_type";

     $w->value=$cn->make_array('select dt_id,dt_value from document_type');
     $w->selected=$doc->md_type;
     echo $w->input();
?>

</td>
</tr>
<tr>
<td>
Affectation
</td>
<td>
<?php

       $waffect=new ISelect();
        $waffect->name='md_affect';
        $waffect->value=array(
                            array('value'=>'ACH','label'=>_('Uniquement journaux achat')),
                            array('value'=>'VEN','label'=>_('Uniquement journaux vente')),
                            array('value'=>'GES','label'=>_('Partie gestion'))
                        );
       $waffect->selected=$doc->md_affect;
       echo $waffect->input();
?>
</td>
</tr>
<tr>

<tr>
<td>
Fichier
<?php
	        $s=dossier::get();

           echo '<A HREF="show_document_modele.php?md_id='.$doc->md_id.'&'.$s.'">(fichier actuel)</a>';
?>
</td>
<td>
<?php
$file=new IFile('doc');
echo $file->input();


?>
</td>
</tr>

<tr>
<td>
Dernier numéro utilisé pour ce type de document
</td>
<td>
<?php
$last=0;
         if ( $cn->exist_sequence("seq_doc_type_".$doc->md_type) )
         {
             $ret= $cn->get_array("select last_value,is_called from seq_doc_type_".$doc->md_type) ;

             $last=$ret[0]['last_value'];
             /*!
                  *\note  With PSQL sequence , the last_value column is 1 when before   AND after the first call, to make the difference between them
                  * I have to check whether the sequence has been already called or not */
             if ($ret[0]['is_called']=='f' ) $last--;
         }
echo $last;
?>
</td>
</tr>
<tr>
<td>
Redémarrer la séquence (laisser à 0 pour ne pas changer)
</td>
<td>
<?php
$pj=new INum('seq');
$pj->value=0;
echo $pj->input();
?>
</td>
</tr>

</table>
<?php
echo HtmlInput::hidden('p_action','document');
echo dossier::hidden();
echo HtmlInput::hidden('sa','mod_template');
echo HtmlInput::hidden('id',$doc->md_id);
echo HtmlInput::submit("mod",'Sauver');

?>
</form>