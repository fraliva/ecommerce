<?php  

use \Hcode\Model\User;

function formatPrice($vlprice)
{

	if (!$vlprice > 0 ) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");

} // End function formatPrice

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

} // End function checkLogin

function getUserName()
{

	$user = User::getFromSession();

	return $user->getdesperson();

} // End function getUserName

?>