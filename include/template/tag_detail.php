<?php
echo Dossier::hidden();
echo HtmlInput::hidden('t_id',$data->t_id);
echo HtmlInput::hidden('ac',$_REQUEST['ac']);
$uos=new Tool_Uos('tag');
echo $uos->hidden();
$t_tag=new IText('t_tag',$data->t_tag);
$t_description=new ITextarea('t_description',$data->t_description);
$t_description->style=' class="itextarea" style="width:50em;height:5em;vertical-align: top;"';
?>
<p>
    Nom de Dossier (tag) : <?php echo $t_tag->input(); ?>
</p>
<p>
Description (tag) : <?php echo $t_description->input(); ?>
</p>
<?php
// If exist you can remove it
if ( $data->t_id != '-1') : 
?>
<p>Cochez pour cette case pour effacer ce tag<input type="checkbox" name="remove">
</p>

<?php
endif;
?>