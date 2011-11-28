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

// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
// $Revision$
/*!\file
 * \brief contains function for the printing
 * \todo the functions of class_impress.php should be replaced in a OO way
*/
require_once('class_periode.php');


class Impress
{
    /*! \brief   Purpose Parse a formula
     *
     * \param $p_cn connexion
     * \param $p_label
     * \param $p_formula
     * \param $p_eval  true if we eval here otherwise the function returns
     *                 a string which must be evaluated
     * \param $p_type_date : type of the date 0 for accountant period or 1
     * for calendar
     * \return array
     *
     *
     */
    static function parse_formula($p_cn,$p_label,$p_formula,$p_start,$p_end,$p_eval=true,$p_type_date=0)
    {

        if ( Impress::check_formula($p_formula) == false)
        {
            if ( $p_eval == true)
                return array('desc'=>$p_label.'  Erreur Formule!',
                             'montant'=>0);
            else
                return $p_formula;

        }
        if ( $p_type_date == 0 )
            $cond=sql_filter_per($p_cn,$p_start,$p_end,'p_id','j_tech_per');
        else
            $cond="( j_date >= to_date('$p_start','DD.MM.YYYY') and j_date <= to_date('$p_end','DD.MM.YYYY'))";
        include_once("class_acc_account_ledger.php");

        //    while (@ereg("(\[[0-9]*%*D*C*S*\])",$p_formula,$e) == true)
        while (preg_match_all("(\[[0-9]*%*D*C*S*\])",$p_formula,$e) == true)
          {

            // remove the [ ]
            $x=$e[0];
            foreach ($x as $line)
              {
                $compute='all';
                if ( strpos($line,'D') != 0 )
                  $compute='deb';
                if ( strpos($line,'C') != 0 )
                  $compute='cred';
                if ( strpos($line,'S') != 0 )
                  $compute='signed';
                $line=str_replace ("[","",$line);
                $line=str_replace ("]","",$line);
                $line=str_replace ("D","",$line);
                $line=str_replace ("C","",$line);
                $line=str_replace ("S","",$line);
                // If there is a FROM clause we must recompute
                // the time cond

                if ($p_type_date == 0 && preg_match ("/FROM=[0-9]+\.[0-9]+/", $p_formula,$afrom) == 1 )
                  {
                    // There is a FROM clause
                    // then we must modify the cond for the periode
                    $from=str_replace("FROM=","",$afrom[0]);

                    // Get the periode
                    /*! \note special value for the clause FROM=00.0000
                     */
                    if ( $from == '00.0000' )
                      {

                        // retrieve the first month of this periode
                        $User=new User($p_cn);
                        $user_periode=$User->get_periode();
                        $oPeriode=new Periode($p_cn);
                        $periode=$oPeriode->get_exercice($user_periode);
                        list($first,$last)=$oPeriode->get_limit($periode);
                        $ret=$first->get_date_limit();
                        $end_date=$oPeriode->get_date_limit($p_end);
                        if ($ret == null ) throw new Exception ('Pas de limite à cette période',1);
                        $cond=sql_filter_per($p_cn,$ret['p_start'],$end_date['p_end'],'date','j_tech_per');


                      }
                    else
                      {
                        $oPeriode=new Periode($p_cn);
                        try
                          {
                            $from=$oPeriode->find_periode('01'.$from);
                          }
                        catch (Exception $exp)
                          {
                            /* if none periode is found
                               then we take the first periode of the year
                            */
                            $User=new User($p_cn);
                            $user_periode=$User->get_periode();

                            $year=$oPeriode->get_exercice($user_periode);
                            list($first,$last)=$oPeriode->get_limit($year);
                            $ret=$first->get_date_limit();
                            $end_date=$oPeriode->get_date_limit($p_end);
                            if ($ret == null ) throw new Exception ('Pas de limite à cette période',1);
                            $cond=sql_filter_per($p_cn,$ret['p_start'],$end_date['p_end'],'date','j_tech_per');
                          }
                      }
                  }

                if ( strpos($p_formula,"FROM") != 0)
                  {
                    // We remove FROM out of the p_formula
                    $p_formula=substr_replace($p_formula,"",strpos($p_formula,"FROM"));
                  }

                // Get sum of account
                $P=new Acc_Account_Ledger($p_cn,$line);
                $detail=$P->get_solde_detail($cond);


                if ( $compute=='all')
                  $i=$detail['solde'];
                if ( $compute=='deb')
                  $i=$detail['debit'];
                if ( $compute=='cred')
                  $i=$detail['credit'];
                if ( $compute=='signed')
                  $i=$detail['debit']-$detail['credit'];
                $p_formula=str_replace($x[0],$i,$p_formula);
              }
          }

        // $p_eval is true then we eval and returns result
        if ( $p_eval == true)
        {
            $p_formula="\$result=".$p_formula.";";

            eval("$p_formula");

            while (preg_match("/\[([0-9]+)([Tt]*)\]/",trim($p_label),$e) == 1)
            {
                $nom = "!!".$e[1]."!!";
                if (Impress::check_formula($e[0]))
                {
                    $nom = $p_cn->get_value ( "SELECT pcm_lib AS acct_name FROM tmp_pcmn WHERE pcm_val::text LIKE $1||'%' ORDER BY pcm_val ASC LIMIT 1",array($e[1]));
                    if($nom)
                    {
                        if($e[2] == 'T') $nom = strtoupper($nom);
                        if($e[2] == 't') $nom = strtolower($nom);
                    }
                }
                $p_label = str_replace($e[0], $nom, $p_label);
            }

            $aret=array('desc'=>$p_label,
                        'montant'=>$result);
            return $aret;
        }
        else
        {
            // $p_eval is false we returns only the string
            return $p_formula;
        }
    }
    /*!
     * \brief  Check if formula doesn't contain
     *           php injection
     * \param string
     *
     * \return true if the formula is good otherwise false
     */
    static function check_formula($p_string)
    {
        // the preg_match gets too complex if we want to add a test
        // for parenthesis, math function...
        // So I prefer remove them before testing
        $p_string=str_replace("round","",$p_string);
        $p_string=str_replace("abs","",$p_string);
        $p_string=str_replace("(","",$p_string);
        $p_string=str_replace(")","",$p_string);
        // for  the inline test like $a=(cond)?value:other;
        $p_string=str_replace("?","+",$p_string);
        $p_string=str_replace(":","+",$p_string);
        $p_string=str_replace(">=","+",$p_string);
        $p_string=str_replace("<=","+",$p_string);
        $p_string=str_replace(">","+",$p_string);
        $p_string=str_replace("<","+",$p_string);
        // eat Space
        $p_string=str_replace(" ","",$p_string);
        // Remove D/C/S
        $p_string=str_replace("C","",$p_string);
        $p_string=str_replace("D","",$p_string);
        $p_string=str_replace("S","",$p_string);
        // Remove T,t
        $p_string=str_replace("T","",$p_string);
        $p_string=str_replace("t","",$p_string);

        if ( @ereg ("^(\\$[a-zA-Z]*[0-9]*=){0,1}((\[{0,1}[0-9]+\.*[0-9]*%{0,1}\]{0,1})+ *([+-\*/])* *(\[{0,1}[0-9]+\.*[0-9]*%{0,1}\]{0,1})*)*(([+-\*/])*\\$([a-zA-Z])+[0-9]*([+-\*/])*)* *( *FROM=[0-9][0-0].20[0-9][0-9]){0,1}$",$p_string) == false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
     /**
     * with the handle of a successull query, echo each row into CSV and
     * send it directly
     * @param type $array of data
     * @param type $aheader  double array, each item of the array contains
     * a key type (num) and a key title
     */
    static function array_to_csv($array,$aheader)
    {
        $seq="";
        for ($i=0;$i<count($aheader);$i++)
        {
            echo $seq.'"'.$aheader[$i]['title'].'"';
            $seq=";";
        }
        printf("\r");

        $seq="";
        // fetch all the rows
        for ($i=0;$i<count($array);$i++)
        {
            $row=$array[$i];
            $sep2="";
			$e=0;
            // for each rows, for each value
            foreach ($array[$i] as $key=>$value)
            {
				if ($e > count($aheader)) $e=0;

				if ( isset ($aheader[$e]['type']))
				{
					switch ($aheader[$e]['type'])
					{
						case 'num':
							echo $sep2.nb($value);
							break;
						default:
							echo $sep2.'"'.$value.'"';
					}
				} else {
					echo '"'.$value.'"'.$sep2;
				}
                $sep2=";";$e++;
            }
            printf("\r");
        }
    }
}