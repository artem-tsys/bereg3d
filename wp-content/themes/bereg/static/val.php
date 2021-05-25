<?php
	$name = htmlspecialchars($_POST["name"]);
	$email = htmlspecialchars($_POST["mail"]);
	$tema = htmlspecialchars($_POST["message"]);

$arr = array(
	'error' => [
			'tel' => 'какой телефон?',
			'name' => 'О_о',
	],
	'placeholder' => 1,
	'result' => 0
);

echo json_encode($arr);