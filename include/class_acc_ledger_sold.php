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
 * \brief class for the sold, herits from acc_ledger
 */
require_once('class_acc_ledger.php');
require_once('class_acc_compute.php');
require_once('class_anc_operation.php');
require_once('user_common.php');

/*!\brief Handle the ledger of sold, 
 *
 *
 */
class  Acc_Ledger_Sold extends Acc_Ledger { 
  function __construct ($p_cn,$p_init) {
    parent::__construct($p_cn,$p_init);
  }
  /*!\brief verify that the data are correct before inserting or confirming
   *\param an array (usually $_POST)
   *\return String
   *\note return an AcException if an error occurs
   */
  public function verify($p_array) {
    extract ($p_array);

    /*  check if the date is valid */
    if ( isDate($e_date) == null ) {
      throw new AcException('Date invalide', 2);
    }

    /* check if the periode is closed */
    if ( $this->is_closed($periode)==1 )
      {
	throw new AcException('Periode fermee',6);
      }

    /* check that the datum is in the choosen periode */
    $per=new Periode($this->db);
    list ($min,$max)=$per->get_date_limit($periode);
    if ( cmpDate($e_date,$min) < 0 ||
	 cmpDate($e_date,$max) > 0) 
	throw new AcException('Date et periode ne correspondent pas',6);
    $fiche=new fiche($this->db);
    $fiche->get_by_qcode($e_client);
    if ( $fiche->empty_attribute(ATTR_DEF_ACCOUNT) == true)
      throw new AcException('La fiche '.$e_client.'n\'a pas de poste comptable',8);

    /* The account exists */
    $poste=new Acc_Account_Ledger($this->db,$fiche->strAttribut(ATTR_DEF_ACCOUNT));
    if ( $poste->load() == false ){
      throw new AcException('Pour la fiche '.$e_client.' le poste comptable ['.$poste->id.'] n\'existe pas',9);
    }


    /* check if amount are numeric and */
    /* check if all card has a ATTR_DEF_ACCOUNT*/
    /* and this account does exist */
    for ($i=0;$i< $nb_item;$i++) {
      if ( strlen(trim(${'e_march'.$i}))== 0) continue;
      if ( isNumber(${'e_march'.$i.'_sell'}) == 0 )
	throw new AcException('La fiche '.${'e_march'.$i}.'a un montant invalide ['.${'e_march'.$i}.']',6);
      if ( isNumber(${'e_quant'.$i}) == 0 )
	throw new AcException('La fiche '.${'e_march'.$i}.'a une quantité invalide ['.${'e_quant'.$i}.']',7);
      $fiche=new fiche($this->db);
      $fiche->get_by_qcode(${'e_march'.$i});
      if ( $fiche->empty_attribute(ATTR_DEF_ACCOUNT) == true)
	throw new AcException('La fiche '.${'e_march'.$i}.'n\'a pas de poste comptable',8);
      /* The account exists */
      $poste=new Acc_Account_Ledger($this->db,$fiche->strAttribut(ATTR_DEF_ACCOUNT));
      if ( $poste->load() == false ){
	throw new AcException('Pour la fiche '.${'e_march'.$i}.' le poste comptable ['.$poste->id.'n\'existe pas',9);
      }
    }
  }

  public function save() {
    echo "<h2> Acc_Ledger_Sold::save Not implemented</h2>";
  }

