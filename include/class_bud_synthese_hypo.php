
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

/*!\file 
 */
/*!
 * \brief Manage the hypothese for the budget module
 *  synthese
 */
require_once ('class_bud_synthese.php');

class Bud_Synthese_Hypo extends Bud_Synthese {
/*   function __construct($p_cn) { */
/*     echo "constructor ".__FILE__; */
/*   } */
  static function make_array($p_cn) {
    $a=make_array($p_cn,'select bh_id, bh_name from bud_hypothese '.
		  ' where pa_id is not null order by bh_name');
    return $a;
  }

  function form() {
    $wSelect = new widget('select');
    $wSelect->name='bh_id';
    $wSelect->value=Bud_Synthese_Hypo::make_array($this->cn);

    $r="Hypoth&egrave;se :".$wSelect->IOValue();

    $per=make_array($this->cn,"select p_id,to_char(p_start,'MM.YYYY') ".
		    " from parm_periode order by p_start,p_end");

    $wFrom=new widget('select');
    $wFrom->name='from';
    $wFrom->value=$per;

    $wto=new widget('select');
    $wto->name='to';
    $wto->value=$per;
    $r.="Periode de ".$wFrom->IOValue()." &agrave; ".$wto->IOValue();
    $r.=dossier::hidden();
    return $r;
  }
  /*!\brief load the data from the database and return the result an
     array
     \return Array
     (
     [6510] => Array
     (
     [GROUPE1] => 0
     [GROUPE3] => 74.0000
     )
     
     [6040001] => Array
     (
     [GROUPE1] => 83.7000
     [GROUPE3] => 78.0000
     )
     
     [6040002] => Array
     (
     [GROUPE1] => 48.0000
     [GROUPE3] => 0
     ) 
*/
  function load() {
    $per=sql_filter_per($this->cn,$this->from,$this->to,'p_id','p_id');
    $sql_poste="select distinct pcm_val from bud_detail where bh_id=".$this->bh_id;
    $aPoste=get_array($this->cn,$sql_poste);

    $cn=DbConnect(dossier::id());
    $sql_prepare=pg_prepare($cn,"get_group","select sum(bdp_amount) as amount,ga_id,".
			    "bc_price_unit".
			    " from ".
			    " bud_detail join bud_detail_periode using (bd_id) ".
			    " join bud_card using (bc_id) ".
			    " join poste_analytique using (po_id) ".
			    " where $per ".
			    " and pcm_val=$1 and ".
			    " bud_detail.bh_id=$2 ".
			    " group by ga_id,bc_price_unit ".
			    " order by ga_id ");
    $array=array();
    // Now we put 0 if there is nothing for a group
    $aGroup=get_array($this->cn,"select distinct ga_id from bud_detail join poste_analytique ".
		      " using (po_id) where bh_id=".$this->bh_id." order by ga_id ");


    // foreach poste get all the value of the group
    foreach ($aPoste as $rPoste) {
      $pcm_val=$rPoste['pcm_val'];
      $line=array();
      $res=pg_execute("get_group",array($pcm_val,$this->bh_id));
      $row=pg_fetch_all($res);
      if ( empty ($row) ) continue;
      // initialize all groupe to 0
      foreach ($aGroup as $rGroup) {
	$sGroup=$rGroup['ga_id'];
	$line[$sGroup]=0;
      }
      foreach ($row as $col ) {
	$groupe=$col['ga_id'];
	$line[$groupe]=$col['amount']*$col['bc_price_unit'];
      }
      $array[$pcm_val]=$line;
    }
    pg_close($cn);

    return $array;
  }
  static function test_me() {
    $cn=DbConnect(dossier::id());
    $obj=new Bud_Synthese_Hypo($cn);
    echo '<form method="GET">';
    echo $obj->form();
    echo widget::submit_button('recherche','Recherche');
    echo '</form>';
    print_r($_GET);
    if ( isset ($_GET['recherche'])) {
      $obj->from_array($_GET);
      print_r( $obj->load());
    }
  }
}
