<table class="result">
<tr>
<th>
   <?=_('Lettrage')?>
</th>
<th>
   <?=_('Date')?>
</th>
<th>
   <?=_('Ref')?>
</th>
<th>
   <?=_('Description')?>
</th>
<th>
   <?=_('Montant')?>
</th>
<th>
   <?=_('Debit / Credit')?>
</th>
</tr>

<?php
for ($i=0;$i<count($this->content);$i++):
?>
<tr>
<td> 
<?php
$letter=($this->content[$i]['letter']==-1)?"x":$this->content[$i]['letter'];
$js="this.gDossier=".dossier::id().
  ";this.phpsessid='".$_REQUEST['PHPSESSID']."'".
  ";this.j_id=".$this->content[$i]['j_id'].
  ";this.obj_type='".$this->object_type."'".
  ";dsp_letter(this)";

?> 
<A class="detail" href="javascript:<?=$js?>"><?=$letter?></A>
</td>
<td> <?=$this->content[$i]['j_date_fmt']?> </td>
  <td> <?=h($this->content[$i]['jr_internal'])?> </td>
  <td> <?=h($this->content[$i]['jr_comment'])?> </td>
<td> <?=$this->content[$i]['j_montant']?> </td>
<td> <?=($this->content[$i]['j_debit']=='t')?'D':'C'?> </td>
</tr>

<?php
    endfor;
?>
</table>