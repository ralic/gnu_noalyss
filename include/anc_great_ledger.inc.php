<?php

require_once('class_anc_grandlivre.php');
$gl = new Anc_GrandLivre($cn);
$gl->get_request();
echo '<form method="get">';
echo $gl->display_form();
echo '<p>' . HtmlInput::submit('Recherche', 'Recherche') . '</p>';
echo '</form>';
if (isset($_GET['result']))
{
    echo $gl->show_button();
    echo $gl->display_html();
    echo $gl->show_button();
}
?>