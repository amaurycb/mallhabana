<?php 

global $smarty;
include('../../config/config.inc.php');
include('../../header.php');

$smarty->display(dirname(__FILE__).'/front.tpl');

include('../../footer.php');