  /*!\brief insert into the database, it calls first the verify function
   *\param $p_array is usually $_POST or a predefined operation
   *\return string
   *\note throw an AcException
   */
  public function insert($p_array) {
    extract ($p_array);
    $this->verify($p_array) ; 

    $own=new own($this->db);
    $group=NextSequence($this->db,"s_oa_group"); /* for analytic */
    $seq=NextSequence($this->db,'s_grpt');
    $this->id=$p_jrn;
    $internal=$this->compute_internal_code($seq);
    $cust=new fiche($this->db);
    $cust->get_by_qcode($e_client);
    $poste=$cust->strAttribut(ATTR_DEF_ACCOUNT);
    bcscale(4);
    try {
      $tot_amount=0;
      $tot_tva=0;
      StartSql($this->db);
      /* Save all the items without vat */
      for ($i=0;$i< $nb_item;$i++) {
	if ( strlen(trim(${'e_march'.$i})) == 0 ) continue;
	if ( ${'e_march'.$i.'_sell'} == 0 ) continue;
	if ( ${'e_quant'.$i} == 0 ) continue;

	/* First we save all the items without vat */
	$fiche=new fiche($this->db);
	$fiche->get_by_qcode(${"e_march".$i});
	$amount=bcmul(${'e_march'.$i.'_sell'},${'e_quant'.$i});
	$tot_amount+=$amount;
	$acc_operation=new Acc_Operation($this->db);
	$acc_operation->date=$e_date;
	$acc_operation->poste=$fiche->strAttribut(ATTR_DEF_ACCOUNT);
	$acc_operation->amount=$amount;
	$acc_operation->grpt=$seq;
	$acc_operation->jrn=$p_jrn;
	$acc_operation->type='c';
	$acc_operation->periode=$periode;
	$acc_operation->qcode=${"e_march".$i};

	$j_id=$acc_operation->insert_jrnx();

	/* Compute sum vat */
	$oTva=new Acc_Tva($this->db);
	$idx_tva=${'e_march'.$i.'_tva_id'};
	$oTva->set_parameter('id',$idx_tva);
	$oTva->load();
	$op_tva=new Acc_Compute();
	$op_tva->set_parameter("amount",$amount);
	$op_tva->set_parameter('amount_vat_rate',$oTva->get_parameter('rate'));
	$op_tva->compute_vat();
	$tva_item=$op_tva->get_parameter('amount_vat');

	if (isset($tva[$idx_tva] ) )
	  $tva[$idx_tva]+=$tva_item;
	else
	  $tva[$idx_tva]=$tva_item;
	$tot_tva=round(bcadd($tva_item,$tot_tva),2);

	/* Save the stock */
	/* if the quantity is < 0 then the stock increase (return of
	 *  material)
	 */
	$nNeg=(${"e_quant".$i}<0)?-1:1;
		
	// always save quantity but in withStock we can find 
	// what card need a stock management
	
	InsertStockGoods($this->db,$j_id,${'e_march'.$i},$nNeg*${'e_quant'.$i},'c') ;

	if ( $own->MY_ANALYTIC != "nu" )
	  {
	    // for each item, insert into operation_analytique */
	    $op=new Anc_Operation($this->db); 
	    $op->oa_group=$group;
	    $op->j_id=$j_id;
	    $op->oa_date=$e_date;
	    $op->oa_debit=($amount < 0 )?'t':'f';		    
	    echo_debug(__FILE__.':'.__LINE__,"Description is $e_comm");
	    $op->oa_description=FormatString($e_comm);
	    $op->save_form_plan($_POST,$i);
	  }
	/* save into quant_sold */
	$r=ExecSql($this->db,"select insert_quant_sold ".
		   "('".$internal."',".$j_id.",'".${'e_march'.$i}
		   ."',".${'e_quant'.$i}.",".$amount.
		   ",".$tva_item.
		   ",".$idx_tva.",'".$e_client."')");
	
      }      // end loop : save all items
	
    
    /*  save total customer */
    $cust_amount=bcadd($tot_amount,$tot_tva);
    $acc_operation=new Acc_Operation($this->db);
    $acc_operation->date=$e_date;
    $acc_operation->poste=$poste;
    $acc_operation->amount=$cust_amount;
    $acc_operation->grpt=$seq;
    $acc_operation->jrn=$p_jrn;
    $acc_operation->type='d';
    $acc_operation->periode=$periode;
    $acc_operation->qcode=${"e_client"};
    $acc_operation->insert_jrnx();
      
    /* save all vat 
     * $i contains the tva_id and value contains the vat amount
     */
    foreach ($tva as $i => $value) {
      $oTva=new Acc_Tva($this->db);
      $oTva->set_parameter('id',$i);
      $oTva->load();

      $poste_vat=$oTva->get_side('c');
      
      $cust_amount=bcadd($tot_amount,$tot_tva);
      $acc_operation=new Acc_Operation($this->db);
      $acc_operation->date=$e_date;
      $acc_operation->poste=$poste_vat;
      $acc_operation->amount=$value;
      $acc_operation->grpt=$seq;
      $acc_operation->jrn=$p_jrn;
      $acc_operation->type='c';
      $acc_operation->periode=$periode;
      $acc_operation->insert_jrnx();
      
    }
    /* insert into jrn */
    $acc_operation=new Acc_Operation($this->db);
    $acc_operation->date=$e_date;
    $acc_operation->echeance=$e_ech;
    $acc_operation->amount=round($tot_amount+$tot_tva,2);
    $acc_operation->desc=$e_comm;
    $acc_operation->grpt=$seq;
    $acc_operation->jrn=$p_jrn;
    $acc_operation->periode=$periode;
    $acc_operation->insert_jrn();
    ExecSql($this->db,"update jrn set jr_internal='".$internal."' where ".
	    " jr_grpt_id = ".$seq);

    /* Save the attachment */
    if ( isset ($_FILES)) {
      if ( sizeof($_FILES) != 0 )
	save_upload_document($this->db,$seq);
    }

    /* Generate an invoice and save it into the database */
    if ( isset($_POST['gen_invoice'])) {
      echo 'voir le '.$this->create_invoice($internal,$p_array);
      echo '<br>';
    }
      
    }
    catch (Exception $e)
      {
	echo '<span class="error">'.
	  'Erreur dans l\'enregistrement '.
	  __FILE__.':'.__LINE__.' '.
	  $e->getMessage();
	Rollback($this->db);
	exit();
      }
    Commit($this->db);
    return $internal;
  }

