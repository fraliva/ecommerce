<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model
{

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	} // End function listAll

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllenght, :vlweight, :desurl)", array(
			":idproduct"	=>$this->getidproduct(),
			":desproduct"	=>$this->getdesproduct(),
			":vlprice"		=>$this->getvlprice(),
			":vlwidth"		=>$this->getvlwidth(),
			":vlheight"		=>$this->getvlheight(),
			":vllenght"		=>$this->getvllenght(),
			":vlweight"		=>$this->getvlweight(),
			":desurl"		=>$this->getdesurl()
			));

		$this->setData($results[0]);

	} // End function save

	public function get($idproduct)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
			"idproduct"=>$idproduct
		));

		$this->setData($results[0]);

	} // End function get

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
			":idproduct"=>$this->getidproduct()
		));

	} // End function delete

} // End class User

?>