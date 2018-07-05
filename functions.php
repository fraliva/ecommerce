<?php  

use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlprice)
{

	if (!$vlprice > 0 ) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");

} // End function formatPrice

function formateDate($date)
{

	return date("d/m/Y", strtotime($date));
	
} // End function formateDate

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

} // End function checkLogin

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

} // End function getUserName

function getCartNrQtd()
{
	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return $totals["nrqtd"];

} // End function getCartNrQtd

function getCartVlSubTotal()
{
	$cart = Cart::getFromSession();

	$totals = $cart->getProductsTotals();

	return formatPrice($totals["vlprice"]);

} // End function getCartVlSubTotal

?>