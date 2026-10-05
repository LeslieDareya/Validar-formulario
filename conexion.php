<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli(
        "localhost",
        "obedient_suit_gub",
        "mQ(7Z-+K71i3vT3Vbp",
        "obedient_suit_gub_formulariodb"
    );

    $mysqli->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    die("No se pudo conectar a la base de datos.");
}