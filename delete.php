<?php

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(
    INPUT_POST,
    "application_id",
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare(
    "DELETE FROM applications WHERE application_id = :id"
);

$stmt->execute([
    ":id" => $id
]);

if ($stmt->rowCount() > 0) {
    header("Location: index.php?status=deleted");
} else {
    header("Location: index.php");
}

exit;