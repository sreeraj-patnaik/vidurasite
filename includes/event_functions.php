<?php

function getEvents(PDO $pdo, $search = '', $club = '', $status = '')
{
    $sql = "

    SELECT

        events.*,

        clubs.name AS club_name,

        (
            SELECT COUNT(*)
            FROM registrations
            WHERE registrations.event_id = events.id
        ) AS registrations

    FROM events

    LEFT JOIN clubs
    ON clubs.id = events.club_id

    WHERE 1=1

    ";

    $params = [];

    if ($search != '') {

        $sql .= "
        AND (
            LOWER(events.title) LIKE LOWER(?)
            OR LOWER(events.venue) LIKE LOWER(?)
        )
        ";

        $keyword = "%".$search."%";

        $params[] = $keyword;
        $params[] = $keyword;
    }

    if ($club != '') {

        $sql .= "

        AND events.club_id=?

        ";

        $params[] = $club;

    }

    if ($status != '') {

        $sql .= "

        AND events.status=?

        ";

        $params[] = $status;

    }

    $sql .= "

    ORDER BY events.event_date DESC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetchAll();
}

function getEvent(PDO $pdo, $id)
{
    $stmt = $pdo->prepare("

    SELECT *

    FROM events

    WHERE id=?

    LIMIT 1

    ");

    $stmt->execute([$id]);

    return $stmt->fetch();
}

function totalEvents(PDO $pdo)
{
    return $pdo
        ->query("SELECT COUNT(*) FROM events")
        ->fetchColumn();
}

function upcomingEvents(PDO $pdo)
{
    return $pdo
        ->query("

        SELECT COUNT(*)

        FROM events

        WHERE status='Upcoming'

        ")
        ->fetchColumn();
}

function completedEvents(PDO $pdo)
{
    return $pdo
        ->query("

        SELECT COUNT(*)

        FROM events

        WHERE status='Completed'

        ")
        ->fetchColumn();
}