  public function update() {
    echo "<h2> Acc_Ledger_Sold::update Not implemented</h2>";
  }

  public function load() {
    echo "<h2> Acc_Ledger_Sold::load Not implemented</h2>";

  }
  /*!\brief Show all the operation, propose a form to select the
   *ledger and the periode
   *\return none
   *\note echo directly, there is no return with the html code
   */
  public function show_ledger() {
    $w=new widget("select");
    $User=new User($this->db); 
    // filter on the current year
    $filter_year=" where p_exercice='".$User->get_exercice()."'";
    
    $periode_start=make_array($this->db,"select p_id,to_char(p_start,'DD-MM-YYYY') from parm_periode $filter_year order by  p_start,p_end",1);
    $current=(isset($_GET['p_periode']))?$_GET['p_periode']:$User->get_periode();
    $w->selected=$current;
    
    echo 'Période  '.$w->IOValue("p_periode",$periode_start);
    $wLedger=$this->select_ledger('VEN',2);
    echo 'Journal '.$wLedger->IOValue();
    echo widget::submit('gl_submit','Valider');
 // Show list of sell
 // Date - date of payment - Customer - amount
    if ( $current == -1) {
      $cond=" and jr_tech_per in (select p_id from parm_periode where p_exercice='".$User->get_exercice()."')";
    } else {
      $cond=" and jr_tech_per=".$current;
    }
    
    $sql=SQL_LIST_ALL_INVOICE.$cond." and jr_def_id=".$this->id ;
    $step=$_SESSION['g_pagesize'];
    $page=(isset($_GET['offset']))?$_GET['page']:1;
    $offset=(isset($_GET['offset']))?$_GET['offset']:0;

    list($max_line,$list)=ListJrn($this->db,$this->id,$sql,null,$offset,1);
    $bar=jrn_navigation_bar($offset,$max_line,$step,$page);
    
    echo "<hr>$bar";
    echo '<form method="POST">';
    echo dossier::hidden();  
    $hid=new widget("hidden");
    

    $hid->name="action";
    $hid->value="voir_jrn";
    echo $hid->IOValue();
    
    

    echo $list;
    if ( $max_line !=0 )
      echo widget::submit('paid','Mise à jour paiement');
    echo '</FORM>';
    echo "$bar <hr>";
    
    echo '</div>';
    
    
  }
  public function delete() {
    echo "<h2> Acc_Ledger_Sold::delete Not implemented</h2>";
  }
  /*!\brief display the form for entering data for invoice
   *\param $p_array is null or you can put the predef operation or the $_POST
   *\return string
   */
  public function display_form($p_array=null) {
    if ( $p_array != null ) extract($p_array);

    $user = new User($this->db);

    // The first day of the periode 
    $periode=new Periode($this->db);
    list ($l_date_start,$l_date_end)=$periode->get_date_limit($user->get_periode());

    $op_date=( ! isset($e_date) ) ?$l_date_start:$e_date;
    $e_ech=(isset($e_ech))?$e_ech:"";
    $e_comm=(isset($e_comm))?$e_comm:"";
    

    $r="";

    $r.=JS_INFOBULLE;
    $r.=JS_SEARCH_CARD;
    $r.=JS_SHOW_TVA;    
    $r.=JS_TVA;
    $r.=JS_AJAX_FICHE;

  
    $r.=dossier::hidden();
    $r.=widget::hidden('phpsessid',$_REQUEST['PHPSESSID']);  
    $r.="<fieldset>";
    $r.="<legend>En-tête facture client  </legend>";
    
    $r.='<TABLE  width="100%">';
    //  Date
    //--
    $Date=new widget("js_date");
    $Date->SetReadOnly(false);
    $Date->table=1;
    $Date->tabindex=1;
    $r.="<tr>";
    $r.=$Date->IOValue("e_date",$op_date,"Date");
    // Payment limit
    //--
    $Echeance=new widget("js_date");
    $Echeance->SetReadOnly(false);
    $Echeance->table=1;
    $Echeance->tabindex=2;
    $label=widget::infobulle(4);
    $r.=$Echeance->IOValue("e_ech",$e_ech,"Echeance ".$label);

    // Periode 
    //--
    $l_user_per=$user->get_periode();
    $l_form_per=FormPeriode($this->db,$l_user_per,OPEN);
    $r.="<td class=\"input_text\">";
    $label=widget::infobulle(3);
    $r.="Période comptable $label</td><td>".$l_form_per;
    $r.="</td>";
    $r.="</tr><tr>";
    // Ledger (p_jrn)
    //--
    $wLedger=$this->select_ledger('VEN',2);

    $wLedger->table=1;
    $wLedger->javascript="onChange='update_predef(\"ven\",\"f\")'";
    $wLedger->label=" Journal ".widget::infobulle(2) ;

    $r.=$wLedger->IOValue();
    // Comment
    //--
    $Commentaire=new widget("text");
    $Commentaire->table=0;
    $Commentaire->SetReadOnly(false);
    $Commentaire->size=80;
    $Commentaire->tabindex=3;
    $label=" Description ".widget::infobulle(1) ;
    $r.="<tr>";
    $r.='<td class="input_text">'.$label.'</td>'.
      '<td colspan="5">'.$Commentaire->IOValue("e_comm",$e_comm)."</td>";
    $r.="</tr>";
    include_once("fiche_inc.php");
    // Display the customer
    //--
    $fiche='deb';
    echo_debug('user_form_ven.php',__LINE__,"Client Nombre d'enregistrement ".sizeof($fiche));
    // Save old value and set a new one
    //--
    $e_client=( isset ($e_client) )?$e_client:"";
    $e_client_label="&nbsp;";//str_pad("",100,".");
  
  
    // retrieve e_client_label
    //--
    $a_client=GetFicheAttribut($this->db,$e_client);
    if ( $a_client != null)   
      $e_client_label=$a_client['vw_name']."  adresse ".$a_client['vw_addr']."  ".$a_client['vw_cp'];
    
    
    $W1=new widget("js_search_only");
    $W1->label="Client ".widget::infobulle(0) ;;
    $W1->name="e_client";
    $W1->tabindex=3;
    $W1->value=$e_client;
    $W1->table=0;
    $W1->extra=$fiche;  // list of card
    $W1->extra2="Recherche";
    $r.='<TR><td colspan="5" >'.$W1->IOValue();
    $client_label=new widget("span");
    $r.=$client_label->IOValue("e_client_label",$e_client_label)."</TD></TR>";
    
    $r.="</TABLE>";
    
    // Record the current number of article
    $Hid=new widget('hidden');
    $p_article= ( isset ($p_article))?$p_article:MAX_ARTICLE;
    $r.=$Hid->IOValue("nb_item",$p_article);
    $e_comment=(isset($e_comment))?$e_comment:"";
    $r.="</fieldset>";
    
    // Start the div for item to sell
    $r.="<DIV>";
    $r.='<fieldset><legend>D&eacute;tail articles vendus</legend>';
    $r.='<TABLE ID="sold_item">';
    $r.='<TR>';
    $r.="<th></th>";
    $label=widget::infobulle(0) ;
    $r.="<th>Code $label</th>";
    $r.="<th>D&eacute;nomination</th>";
    $r.="<th>prix</th>";
    $r.="<th>tva</th>";
    $r.="<th>quantit&eacute;</th>";

    $r.='</TR>';
    // For each article
    //--
    for ($i=0;$i< MAX_ARTICLE;$i++) {
      // Code id, price & vat code
      //--
      $march=(isset(${"e_march$i"}))?${"e_march$i"}:"";
      $march_sell=(isset(${"e_march".$i."_sell"}))?${"e_march".$i."_sell"}:"";
      $march_tva_id=(isset(${"e_march$i"."_tva_id"}))?${"e_march$i"."_tva_id"}:"";
      
      $march_tva_label="";

      $march_label="&nbsp;";
      // retrieve the tva label and name
      //--
      $a_fiche=GetFicheAttribut($this->db, $march);
      if ( $a_fiche != null ) {
	if ( $march_tva_id == "" ) {
	  $march_tva_id=$a_fiche['tva_id'];
	  $march_tva_label=$a_fiche['tva_label'];
	}
	$march_label=$a_fiche['vw_name'];
      }
      // Show input
      //--
      $W1=new widget("js_search_only");
      $W1->label="";
      $W1->name="e_march".$i;
      $W1->value=$march;
      $W1->table=1;
      $W1->extra2="Recherche";
      $W1->extra='cred';  // credits

      $W1->readonly=false;
      $r.="<TR>".$W1->IOValue();
      // For computing we need some hidden field for holding the value
      $r.=widget::hidden('tva_march'.$i,0);      
      $r.=widget::hidden('htva_march'.$i,0);      
      $r.=widget::hidden('tvac_march'.$i,0);      
      $r.="</TD>";
      $Span=new widget ("span");
      $Span->SetReadOnly(false);
      // card's name, price
      //--
      $r.='<TD style="width:60%;border-bottom:1px dotted grey;">'.$Span->IOValue("e_march".$i."_label",$march_label)."</TD>";
      // price
      $Price=new widget("text");
      $Price->SetReadOnly(false);
      $Price->table=1;
      $Price->size=9;
      $Price->javascript="onBlur='compute_sold($i)'";
      $r.=$Price->IOValue("e_march".$i."_sell",$march_sell);
      // vat label
      //--
      $select_tva=make_array($this->db,"select tva_id,tva_label from tva_rate order by tva_rate desc",0);
      $Tva=new widget("select");
      $Tva->javascript="onChange=compute_sold($i)";
      $Tva->table=1;
      $Tva->selected=$march_tva_id;
      $r.=$Tva->IOValue("e_march$i"."_tva_id",$select_tva);
      
      // quantity
      //--
      $quant=(isset(${"e_quant$i"}))?${"e_quant$i"}:"1";
      $Quantity=new widget("text");
      $Quantity->SetReadOnly(false);
      $Quantity->table=1;
      $Quantity->size=9;
      $Quantity->javascript="onChange=compute_sold($i)";
      $r.=$Quantity->IOValue("e_quant".$i,$quant);

      $r.="</tr>";
    }

    
    
    $r.="</TABLE>";

    $r.='<div style="position:float;float:left;text-align:right;padding-right:5px;color:blue">';
    $r.='<br>Total HTVA';
    $r.='<br>Total TVA';
    $r.='<br>Total TVAC';
    $r.="</div>";

    $r.='<div style="position:float;float:left;text-align:left;color:blue">';
    $r.='<br><span id="htva">0.0</span>';
    $r.='<br><span id="tva">0.0</span>';
    $r.='<br><span id="tvac">0.0</span>';
    $r.="</div>";

    $r.="</fieldset>";
    // Set correctly the REQUEST param for jrn_type 
    $r.=widget::hidden('jrn_type','VEN');

    $r.='<INPUT TYPE="BUTTON" NAME="add_item" VALUE="Ajout article" '.
      ' onClick="ledger_sold_add_row(\''.dossier::id().'\',\''.$_REQUEST['PHPSESSID'].'\')"'.     
      ' TABINDEX="32767">';

    $r.='<INPUT TYPE="SUBMIT" NAME="view_invoice" VALUE="Enregistrer" TABINDEX="32767" ID="SubmitButton">';
    $r.="</DIV>";

    $r.=JS_CALC_LINE;
    return $r;
  }
  /*!\brief show the summary of the operation and propose to save it
   *\param array contains normally $_POST. It proposes also to save
   * the Analytic accountancy
   *\return string
   */
  function confirm($p_array) {
    extract ($p_array);
    $this->verify($p_array) ; 

    // to show a select list for the analytic
    // if analytic is op (optionnel) there is a blank line
    $own = new Own($this->db);

    bcscale(4);
    $client=new fiche($this->db);
    $client->get_by_qcode($e_client,true);

    $client_name=$client->getName().
      ' '.$client->strAttribut(ATTR_DEF_ADRESS).' '.
      $client->strAttribut(ATTR_DEF_CP).' '.
      $client->strAttribut(ATTR_DEF_CITY);
    $lPeriode=new Periode($this->db);
    $date_limit=$lPeriode->get_date_limit($periode);
    $r="";
    $r.="<fieldset>";
    $r.="<legend>En-tête facture client  </legend>";
    $r.='<TABLE  width="100%">';
    $r.='<tr>';
    $r.='<td> Date '.$e_date.'</td>';
    $r.='<td>Echeance '.$e_ech.'</td>';
    $r.='<td> Période Comptable '.$date_limit['p_start'].'-'.$date_limit['p_end'].'</td>';
    $r.='<tr>';
    $r.='<td> Journal '.$this->get_name().'</td>';
    $r.='</tr>';
    $r.='<tr>';
    $r.='<td colspan="3"> Description '.$e_comm.'</td>';
    $r.='</tr>';
    $r.='<tr>';
    $r.='<td colspan="3"> Client '.$e_client.':'.$client_name.'</td>';
    $r.='</tr>';
    $r.='</table>';
    $r.='</fieldset>';
    $r.='<fieldset><legend>D&eacute;tail articles vendus</legend>';
    $r.='<table width="100%" border="0">';
    $r.='<TR>';
    $r.="<th>Code</th>";
    $r.="<th>D&eacute;nomination</th>";
    $r.="<th>prix</th>";
    $r.="<th>tva</th>";
    $r.="<th>quantit&eacute;</th>";

    $r.='<th> Montant TVA</th>';
    $r.='<th>Montant HTVA</th>';
    $r.=($own->MY_ANALYTIC!='nu')?'<th>Compt. Analytique</th>':'';
    $r.='</tr>';
    $tot_amount=0.0;
    $tot_tva=0.0;
    for ($i = 0; $i < $nb_item;$i++) {
      if ( strlen(trim(${"e_march".$i})) == 0 ) continue;

      /* retrieve information for card */
      $fiche=new fiche($this->db);
      $fiche->get_by_qcode(${"e_march".$i});
      $fiche_name=$fiche->getName();
      $oTva=new Acc_Tva($this->db);
      $idx_tva=${"e_march".$i."_tva_id"};

      $oTva->set_parameter('id',$idx_tva);
      $oTva->load();
      $op=new Acc_Compute();
      $amount=bcmul(${"e_march".$i."_sell"},${'e_quant'.$i});

      $op->set_parameter("amount",$amount);
      $op->set_parameter('amount_vat_rate',$oTva->get_parameter('rate'));
      $op->compute_vat();
      $tva_item=$op->get_parameter('amount_vat');
      if (isset($tva[$idx_tva] ) )
	$tva[$idx_tva]+=$tva_item;
      else
	$tva[$idx_tva]=$tva_item;
      $tot_amount=round(bcadd($tot_amount,$amount),2);
      $tot_tva=round(bcadd($tva_item,$tot_tva),2);
      $r.='<tr>';
      $r.='<td>';
      $r.=${"e_march".$i};
      $r.='</td>';
      $r.='<TD style="width:60%;border-bottom:1px dotted grey;">';
      $r.=$fiche_name;
      $r.='</td>';
      $r.='<td align="right">';
      $r.=${"e_march".$i."_sell"};
      $r.='</td>';
      $r.='<td align="right">';
      $r.=${"e_quant".$i};
      $r.='</td>';

      $r.='<td align="right">';
      $r.=$oTva->get_parameter('label');
      $r.='</td>';
      $r.='<td align="right">';
      $r.=$tva_item;
      $r.='</td>';
      $r.='<td align="right">';
      $r.=$amount;
      $r.='</td>';

      // encode the pa
      if ( $own->MY_ANALYTIC!='nu') // use of AA
	{
	  // show form
	  $anc_op=new Anc_Operation($this->db);
	  $null=($own->MY_ANALYTIC=='op')?1:0;
	  $r.='<td>';
	  $p_mode=1;
	  $r.=$anc_op->display_form_plan($p_array,$null,$p_mode,$i,$amount);
	  $r.='</td>';
	}
		

      $r.='</tr>';
      
    }


    $r.='</table>';
      if ( $own->MY_ANALYTIC!='nu') // use of AA
	 $r.='<input type="button" value="verifie CA" onClick="verify_ca(\'ok\');">';
    $r.='</fieldset>';
    $r.='<fieldset> <legend>Totaux</legend>';

    $tot=round(bcadd($tot_amount,$tot_tva),2);
    $r.='<div style="position:float;float:right;text-align:left;color:blue">';
    $r.='<br><span id="htva">'.$tot_amount.'</span>';

    foreach ($tva as $i=>$value) {
      $r.='<br>'.$tva[$i];
    }
    $r.='<br><span id="tva">'.$tot_tva.'</span>';
    $r.='<br><span id="tvac">'.$tot.'</span>';
    $r.="</div>";


    $r.='<div style="position:float;float:right;text-align:right;padding-right:5px;color:blue">';
    $r.='<br>Total HTVA';

    foreach ($tva as $i=>$value) {
      $oTva->set_parameter('id',$i);
      $oTva->load();

      $r.='<br>  TVA à '.$oTva->get_parameter('label');
    }
    $r.='<br>Total TVA';
    $r.='<br>Total TVAC';
    $r.="</div>";

    $r.='</fieldset>';
    /*  Add hidden */
    $r.=widget::hidden('e_client',$e_client);
    $r.=widget::hidden('nb_item',$nb_item);
    $r.=widget::hidden('p_jrn',$p_jrn);
    $r.=widget::hidden('periode',$periode);
    $r.=widget::hidden('e_comm',$e_comm);
    $r.=widget::hidden('e_date',$e_date);
    $r.=widget::hidden('e_ech',$e_ech);
    $r.=widget::hidden('jrn_type',$jrn_type);
    for ($i=0;$i < $nb_item;$i++) {
      $r.=widget::hidden("e_march".$i,${"e_march".$i});
      $r.=widget::hidden("e_march".$i."_sell",${"e_march".$i."_sell"});
      $r.=widget::hidden("e_march".$i."_tva_id",${"e_march".$i."_tva_id"});
      $r.=widget::hidden("e_quant".$i,${"e_quant".$i});
    }
    $r.=$this->extra_info();
    return $r;
  }
  /*!\brief the function extra info allows to
   * - add a attachment
   * - generate an invoice
   * - insert extra info 
   *\return string
   */
  public function extra_info() {
    $r="";
    $r.='<fieldset> <legend> Facturation</legend>';
       // check for upload piece
    $file=new widget("file");
    $file->table=0;
    $r.="Pi&egrave;ce justificative";
    $r.=$file->IOValue("pj","");
    $r.='<br>';
    if ( CountSql($this->db,
		  "select md_id,md_name from document_modele where md_type=4") > 0 )
      {

	
	$r.='ou g&eacute;n&eacute;rer une facture <input type="checkbox" name="gen_invoice" CHECKED>';
	// We propose to generate  the invoice and some template
	$doc_gen=new widget("select");
	$doc_gen->name="gen_doc";
	$doc_gen->value=make_array($this->db,
				   "select md_id,md_name from document_modele where md_type=4");
	$r.=$doc_gen->IOValue().'<br>';  
      }
    $obj=new widget('TEXT');
    $r.='Numero de bon de commande : '.$obj->IOValue('bon_comm').'<br>';
    $r.='Autre information : '.$obj->IOValue('other_info').'<br>';

    $r.="</fieldset>";

    return $r; 
  }
  /*!\brief create the invoice and saved it as attachment to the
   *operation, 
   *\param $internal is the internal code
   *\param $p_array is normally the $_POST
   *\return a string
   */
  function create_invoice($internal,$p_array) {
    extract ($p_array);
    $doc=new Document($this->db);
    $doc->f_id=$e_client;
    $doc->md_id=$gen_doc;
    $doc->ag_id=0;
    $str_file=$doc->Generate();
    // Move the document to the jrn
    $doc->MoveDocumentPj($internal);
    // Update the comment with invoice number
    $sql="update jrn set jr_comment='Facture ".$doc->d_number."' where jr_internal='$internal'";
    ExecSql($this->db,$sql);
    return $str_file;
    
  }


