<?php 
include('Smarty.class.php'); 
  
// create object 
$smarty = new Smarty; 
  
// assign some content. This would typically come from 
// a database or other source, but we'll use static 
$smarty->assign('name', 'Soumit Sarkar'); 
$smarty->assign('address', 'Kolkata'); 
  
  
$root_path      = '/home/murabba/public_html/whmcs.murabba.dev';//$_SERVER['DOCUMENT_ROOT'];
$tpl_path=$root_path.'/modules/servers/cpanel/templates/overview.tpl';


// display it 
$smarty->display($tpl_path); 
  
  /*
  {include_php file='/home/murabba/public_html/whmcs.murabba.dev/modules/load_data_to_tpl_file.php'}

{$address}*/
?> 