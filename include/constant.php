<?


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

// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
// $Revision$

// securite correspond a la table 
// action
// access aux journaux
define ("ENCJRN",1);
// cr�ation facture
define("FACT",2);
// cr�ation aux fiche
define ("FICHE",3);
// Impression
define ("IMP",4);
//formulaire
define("FORM",5);
// Modif du Plan Comptable
define ("MPCMN",6);
// Gestion des journaux
define ("GJRN",7);
// Gestion des param�tres globaux
define ("PARM",8);
// Gestion de la s�curit�
define ("SECU",9);
// Access � la centralisation
define ("CENTRALIZE",10);
define ("VEN",11);
define ("BQE",12);
define ("ODS",13);
define ("ACH",14);

// Erreur
define ("NOERROR",0);
define ("BADPARM",1);
define ("BADDATE",2);
define ("NOTPERIODE",3);
define ("PERIODCLOSED",4);
define ("INVALID_ECH",5);
define ("RAPPT_ALREADY_USED",6);
define ("RAPPT_NOT_EXIST",7);
define ("DIFF_AMOUNT",8);
define ("RAPPT_NOMATCH_AMOUNT",9);
define ("NO_PERIOD_SELECTED",10);
define ("NO_POST_SELECTED",11);
define ("LAST",1);
define ("FIRST",0);
define ("ERROR",12);

//valeurs standardd
define ("YES",1);
define ("NO",0);
define ("OPEN",1);
define ("CLOSED",0);
define ("NOTCENTRALIZED",3);

// Pour les ShowMenuComptaLeft
define ("MENU_FACT",1);
define ("MENU_FICHE",2);
define ("MENU_PARAM",3);

// for the fiche_inc.GetSqlFiche function
define ("ALL_FICHE_DEF_REF", 1000);

// fixed value for attr_def data
define ("ATTR_DEF_ACCOUNT",5);
define ("ATTR_DEF_NAME",1);
define ("ATTR_DEF_PRIX_ACHAT",7);
define ("ATTR_DEF_PRIX_VENTE",6);
define ("ATTR_DEF_TVA",2);
define ("FICHE_TYPE_CLIENT",9);
define ("FICHE_TYPE_VENTE",1);
define ("ATTR_DEF_ADRESS",14);
define ("ATTR_DEF_CP",15);
define ("ATTR_DEF_PAYS",16);


define ("JS_SEARCH_POSTE","<SCRIPT>function SearchPoste(p_sessid)
     {
       var win=window.open('poste_search.php?PHPSESSID='+p_sessid,'Cherche','toolbar=no,width=600,height=600,scrollbars=yes,resizable=yes');
    } 
	 function GetIt() {
	   window.close();	
	} 
	</SCRIPT>"
);
define ("JS_SHOW_TVA","<SCRIPT>function ShowTva(p_sessid)
     {
       var win=window.open('show_tva.php?PHPSESSID='+p_sessid,'Montre','scrollbar,toolbar=no,width=300,height=300,resizable=yes');
    } 
	 function GetIt() {
	   window.close();	
	} 
	</SCRIPT>"
);

define ("JS_VIEW_JRN_DETAIL","<script>function viewDetail(p_value,p_sessid)
		{
			var win=window.open('jrn_op_detail.php?jrn_op='+p_value+'&PHPSESSID='+p_sessid,'Cherche','toolbar=no,width=400,height=400,scrollbars=yes,resizable=yes');
		}
	</script>");

// Sql string
define ("SQL_LIST_ALL_INVOICE","");
define ("SQL_LIST_UNPAID_INVOICE"," where (jr_rapt is null or jr_rapt = '')"); 
define ("SQL_LIST_UNPAID_INVOICE_DATE_LIMIT" ," where (jr_rapt is null or jr_rapt = '') 
                       and to_date(to_char(jr_ech,'DD.MM.YYYY'),'DD.MM.YYYY') < to_date(to_char(now(),'DD.MM.YYYY'),'DD.MM.YYYY')");
?>
