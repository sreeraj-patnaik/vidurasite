<?php

function getStudents(PDO $pdo)
{
    $sql = "
    SELECT
        users.*,
        clubs.name AS club_name
    FROM users
    LEFT JOIN clubs
        ON clubs.id = users.club_id
    ORDER BY users.created_at DESC
    ";

    return $pdo->query($sql)->fetchAll();
}

function getStudent(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    return $stmt->fetch();
}