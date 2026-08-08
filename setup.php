<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>VIDURA Database Setup</h2>";
echo "<hr>";

$sql = "

CREATE TABLE IF NOT EXISTS users (

id SERIAL PRIMARY KEY,

roll_number VARCHAR(20) UNIQUE NOT NULL,

name VARCHAR(120) NOT NULL,

email VARCHAR(120) UNIQUE NOT NULL,

password TEXT NOT NULL,

phone VARCHAR(20),

year SMALLINT,

department VARCHAR(100),

section VARCHAR(20),

club_id INTEGER,

profile_photo TEXT,

bio TEXT,

points INTEGER DEFAULT 0,

level INTEGER DEFAULT 1,

status VARCHAR(20) DEFAULT 'pending',

role VARCHAR(20) DEFAULT 'member',

joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ users created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS clubs(

id SERIAL PRIMARY KEY,

name VARCHAR(100) UNIQUE,

description TEXT,

logo TEXT,

theme_color VARCHAR(20)

);

";

$pdo->exec($sql);

echo "✅ clubs created<br>";

$count = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();

if($count==0){

$pdo->exec("

INSERT INTO clubs(name,description,theme_color)

VALUES

('TechKruti','Technology Club','#005B96'),

('KhelKruti','Sports Club','#00A86B'),

('SamsKruti','Cultural Club','#C44536');

");

echo "✅ Clubs inserted<br>";

}

$sql = "

CREATE TABLE IF NOT EXISTS events (

id SERIAL PRIMARY KEY,

title VARCHAR(200) NOT NULL,

description TEXT,

club_id INTEGER REFERENCES clubs(id) ON DELETE SET NULL,

venue VARCHAR(200),

event_date TIMESTAMP,

registration_start TIMESTAMP,

registration_end TIMESTAMP,

capacity INTEGER,

year_allowed VARCHAR(50),

department_allowed VARCHAR(100),

first_come_first_serve BOOLEAN DEFAULT TRUE,

points INTEGER DEFAULT 0,

banner TEXT,

status VARCHAR(20) DEFAULT 'Upcoming',

created_by INTEGER,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ events created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS registrations (

id SERIAL PRIMARY KEY,

event_id INTEGER REFERENCES events(id) ON DELETE CASCADE,

user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,

attendance BOOLEAN DEFAULT FALSE,

registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

remarks TEXT

);

";

$pdo->exec($sql);

echo "✅ registrations created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS point_logs (

id SERIAL PRIMARY KEY,

user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,

event_id INTEGER REFERENCES events(id) ON DELETE SET NULL,

points INTEGER NOT NULL,

reason TEXT,

added_by INTEGER,

added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ point_logs created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS badges (

id SERIAL PRIMARY KEY,

title VARCHAR(100),

description TEXT,

icon TEXT,

color VARCHAR(20)

);

";

$pdo->exec($sql);

echo "✅ badges created<br>";

$count = $pdo->query("SELECT COUNT(*) FROM badges")->fetchColumn();

if($count==0){

$pdo->exec("

INSERT INTO badges(title,description,color)

VALUES

('Top Performer','Outstanding performance','#FFD700'),

('Organizer','Successfully organized events','#2196F3'),

('Volunteer','Active volunteer','#4CAF50'),

('Champion','Competition Winner','#F44336'),

('Researcher','Research Activities','#9C27B0');

");

echo "✅ Default badges inserted<br>";

}

$sql = "

CREATE TABLE IF NOT EXISTS user_badges (

id SERIAL PRIMARY KEY,

user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,

badge_id INTEGER REFERENCES badges(id) ON DELETE CASCADE,

awarded_by INTEGER,

awarded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ user_badges created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS gallery (

id SERIAL PRIMARY KEY,

title VARCHAR(200),

image TEXT,

event_id INTEGER REFERENCES events(id) ON DELETE SET NULL,

uploaded_by INTEGER,

uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ gallery created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS announcements (

id SERIAL PRIMARY KEY,

title VARCHAR(200),

description TEXT,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

expires_at TIMESTAMP

);

";

$pdo->exec($sql);

echo "✅ announcements created<br>";

$sql = "

CREATE TABLE IF NOT EXISTS settings (

id SERIAL PRIMARY KEY,

website_title VARCHAR(150),

membership_fee INTEGER,

semester VARCHAR(20),

contact_email VARCHAR(150),

homepage_banner TEXT,

techkruti_image TEXT,

khelkruti_image TEXT,

samskruti_image TEXT,

liet_logo TEXT,

vidura_logo TEXT

);

";

$pdo->exec($sql);

echo "✅ settings created<br>";

$count = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();

if($count==0){

$pdo->exec("

INSERT INTO settings

(website_title,membership_fee,semester,contact_email)

VALUES

('VIDURA Activity Clubs',100,'Odd 2026-27','vidura@liet.ac.in');

");

echo "✅ Default settings inserted<br>";

}


echo "<hr>";
echo "<h3 style='color:green;'>🎉 VIDURA database initialized successfully.</h3>";
echo "<p>You can now delete <b>setup.php</b> for security.</p>";
