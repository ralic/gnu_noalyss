<form method="post">

<table>

<?php
for ($i=0;$i<count($aList);$i++) :
  $row=$aList[$i];
?>

<tr id="row<?=$row['dt_id']?>">
<td colspan="2">
<?=$row['dt_value'];?>
</td>
<td>
<?=$row['js_remove'];?>
</td>
</tr>
<?
endfor;
?>
<tr>
<td>
<?=$str_addCat?>
</td>
<td>
   <?=$str_submit?>
</td>
</tr>

</table>
<?
echo HtmlInput::phpsessid().dossier::hidden();
echo HtmlInput::hidden('p_action',$_REQUEST['p_action']);
echo HtmlInput::hidden('sa',$_REQUEST['sa']);
?>
</form>