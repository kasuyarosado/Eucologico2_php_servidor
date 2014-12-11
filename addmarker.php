<?php
	
	require ("config.inc.php");

	if (!empty($_POST)) {
	
		$removed = false; // variável que indica se o par categoria-posição foi removido da tabela removed.
		$confirmed = false; // variável que indica se o par categoria-posição foi adicionado à tabela confirmed.
	
		if ((empty($_POST['category']) && $_POST['category'] != 0) || empty($_POST['lat']) || empty($_POST['lng']) || (empty($_POST['id']) && $_POST['id'] != 0)) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Por favor, entre uma categoria, uma latitude, uma longitude e um id para o dispositivo.";
			die(json_encode($response));
		}
		
		//  O sistema verifica se a posição a ser adicionado está contido na tabela removed para a categoria em questão sob id igual ao do dispositivo. Se estiver, remove a entrada.		
		$query = "LOCK TABLES `removed` WRITE";
		$query_params = null;
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not place the order for getting the lock for the table removed.";
			echo(json_encode($response));
			$query = "UNLOCK TABLES";
			$query_params = null;
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not release the lock for the table removed.";
				die(json_encode($response));
			}
			die();
		}
		
		$query = "SELECT 1 FROM `removed` WHERE category = :category AND lat = :lat AND lng = :lng AND id = :id";
		$query_params = array(
			':category' => $_POST['category'],
			':lat' => $_POST['lat'],
			':lng' => $_POST['lng'],
			':id' => $_POST['id']
		);
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not consult the table removed.";
			die(json_encode($response));
		}
	
		$row = $stmt->fetch();
		
		if ($row) {
			$query = "DELETE FROM `removed` WHERE category = :category AND lat = :lat AND lng = :lng AND id = :id";
			$query_params = array(
				':category' => $_POST['category'],
				':lat' => $_POST['lat'],
				':lng' => $_POST['lng'],
				':id' => $_POST['id']
			);
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not delete from the table removed.";
				echo(json_encode($response));
				$query = "UNLOCK TABLES";
				$query_params = null;
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (PDOException $ex) {
					$response["success"] = false;
					$response["removed"] = $removed;
					$response["confirmed"] = $confirmed;
					$response["message"] = "Database Error. Could not release the locks for the table removed.";
					die(json_encode($response));
				}
			}
			$removed = true;
		}
		
		$query = "UNLOCK TABLES";
		$query_params = null;
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not release the lock for the table removed.";
			die(json_encode($response));
		}
		
		// O sistema verifica junto ao servidor se a categoria e posição a serem adicionadas não estão contidas na tabela confirmed. Se sim, interrompe; se não, continua.
		$query = "SELECT 1 FROM `confirmed` WHERE category = :category AND lat = :lat AND lng = :lng";
		$query_params = array(
			':category' => $_POST['category'],
			':lat' => $_POST['lat'],
			':lng' => $_POST['lng']
		);
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not consult the table confirmed.";
			die(json_encode($response));
		}
	
		$row = $stmt->fetch();
	
		if ($row) {
			$response["success"] = true;
			$response["removed"] = $removed;
			$response["confirmed"] = true;
			$response["message"] = "This location is already inserted and confirmed.";
			die(json_encode($response));
		}
		
		// O sistema verifica junto ao servidor se a categoria e posição a serem adicionadas não estão contidas na tabela not_confirmed sob id igual ao do dispositivo. Se sim, interrompe; se não, continua.
		$query = "SELECT 1 FROM `not_confirmed` WHERE category = :category AND lat = :lat AND lng = :lng AND id = :id";
		$query_params = array(
			':category' => $_POST['category'],
			':lat' => $_POST['lat'],
			':lng' => $_POST['lng'],
			':id' => $_POST['id']
		);
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not consult the table not_confirmed.";
			die(json_encode($response));
		}
	
		$row = $stmt->fetch();
		
		if ($row) {
			$response["success"] = true;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "This location is already inserted for this device.";
			die(json_encode($response));
		}
		
		// Tenta obter o write-lock para a tabela not_confirmed. 
		$query = "LOCK TABLES `not_confirmed` WRITE";
		$query_params = null;
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not place the order for getting the lock for the table not_confirmed.";
			echo(json_encode($response));
			$query = "UNLOCK TABLES";
			$query_params = null;
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not release the lock for the table not_confirmed.";
				die(json_encode($response));
			}
			die();
		}
		
		// Tenta obter o número de vezes que os campos category, lat e lng a serem adicionados estão contido na tabela not_confirmed sob ids que não iguais ao do dispositivo. 
		$query = "SELECT COUNT(*) FROM `not_confirmed` WHERE category = :category AND lat = :lat AND lng = :lng GROUP BY category, lat, lng";
		$query_params = array(
			':category' => $_POST['category'],
			':lat' => $_POST['lat'],
			':lng' => $_POST['lng']
		);
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not consult the table not_confirmed.";
			echo(json_encode($response));
			$query = "UNLOCK TABLES";
			$query_params = null;
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not release the lock for the table not_confirmed.";
				die(json_encode($response));
			}
			die(json_encode($response));
		}
		
		$number_of_rows = (int) $stmt->fetchColumn();
		
		// Se o número obtido na etapa anterior for menor que 2, então uma nova tupla constituída pelos campos category, lat, lng e id é adicionada à tabela not_confirmed.
		// Se o número obtido na etapa anterior for maior igual à 2, então todas as tuplas que contém os campos category, lat, lng são removidas da tabela not_confirmed e uma nova tupla constituída pelos campos category, lat, lng e id é adicionada à tabela confirmed.
		if ($number_of_rows < 2) {
			$query = "INSERT INTO `not_confirmed` VALUES (:category, :lat, :lng, :id)";
			$query_params = array(
				':category' => $_POST['category'],
				':lat' => $_POST['lat'],
				':lng' => $_POST['lng'],
				':id' => $_POST['id']
			);
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not insert into the table not_confirmed.";
				echo(json_encode($response));
				$query = "UNLOCK TABLES";
				$query_params = null;
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (PDOException $ex) {
					$response["success"] = false;
					$response["removed"] = $removed;
					$response["confirmed"] = $confirmed;
					$response["message"] = "Database Error. Could not release the lock for the table not_confirmed.";
					die(json_encode($response));
				}
				die(json_encode($response));
			}
		}
		else {
			$query = "DELETE FROM `not_confirmed` WHERE category = :category AND lat = :lat AND lng = :lng";
			$query_params = array(
				':category' => $_POST['category'],
				':lat' => $_POST['lat'],
				':lng' => $_POST['lng']
			);
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not delete from the table not_confirmed.";
				echo(json_encode($response));
				$query = "UNLOCK TABLES";
				$query_params = null;
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (PDOException $ex) {
					$response["success"] = false;
					$response["removed"] = $removed;
					$response["confirmed"] = $confirmed;
					$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
					die(json_encode($response));
				}
				die(json_encode($response));
			}
			$query = "LOCK TABLES `confirmed` WRITE";
			$query_params = null;
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not place the order for getting the lock for the table confirmed.";
				echo(json_encode($response));
				$query = "UNLOCK TABLES";
				$query_params = null;
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (PDOException $ex) {
					$response["success"] = false;
					$response["removed"] = $removed;
					$response["confirmed"] = $confirmed;
					$response["message"] = "Database Error. Could not release the locks for the tables not_confirmed and confirmed.";
					die(json_encode($response));
				}
				die(json_encode($response));
			}
			$query = "INSERT INTO `confirmed` VALUES (:category, :lat, :lng)";
			$query_params = array(
				':category' => $_POST['category'],
				':lat' => $_POST['lat'],
				':lng' => $_POST['lng']
			);
			try {
				$stmt = $db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch (PDOException $ex) {
				$response["success"] = false;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Database Error. Could not insert into the table confirmed.";
				echo(json_encode($response));
				$query = "UNLOCK TABLES";
				$query_params = null;
				try {
					$stmt = $db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch (PDOException $ex) {
					$response["success"] = false;
					$response["removed"] = $removed;
					$response["confirmed"] = $confirmed;
					$response["message"] = "Database Error. Could not release the lock for the tables not_confirmed and confirmed.";
					die(json_encode($response));
				}
				die(json_encode($response));
			}
			$confirmed = true;
		}
		// Tenta soltar os locks
		$query = "UNLOCK TABLES";
		$query_params = null;
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Database Error. Could not release the locks for the tables not_confirmed and confirmed.";
			die(json_encode($response));
		}
		
		$response["success"] = true;
		$response["removed"] = $removed;
		$response["confirmed"] = $confirmed;
		$response["message"] = "Location successfully inserted.";
		die(json_encode($response));
		
	}
	else {
?>
		<h1>Add Marker</h1>
		<form action="addmarker.php" method="post">
			Categoria: <br />
			<input type="int" name="category" placeholder="categoria" value="" />
			<br></br>
			Latitude: <br />
			<input type="double" name="lat" placeholder="latitude" value="" />
			<br></br>
			Longitude: <br />
			<input type="double" name="lng" placeholder="longitude" value="" />
			<br></br>
			ID do dispositivo: <br />
			<input type="text" name="id" placeholder="id do dispositivo" value="" />
			<br></br>
			<input type="submit" value="Add Marker" />
		</form>
<?php
	}
?>