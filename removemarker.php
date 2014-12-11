<?php
	
	require ("config.inc.php");

	if (!empty($_POST)) {
	
		$removed = false; // variável que indica se o par categoria-posição foi removido da tabela removed.
		$confirmed = false; // variável que indica se o par categoria-posição foi removido da tabela confirmed.
	
		if ((empty($_POST['category']) && $_POST['category'] != 0) || empty($_POST['lat']) || empty($_POST['lng']) || (empty($_POST['id']) && $_POST['id'] != 0)) {
			$response["success"] = false;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Por favor, entre uma categoria, uma latitude, uma longitude e um id para o dispositivo.";
			die(json_encode($response));
		}
		
		// O sistema verifica junto ao servidor se a categoria e posição a serem adicionadas estão contidas na tabela not_confirmed sob id igual ao do dispositivo. Se sim, remove a entrada; se não, continua.
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
					$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
					echo(json_encode($response));
					die();
				}
				die();
			}
			$query = "DELETE FROM `not_confirmed` WHERE category = :category AND lat = :lat AND lng = :lng AND id = :id";
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
					$response["message"] = "Database Error. Could not release the lock for the table not_confirmed.";
					echo(json_encode($response));
					die();
				}
				die();
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
				$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
				die(json_encode($response));
			}
			
			$response["success"] = true;
			$response["removed"] = $removed;
			$response["confirmed"] = $confirmed;
			$response["message"] = "Location successfully removed from the table not_confirmed.";
			die(json_encode($response));
		}
		
		// O sistema verifica junto ao servidor se a categoria e posição a serem removidas estão contidas na tabela confirmed.
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
	
		// Se não estiverem contidas, então verifica se está contidas na tabela removed sob id igual ao do dispositivo, daí, caso estejam, remove dessa tabela.
		if (!$row) {
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
			// Se estiverem contidas
			if ($row) {
				// Tenta pegar o lock
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
						$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
						echo(json_encode($response));
						die();
					}
					die();
				}
				// Remove
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
						$response["message"] = "Database Error. Could not release the lock for the table removed.";
						echo(json_encode($response));
						die();
					}
					die();
				}
				// Solta os locks
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
				$response["success"] = true;
				$response["removed"] = true;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Location not found in the tables confirmed, not_confirmed and successfully removed from the table removed.";
				die(json_encode($response));
			}
			else {
				$response["success"] = true;
				$response["removed"] = $removed;
				$response["confirmed"] = $confirmed;
				$response["message"] = "Locations not found in the tables confirmed and not_confirmed.";
				die(json_encode($response));
			}
		}
		// Se estiver contida na tabela not_confirmed
		else {
			// Insere na tabela removed sob id igual ao do dispositivo. 
			$query = "LOCK TABLES `removed` WRITE";
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
					$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
					echo(json_encode($response));
					die();
				}
				die();
			}
			$query = "INSERT INTO `removed` VALUES (:category, :lat, :lng, :id);";
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
					$response["message"] = "Database Error. Could not release the lock for the table not_confirmed.";
					echo(json_encode($response));
					die();
				}
				die();
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
				$response["message"] = "Database Error. Could not release the locks for the table not_confirmed.";
				die(json_encode($response));
			}
			
			$response["success"] = true;
			$response["removed"] = false;
			$response["confirmed"] = true;
			$response["message"] = "Location successfully removed from the table not_confirmed.";
			die(json_encode($response));
		}
	}
	else {
?>
		<h1>Remove Marker</h1>
		<form action="removemarker.php" method="post">
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
			<input type="submit" value="Remove Marker" />
		</form>
<?php
	}
?>