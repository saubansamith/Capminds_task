<?php
include('../config/db.php');

$id = $_GET['id'];

$conn->query("DELETE FROM patients WHERE id=$id");

header("Location: list.php");
exit;
