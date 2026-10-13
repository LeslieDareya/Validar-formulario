<?php

require 'conexion.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;
}


$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;


if ($id <= 0) {

    header('Location: index.php');

    exit;
}


$stmt = $mysqli->prepare(
    "DELETE FROM eventos WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();

$mysqli->close();


header('Location: index.php?borrado=1');

exit;