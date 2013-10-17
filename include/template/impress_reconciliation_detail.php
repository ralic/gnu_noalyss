<table class="result">
    <tr>
        <th>
            
        </th>
         <th style="text-align:right">
            Prix HTVA
        </th>
        <th style="text-align:right">
            Non Deductible
        </th>
        <th style="text-align:right" >
            code tva
        </th>
        <th style="text-align:right">
            TVA
        </th>
        <th style="text-align:right">
            Tva ND
        </th>
        <th style="text-align:right">
            TVAC
        </th>
    </tr>
<?php 
$nb_record=Database::num_row($p_ret);
bcscale(2);
$tot_cum_price=0;
$tot_cum_vat=0;
$tot_cum_nd=0;
$tot_cum_nd_tva=0;
$tot_cum_tvac=0;
for ($i=0;$i < $nb_record;$i++)    :
    $row=Database::fetch_array($p_ret,$i);
    $tot_cum_price=bcadd($tot_cum_price,$row['price']);
    
    $tot_cum_vat=bcadd($tot_cum_vat,$row['vat_amount']);
    

?>    

    <tr>
        <td>
            
        </td>
        <td class="num">
          <?php  echo nbm($row['price']); ?>
        </td>
        <td class="num">
            <?php  $tot_price=bcadd($row['nd_amount'],$row['dep_priv']); 
            $tot_cum_nd=bcadd($tot_cum_nd,$tot_price);
            echo nbm($tot_price); ?>  
        </td>
        <td class="num">
          <?php  echo $row['tva_label']; ?>
        </td>
        <td class="num">
          <?php  echo nbm($row['vat_amount']); ?>
        </td>
        <td class="num">
          <?php  $tot_vat=bcadd($row['nd_tva'],$row['nd_tva_recup']); 
          $tot_cum_nd_tva=bcadd($tot_cum_nd_tva,$tot_vat);
          echo nbm($tot_vat); ?>  
        </td>
        <td class="num">
            <?php 
            $tot=bcadd($tot_vat,$row['price']);
            $tot=bcadd($tot,$row['vat_amount']);
            $tot_cum_tvac=bcadd($tot_cum_tvac,$tot);
            echo nbm($tot);
            ?>
        </td>    
    </tr>
<?php
          endfor;
?>
    <tfoot>
    <td>
        Totaux
    </td>
    <td class="num">
        <?php echo nbm($tot_cum_price); ?>
    </td>
    <td class="num">
        <?php echo nbm($tot_cum_nd); ?>
    </td>
    <td>
        
    </td>
    <td class="num">
        <?php echo nbm($tot_cum_vat); ?>
    </td>
    <td class="num">
        <?php echo nbm($tot_cum_nd_tva); ?>
    </td>
    <td class="num">
        <?php 
        echo nbm($tot_cum_tvac); ?>
    </td>
    </tfoot>
</table>        