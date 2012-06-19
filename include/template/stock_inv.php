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
 * @brief show the input
 *
 */
?>
<div class="content">
	<form method="POST" onsubmit="return confirm('Vous confirmez ?')">
<table>
	<tr><td>
			Date
		</td>
		<td>
			<?=$date->input()?>
		</td>
	</tr>
	<tr>
		<td>
			Dépot
		</td>
		<td>
			<?=$idepo->input()?>
		</td>
	</tr>
	<tr>
		<td>
			Motif de changement
		</td>
		<td>
			<?=$motif->input()?>
		</td>
	</tr>
</table>
<table style="width: 80%">
	<tr>
		<th >
			Code Stock
		</th>
		<th style="text-align:right">
			Quantité
		</th>
	</tr>
<? for($i=0;$i<MAX_ARTICLE;$i++): ?>
	<tr>
		<td>
			<?=$sg_code[$i]->input()?>
			<?=$sg_code[$i]->search()?>
			<?=$label[$i]->input()?>
		</td>
		<TD class="num"">
			<? if ($sg_quantity[$i]->value==0 && $p_readonly==true):?>

			<? else : ?>
			<?=$sg_quantity[$i]->input()?>
			<? endif;?>
		</td>
	</tr>
<? endfor; ?>
</table>
<? if ($p_readonly == false) echo HtmlInput::submit('save','Sauver')?>
	</form>
</div>