<?php  

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model 
{

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const ERROR = "UserError";
	const ERROR_REGISTER  = "UserErrorRegister";
	const SUCCESS = "UserSuccess";

	public static function getFromSession()
	{

		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	} // End function getFromSession

	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 ) {

			// Não está logado
			return false;

		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {

				return true;

			} else if ($inadmin === false) {

				return true;

			} else {

				return false;
			
			}

		}
		
	} // End function checkLogin

	public static function login($login, $password)
	{

		$sql = new Sql();

		$results =  $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :LOGIN", array(
				":LOGIN"=>$login
		));

		if (count($results) === 0){

			throw new \Exception("Usuário inexistente ou senha inválida!");
			
		}

		$data = $results[0];

		// echo User::getPasswordHash($password);
		// exit;
		
		if (password_verify($password, $data["despassword"]) === true) {

			$user = new User();

			$data["desperson"] = utf8_encode($data["desperson"]);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Usuário inexistente ou senha inválida!");
			
		}
	} // End function login

	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin)) {

			if ($inadmin) {

				header("Location: /admin/login");

			} else {

				header("Location: /login");

			}

			exit;
		}

	} // End function verifyLogin

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	} // End function logout

	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	} // End function listAll

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
			));

		$this->setData($results[0]);

	} // End function save

	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
			));

		$data = $results[0];

		$data["desperson"] = utf8_encode($data["desperson"]);

		$this->setData($data);

	}

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
			));

		$this->setData($results[0]);

	} // End funcion update

	public function delete()
	{

		$sql = new Sql();

		$results = $sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));

	} // End function delete

	public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();
		$results = $sql->select(
			"SELECT * 
			FROM tb_persons a 
			INNER JOIN tb_users b USING(idperson) 
			WHERE a.desemail = :email;", array(
			":email"=>$email
		));

		if (count($results) === 0 ){

			throw new \Exception("Não foi possível recuperar a senha.");
			
		} else {

			$data = $results["0"];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
					":iduser"=>$data["iduser"],
					":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if (count($results2) === 0 ) {

				throw new \Exception("Não foi possível recuperar a senha.");
				
			} else {

				$dataRecovery = $results2[0];

				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
				$code = openssl_encrypt($dataRecovery["idrecovery"], 'aes-256-cbc', User::SECRET, 0 , $iv);

				$result = base64_encode($iv.$code);

				if ($inadmin === true) {

					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";

				} else {

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
					
				}
			
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir a senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $link;

			}

		}
	} // End function getForgot

	public static function validForgotDecrypt($result)
	{
		
		$result = base64_decode($result);

		$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
		$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');
		$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
			a.idrecovery = :idrecovery
			AND
			a.dtrecovery IS NULL
			AND
			DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
				":idrecovery"=>$idrecovery
		));

		if (count($results) === 0 ) {

			throw new \Exception("Não foi possível recuperar a senha");			
		} else {

			return $results[0];
		}
	} // End function validForgotDecrypt

	public static function setForgotUsed($idrecovery)
	{

		$sql = new Sql;

		$sql->select("UPDATE tb_userspasswordrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	} // End function setForgotUsed

	public function setPassword($password)
	{

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));

	} // End function setPassword

	public static function setError($msg)
	{

		$_SESSION[User::ERROR] = $msg;
			
	} // End function setErro

	public static function getError()
	{

		$msg = (isset($_SESSION[User::ERROR])) && $_SESSION[User::ERROR] ? $_SESSION[User::ERROR] : "";

		User::clearError();

		return $msg;
			
	} // End function getErro

	public static function clearError()
	{

		$_SESSION[User::ERROR] = NULL;
			
	} // End function clearError

	public static function setSuccess($msg)
	{

		$_SESSION[User::SUCCESS] = $msg;
			
	} // End function setSuccess

	public static function getSuccess()
	{

		$msg = (isset($_SESSION[User::SUCCESS])) && $_SESSION[User::SUCCESS] ? $_SESSION[User::SUCCESS] : "";

		User::clearSuccess();

		return $msg;
			
	} // End function getSuccess

	public static function clearSuccess()
	{

		$_SESSION[User::SUCCESS] = NULL;
			
	} // End function getSuccess

	public static function setErrorRegister($msg)
	{

		$_SESSION[User::ERROR_REGISTER] = $msg;

	} // End  function setErrorRegister

	public static function getErrorRegister()
	{

		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";

		User::clearErrorRegister();

		return $msg;

	} // End function getErrorRegister

	public static function clearErrorRegister()
	{

		$_SESSION[User::ERROR_REGISTER] = NULL;

	} // End  function setErrorRegister

	public static function checkLoginExists($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", array(
			":deslogin"=>$login
		));

		return (count($results) > 0 );

	} // End function checkLoginExists

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, array(
			"cost"=>12		
		));


	} // End function getPasswordHash

	public function getOrders()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser", 
			array(
				":iduser"=>$this->getiduser()
			));

		if (count($results) > 0 ) {

			return $results;

		}

	} // End  function getOrders 

	public static function getPage($page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		");

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return array(
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		);

	} // End function getPage

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson)
			WHERE b.desperson LIKE :search
			OR    b.desemail = :search
			OR    a.deslogin LIKE :search
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;", array(
				":search"=>"%".$search."%"
			));

		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

		return array(
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		);

	} // End function getPageSearch

} // End class User

?>