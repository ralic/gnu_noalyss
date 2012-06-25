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

/**
 * @file
 * @brief show all the categories of card fiche_def
 *
 */
$max=Database::num_row($res);
?>
<div id="list_cat_div" class="content">
<table class="result">
	<tR>
		<th>
			<?=$tab->get_header(0)?>
		</th>
		<th>
			<?=$tab->get_header(1)?>
		</th>
		<th>
			<?=$tab->get_header(2)?>
		</th>
		<th>
			<?=$tab->get_header(3)?>
		</th>
	</tR>
<?
$dossier=Dossier::id();
for ($i=0;$i<$max;$i++):
	$row=Database::fetch_array($res, $i);
?>
	<tr>
		<td>
		<?=HtmlInput::anchor(h($row['fd_label']), "javascript:void(0)", "onclick=\"detail_category_show('detail_category_div','".$dossier."','".$row['fd_id']."')\"")?>
		</td>
		<td>
			<?=h($row['fd_class_base'])?>
		</td>
		<td>
			<?
			 $v=($row['fd_create_account']=='t')?"Automatique":"Manuel";
			 echo $v;
			?>
		</td>
		<td>
			<?=$row['frd_text']?>
		</td>
	</tr>


<?
endfor;
?>
</table>
<?
echo HtmlInput::button("cat_fiche_def_add","Ajout d'une nouvelle catégorie", "onclick=\"detail_category_show('detail_category_div','".$dossier."','-1')\"");
?>
</div>
<div id="detail_category_div" style="display:none"">

</div>