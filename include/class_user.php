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
/* $Revision$ */
// Copyright Author Dany De Bontridder ddebontridder@yahoo.fr
/* Class user 
 **************************************************
 * Purpose : 
 *   Data & function about connected users
 */
include_once("constant.php");

class cl_user {
  var $id;
  var $pass;
  var $db;
  var $admin;
  var $valid;

  function cl_user ($p_cn){
    $this->id=$_SESSION['g_user'];
    $this->pass=$_SESSION['g_pass'];
    $this->valid=(isset ($_SESSION['isValid']))?1:0;
    $this->db=$p_cn;
    if ( isset($_SESSION['use_usertype']) )
      $this->type=$_SESSION['use_usertype'];
    if ( isset($_SESSION['use_theme']) )
      $this->theme=$_SESSION['use_theme'];
    
    $this->admin=( isset($_SESSION['use_admin']) )?$_SESSION['use_admin']:0;
    
    if ( isset($_SESSION['use_name']) )
      $this->name=$_SESSION['use_name'];
    if ( isset($_SESSION['use_first_name']) )
      $this->first_name=$_SESSION['use_first_name'];

  }
  /*++ 
   * function : CheckUser
   * Parameter : none
   * return    : not use
   * Description :
   * Check if user is active and exists in the
   * repository
   * Automatically redirect
   * 
   ++*/
  function Check()
  {
	
	$res=0;
	$pass5=md5($this->pass);
      	//if ( $this->valid == 1 ) { return; }
	$cn=DbConnect();
	if ( $cn != false ) {
	  $sql="select ac_users.use_login,ac_users.use_active, ac_users.use_pass,
                       use_usertype,use_theme,use_admin,use_first_name,use_name
				from ac_users  
				 where ac_users.use_login='$this->id' 
					and ac_users.use_active=1
					and ac_users.use_pass='$pass5'";
	    echo_debug(__FILE__,__LINE__,"Sql = $sql");
	    $ret=pg_exec($cn,$sql);
	    $res=pg_NumRows($ret);
	    echo_debug(__FILE__,__LINE__,"Number of found rows : $res");
	    if ( $res >0 ) {
	      $r=pg_fetch_array($ret,0);
	      $_SESSION['use_usertype']=$r['use_usertype'];
	      $_SESSION['use_theme']=$r['use_theme'];
	      $_SESSION['use_admin']=$r['use_admin'];
	      $_SESSION['use_name']=$r['use_name'];
	      $_SESSION['use_first_name']=$r['use_first_name'];
	      
	      $this->type=$_SESSION['use_usertype'];
	      $this->theme=$_SESSION['use_theme'];
	      $this->admin=$_SESSION['use_admin'];
	      $this->name=$_SESSION['use_name'];
	      $this->first_name=$_SESSION['use_first_name'];
	    }
	  }
	  
	if ( $res == 0  ) {
	  	echo '<META HTTP-EQUIV="REFRESH" content="4;url=index.html">';
		echo "<BR><BR><BR><BR><BR><BR>";
		echo "<P ALIGN=center><BLINK>
			<FONT size=+12 COLOR=RED>
			Invalid user <BR> or<BR> Invalid password 
			</FONT></BLINK></P></BODY></HTML>";
		session_unset();
		
		exit -1;			
	} else {
	  $this->valid=1;
	}
	
	return $ret;
	
  }

  function getJrn() {
  }
/* function CheckAdmin
 **************************************************
 * Purpose : Check if an user is an admin
 *        
 * parm : 
 *	- none
 * gen : 
 *	- none 
 * return: 1 for yes 0 for no
 */
  function Admin() {
    $res=0;
    
    if ( $this->id != 'phpcompta') {
      $pass5=md5($this->pass);
      $sql="select use_id from ac_users where use_login='$this->id'
		and use_active=1 and use_admin=1 and use_pass='$pass5'";
      
      $cn=DbConnect();
      
      $this->admin=CountSql($cn,$sql);
    } else $this->admin=1;
    
    return $this->admin;
  }
  function AccessJrn($p_cn,$p_jrn_id) {
    $this->Admin();
    if ( $this->admin==1) return true;
    $sql=CountSql($p_cn,"select uj_id 
                             from user_sec_jrn 
                             where
                             uj_priv in ('R','W')
                             and uj_jrn_id=".$p_jrn_id.
		  "  and uj_login = '".$this->id."'");
    if ( $sql != 0 ) return true;
    return false;
        
  }
/* function SetPeriode
 * Purpose : Set the selected periode in the user's preferences
 * 
 * parm : 
 
 *      - $p_periode 
 *      - $p_user
 * gen :
 *	- none
 * return:
 *	- none
 *
 */ 
function SetPeriode($p_periode) {
  $sql="update user_pref set pref_periode=$p_periode where pref_user='$this->id'";
  $Res=ExecSql($this->db,$sql);
}
/* function GetPeriode
 * Purpose : Get the default periode from the user's preferences
 * 
 * parm : 
 *	- $p_cn connexion 
 *      - $p_user
 * gen :
 *	- none
 * return:
 *	- the default periode
 *
 */ 

function GetPeriode() {
  $array=$this->GetPreferences();
  return $array['active_periode'];
}
/* function GetPreferences
 * Purpose : Get the default user's preferences
 * 
 * parm : 
 *	- $p_cn connexion 
 *      - $p_user
 * gen :
 *	- none
 * return:
 *	- none
 *
 */ 
function GetPreferences ()
{
  // si preference n'existe pas, les cr�er
  $sql="select pref_periode as active_periode from user_pref where pref_user='".$this->id."'";
  $Res=ExecSql($this->db,$sql);
  if (pg_NumRows($Res) == 0 ) {
    $sql=sprintf("insert into user_pref (pref_periode,pref_user) values 
		 ( %d , '%s')" ,
		 1, $this->id);
    $Res=ExecSql($this->db,$sql);

    $l_array=$this->GetPreferences();
  } else {
    $l_array= pg_fetch_array($Res,0);
  }
  return $l_array;
}
/* function CheckAction
 * Purpose : Check if an user is allowed to do an action
 * 
 * parm : 
 *	- p_dossier dossier id
 *      - p_login   user's login
 *      - p_action_id 
 * gen :
 *	-
 * return:
 *	- 0 no priv
 *      - 1 priv granted
 *
 */ 
 function CheckAction ( $p_cn,$p_action_id)
{
  if ( $this->admin==1 ) return 1;

  $Res=ExecSql($p_cn,"select * from user_sec_act where ua_login='".$this->id."' and ua_act_id=$p_action_id");
  $Count=pg_NumRows($Res);
  if ( $Count == 0 ) return 0;
  if ( $Count == 1 ) return 1;
  echo "<H2 class=\"error\"> Invalid action !!! $Count select * from user_sec_act where ua_login='$p_login' and ua_act_id=$p_action_id </H2>";
}


}
?>
