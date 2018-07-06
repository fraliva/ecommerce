<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model
{

	const SESSION_ERROR = "AddressError";

	public static function getCEP($nrcep)
	{

		$nrcep = str_replace("-","", $nrcep);

		//http://viacep.com.br/ws/01001000/json/

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/$nrcep/json/");

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		return $data;

	} // End function getCEP

	public function loadFromCEP($nrcep)
	{
		$data = Address::getCEP($nrcep);

		if ( isset($data['logradouro']) && $data['logradouro'] ) {

			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']);
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($data['cep']);

		}

	} // End function loadFromCEP

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", array(
			":idaddress"=>$this->getidaddress(),
			":idperson"=>$this->getidperson(),
			":desaddress"=>utf8_decode($this->getdesaddress()),
			":desnumber"=>$this->getdesnumber(),
			":descomplement"=>utf8_decode($this->getdescomplement()),
			":descity"=>utf8_decode($this->getdescity()),
			":desstate"=>$this->getdesstate(),
			":descountry"=>utf8_decode($this->getdescountry()),
			":deszipcode"=>$this->getdeszipcode(),
			":desdistrict"=>utf8_decode($this->getdesdistrict())
		));

		if (count($results) > 0 ) {

			$this->setData($results[0]);

		}
	} // End function save

	public static function setMsgError($msg)
	{

		$_SESSION[Address::SESSION_ERROR] = $msg;
			
	} // End function setMsgErro

	public static function getMsgError()
	{

		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;
			
	} // End function getMsgErro

	public static function clearMsgError()
	{

		$_SESSION[Address::SESSION_ERROR] = NULL;
			
	} // End function getMsgErro

} // End class User

?>