<?php  

use \Hcode\Page;
use \Hcode\Model\Product;

// Rota principal do site
$app->get('/', function() {
    
    $products = Product::listAll();

	$page = new Page();
	$page->setTpl("index", array(
		"products"=>Product::checkList($products)
	));

});

?>