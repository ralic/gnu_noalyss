<fieldset id="asearch" style="height:88%">
   <legend><? echo _('Résultats'); ?></legend>
<div style="height:88%;overflow:auto;">
<table width="100%">
<? for ($i=0;$i<sizeof($array);$i++) : ?>
<tr>
<td>
<a href="javascript:void(0)" class="one" onclick="<?=$array[$i]['javascript']?>">
<?=$array[$i]['quick_code']?>
</a>
</td>
<td>
<?=$array[$i]['name']?>
</td>
<td>
<?=$array[$i]['description']?>
</td>

</tr>


<? endfor; ?>
</table>
<span style="background-color:#9FFFF1;border:1px solid black;padding:2px">
   <? echo _("Nombre d'enregistrements:$i"); ?>
</span>
<br>
</div>
</fieldset>
