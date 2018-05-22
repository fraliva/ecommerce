<?php  

use \Hcode\Page;

// Rota principal do site
$app->get('/', function() {
    
	$page = new Page();
	$page->setTpl("index");

});


?>