  /*!\brief update the payment
   */
  function show_unpaid() {
    // Show list of unpaid sell
    // Date - date of payment - Customer - amount
    // Nav. bar 
    $step=$_SESSION['g_pagesize'];
    $page=(isset($_GET['offset']))?$_GET['page']:1;
    $offset=(isset($_GET['offset']))?$_GET['offset']:0;
    
    
    $sql=SQL_LIST_UNPAID_INVOICE_DATE_LIMIT." and jr_def_id=".$this->id ;
    list($max_line,$list)=ListJrn($this->db,$this->id,$sql,null,$offset,1);
    $sql=SQL_LIST_UNPAID_INVOICE." and jr_def_id=".$this->id ;
    list($max_line2,$list2)=ListJrn($this->db,$this->id,$sql,null,$offset,1);

    // Get the max line
    $m=($max_line2>$max_line)?$max_line2:$max_line;
    $bar2=jrn_navigation_bar($offset,$m,$step,$page);

    echo $bar2;
    echo '<h2 class="info"> Echeance dépassée </h2>';
    echo $list;
    echo  '<h2 class="info"> Non Payée </h2>';
    echo $list2;
    echo $bar2;
    // Add hidden parameter
    $hid=new widget("hidden");

    echo '<hr>';

    if ( $m != 0 )
      echo widget::submit('paid','Mise à jour paiement');


  }
  /*!\brief
   *\param
   *\return
   *\note
   *\see
   *\todo
   */	
  static function test_me() {
  }
  
}




  
