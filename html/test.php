
<style type="text/css">
<!--
h2 {
	color:green;
	font-size:20px;
}
.error {
	color:red;
	font-size:20px;
}
-->
</style>
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
/* $Revision*/
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr

include_once("ac_common.php");
include_once("postgres.php");
require_once ('class_dossier.php');
if ( ! file_exists('authorized_debug') ) { 
echo "Pour pouvoir utiliser ce fichier vous devez créer un fichier nomme authorized_debug 
dans le repertoire html du server";
exit();

}
// Test the connection
echo __FILE__.":".__LINE__;
if ( ! isset($_REQUEST['gDossier'])) {
  echo "Vous avez oublie de specifier le gDossier ;)";
  exit();
 }


/* 
require_once('class_plananalytic.php');
PlanAnalytic::testme();



//--------------------------------------------------------------------------------
require_once("class_poste_analytic.php");
Poste_analytique::testme();


require_once ('class_bud_card.php');
Bud_Card::testme();



require_once('class_bud_hypo.php');
Bud_Hypo::testme();


require_once ('class_bud_detail_periode.php');
Bud_Detail_Periode::test_me();



require_once ('class_bud_data.php');
Bud_Data::test_me();



require_once ('class_bud_synthese_hypo.php');
Bud_Synthese_Hypo::test_me();



require_once ('class_bud_synthese_acc.php');
Bud_Synthese_Acc::test_me();

require_once ('class_bud_synthese_anc.php');
Bud_Synthese_Anc::test_me();
*/


require_once ('class_bud_synthese_group.php');
Bud_Synthese_Group::test_me();
