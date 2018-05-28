<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model
{

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	} // End function listAll

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
			));

		$this->setData($results[0]);

		Category::updateFile();

	} // End function save

	public function get($idcategory)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			"idcategory"=>$idcategory
		));

		$this->setData($results[0]);

	} // End function get

	public function delete()
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$this->getidcategory()
		));

		Category::updateFile();

	} // End function delete

	public static function updateFile()
	{

		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));

	}

	public function getProducts($related = true)
	{

		$sql = new Sql();

		if ($related) {

			return $sql->select("SELECT * FROM tb_products WHERE idproduct IN (
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
				);
			", array(
				":idcategory"=>$this->getidcategory()
			));

		} else {
			
			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN (
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
				);
			", array(
				":idcategory"=>$this->getidcategory()
			));
		}
	} // End function getProducts

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));

	} // End function addProduct

	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));

	} // End function removeProduct

} // End class User

?>