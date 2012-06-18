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
 * @brief show State of the stock
 *
 */
?>
<div class="content">
<table class="result">
	<tr>
		<th>
			Code Stock
		</th>
		<? for ($i = 0; $i < count($a_repository); $i++):?>
			<th>
				<?=h( $a_repository[$i]['r_name'])?>
			</th>
		<? endfor;?>
			<th>
				Total
			</th>
	</tr>
	<?
	for ($x = 0; $x < count($a_code); $x++):
		$class=($x%2==0)?' class="odd" ':' class="even" ';
		?>

		<tr <?=$class?> >
			<td>
				<?=HtmlInput::card_detail($a_code[$x]['sg_code'])?>
			</td>
			<?
			$n_in=0;$n_out=0;
			for ($e = 0; $e < count($a_repository); $e++):

				$array = $cn->get_array("select * from tmp_stockgood_detail where r_id=$1 and sg_code=$2 and s_id=$3"
						, array($a_repository[$e]['r_id'], $a_code[$x]['sg_code'],$tmp_id));
				?>
			<td>
				<?
					if (count($array)==0):
						echo 0;
					else:
						$n_in+=$array[0]['s_qin'];
						$n_out+=$array[0]['s_qout'];
						?>
						<table>
							<tr>
								<td>
									IN  :
								</td>
								<td class="num">
									<?=nbm($array[0]['s_qin'])?>
								</td>
							</tr>
							<tr>
								<td>
									OUT  :
								</td>
								<td class="num">
									<?=nbm($array[0]['s_qout'])?>
								</td>
							</tr>
							<tr>
								<td>
									DIFF  :
								</td>
								<td class="num">
									<?=nbm((bcsub($array[0]['s_qin'],$array[0]['s_qout'])))?>
								</td>
							</tr>
						</table>
						<?
					endif;
				?>
 			</td>
				<?
			endfor;  // loop e
			?>
			<td>
<table>
							<tr>
								<td>
									IN  :
								</td>
								<td class="num">
									<?=nbm($n_in)?>
								</td>
							</tr>
							<tr>
								<td>
									OUT  :
								</td>
								<td class="num">
									<?=nbm($n_out)?>
								</td>
							</tr>
							<tr>
								<td>
									DIFF  :
								</td>
								<td class="num">
									<?=nbm((bcsub($n_in,$n_out)))?>
								</td>
							</tr>
						</table>
			</td>
		</tr>
		<?
	endfor; // loop x
	?>
</table>
</div>