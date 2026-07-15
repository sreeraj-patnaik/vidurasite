<?php

$host = "aws-1-ap-northeast-2.pooler.supabase.com";
$port = "5432";
$dbname = "postgres";

$user = "postgres.utlpeohqfylltojfzqmz";
$password = "@ViduraDB01";

try {

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    $pdo = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );


} catch (PDOException $e) {

    die("Database Connection Failed : " . $e->getMessage());

}