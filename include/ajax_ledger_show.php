<?php
/**
 * @brief
 * Show a div for selecting ledger
 * return a html code for creating a window
 * parameter 
 *   - type 
 *   - div
 *   - nbjrn
 *   - r_jrn[]
 */
// require_once '.php';
require_once 'class_acc_ledger.php';
require_once 'class_html_input.php';
if ( ! isset ($r_jrn)) { $r_jrn=null;}
$ctl='div_jrn'.$div;
ob_start();
echo HtmlInput::select_ledger($type,$r_jrn, $div);

$response = ob_get_clean();
ob_end_clean();
$html = escape_xml($response);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$ctl</ctl>
<code>$html</code>
</data>
EOF;
exit();
?>    