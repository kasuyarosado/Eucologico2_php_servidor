<?php

	require ("config.inc.php");

	if (!empty($_POST))  {
		
		if (empty($_POST['id']) && $_POST['id'] != 0) {
			$response["success"] = false;
			$response["message"] = "Por favor, entre um id para o dispositivo.";
			die(json_encode($response));
		}
		
		$query = "Select category, lat, lng FROM `removed` WHERE id = :id";
		$query_params = array(':id' => $_POST['id']);
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch (PDOException $ex) {
			$response["success"] = false;
			$response["message"] = "Database Error.";
			echo(json_encode($response));
			die();
		}
	
		$rows = $stmt->fetchAll();
	
		if ($rows) {
			$response["success"] = true;
			$response["message"] = "Category and Position Available!";
			$response["category_position"] = array();
		
			foreach ($rows as $row) {
				$post = array();
				$post["category"] = $row["category"];
				$post["lat"] = $row["lat"];
				$post["lng"] = $row["lng"];
			
				array_push($response["category_position"], $post);
			}
		
			echo(json_encode($response));
		}
		else {
			$response["success"] = true;
			$response["message"] = "No Category and Position Available!";
			$response["category_position"] = array();
			echo(json_encode($response));
			die();
		}
	}
	else {
?>
		<h1>Fetch Not Confirmed Removed Marker</h1>
		<form action="fetchnotconfirmedremovedmarker.php" method="post">
			ID do dispositivo: <br />
			<input type="int" name="id" placeholder="id do dispositivo" value="" />
			<br></br>
			<input type="submit" value="Fetch Not Confirmed Removed Marker" />
		</form>
<?php
	}
?>