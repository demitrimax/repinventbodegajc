<?php
	date_default_timezone_set("America/Mexico_City");
	$conn = new mysqli('localhost','root','','bodegajc');

	if($conn->connect_error) {
		echo $error = $conn->connect_erro;
	}
?>