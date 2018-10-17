<?php
	$conn = new mysqli('localhost','root','','bodegajc');

	if($conn->connect_error) {
		echo $error = $conn->connect_erro;
	}
?>