<?php
require_once('class_database.php');
require_once('class_dossier.php');
require_once("ac_common.php");
require_once("constant.php");
require_once('function_javascript.php');
require_once('class_extension.php');
require_once ('class_html_input.php');
require_once('class_iselect.php');
require_once ('constant.security.php');
require_once ('class_user.php');
echo '<div class="topmenu">';
@html_page_start($_SESSION['g_theme']);

$cn=new Database(dossier::id());
$user=new User($cn);
$user->check();
$only_plugin=$user->check_dossier(dossier::id());


/* javascript file */
echo load_all_script();

/* show button to return to access */

/* show all the extension we can access */
$a=new ISelect('plugin_code');
$a->value=Extension::make_array($cn);
$a->selected=(isset($_REQUEST['plugin_code']))?strtoupper($_REQUEST['plugin_code']):'';

/* no plugin available */
if ( count($a->value) == 0 )
{
    alert(j(_("Aucune extension  disponible")));
    exit;
}

/* only one plugin available then we don't propose a choice*/
if ( count($a->value)==1 )
{
    $_REQUEST['plugin_code']=$a->value[0]['value'];
}
else
{
    echo '<form method="get" action="do.php">';
    echo Dossier::hidden();
    echo HtmlInput::request_to_hidden(array('plugin_code','ac'));
    echo _('Extension').$a->input().HtmlInput::submit('go',_("Choix de l'extension"));
    echo '</form>';
    echo '<hr>';
}
/* if a code has been asked */
if (isset($_REQUEST['plugin_code']) )
{
    $cn=new Database(dossier::id());
    $ext=new Extension($cn);
    $ext->search('code',$_REQUEST['plugin_code']);
    if ( $ext->get_parameter('id') != 0 )
    {
        /* security */
        if ( $ext->can_request($_SESSION['g_user']) == 0 )
        {
            alert(j(_("Vous ne pouvez pas utiliser cette extension. Contactez votre responsable")));
            exit();
        }
   
        if ( ! file_exists('../include/ext'.DIRECTORY_SEPARATOR.trim($ext->get_parameter('filepath'))))
        {
            alert(j(_("Ce fichier n'existe pas ")));
            exit();
        }
        require_once('ext'.DIRECTORY_SEPARATOR.trim($ext->get_parameter('filepath')));
    }
    else
    {
        alert(j(_("Cette extension n'existe pas ")));
        exit();
    }

}
?>
