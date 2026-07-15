<?php

function totalMembers($pdo){

    return $pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE status='approved'
    ")->fetchColumn();

}

function totalEvents($pdo){

    return $pdo->query("
        SELECT COUNT(*)
        FROM events
    ")->fetchColumn();

}

function totalClubs($pdo){

    return $pdo->query("
        SELECT COUNT(*)
        FROM clubs
    ")->fetchColumn();

}

function totalRegistrations($pdo){

    return $pdo->query("
        SELECT COUNT(*)
        FROM registrations
    ")->fetchColumn();

}