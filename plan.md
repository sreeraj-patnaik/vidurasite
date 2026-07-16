# VIDURA Activity Clubs Management System

## Overview

VIDURA is a complete Activity Club Management System for the Department of Computer Science & Systems Engineering.

The platform manages:

- Student Membership
- Club Management
- Event Management
- Registrations
- Attendance
- Points
- Leaderboard
- Badges
- Announcements
- Gallery
- Certificates
- Digital ID
- Reports

Tech Stack

- PHP 8
- PostgreSQL (Supabase)
- Bootstrap 5
- Vanilla JavaScript
- XAMPP (Development)

---

# Folder Structure

```
vidurasite/

admin/
member/
public/

actions/

config/
includes/
uploads/
assets/

login.php
logout.php
register.php
index.php
```

There should be ONLY ONE `actions` folder in the project.

Never create:

```
admin/actions/
member/actions/
```

---

# Authentication

Implemented

Uses PHP Sessions.

Session Variables

```
$_SESSION['user_id']
$_SESSION['name']
$_SESSION['role']
```

Role values

```
admin
member
```

Logout

```
logout.php
```

destroys session, cookie and redirects to login.

---

# Database

IMPORTANT

Never assume database columns.

Always use the existing schema.

Current confirmed tables:

## users

```
id
roll_number
name
email
password
phone
year
department
section
club_id
profile_photo
bio
points
level
status
role
joined_at
created_at
```

---

## clubs

```
id
name
description
logo
theme_color
```

---

## badges

```
id
title
description
icon
color
```

NO required_points column exists.

---

## announcements

```
id
title
description
created_at
expires_at
```

NO status column.

NO content column.

---

## events

```
id
title
description
club_id
venue
event_date
registration_start
registration_end
capacity
year_allowed
department_allowed
first_come_first_serve
points
banner
status
created_by
created_at
start_time
end_time
max_participants
```

---

# Current Features

## Public

Implemented

- Home
- About
- Club pages
- Events
- Gallery placeholder
- Leaderboard placeholder

---

## Authentication

Implemented

- Login
- Registration
- Logout

Needs testing.

---

## Admin

Implemented

Dashboard

Students

Events

Registrations

Attendance

Points

Announcement page

Badge page

Most CRUD pages exist but require verification.

---

## Member

Implemented

Dashboard

Events

Profile

Leaderboard

Announcement integration pending.

Badges incomplete.

---

# Current Problems

The project contains many inconsistencies because several pages were generated before the final database schema was confirmed.

Examples

Announcements

Wrong assumptions

```
content
status
```

Correct schema

```
description
expires_at
```

Badges

Wrong assumption

```
required_points
```

Correct schema

```
color
```

Several pages still reference old columns.

Need a full audit.

---

# Required Refactoring

Before implementing new modules:

Review every CRUD page.

Verify:

SELECT

INSERT

UPDATE

DELETE

against actual PostgreSQL schema.

Never reference non-existing columns.

---

# Navbar

Current navbar works but needs redesign.

Required

Responsive

Single line

Profile dropdown

Dynamic menu

Guest

```
Login
Register
```

Member

```
Dashboard
Profile
Events
Leaderboard
Badges
Logout
```

Admin

```
Dashboard
Students
Events
Announcements
Logout
```

---

# Coding Rules

Never assume columns.

Always inspect schema.

Never create duplicate action folders.

One feature at a time.

Complete one file before moving to next.

Avoid placeholder logic.

All CRUD should work immediately.

---

# Pending Modules

Priority 1

Announcement CRUD verification

Badge CRUD verification

Navbar redesign

Fix all schema mismatches

---

Priority 2

Member Badges

Announcement display

Gallery

Admin Gallery

---

Priority 3

Digital ID

QR Code

Certificates

Reports

Analytics

Settings

---

# UI

Bootstrap 5

Modern cards

Rounded corners

Consistent spacing

Responsive

Use Bootstrap Icons.

---

# Upload Locations

Profile Photos

```
uploads/profiles/
```

Badge Icons

```
uploads/badges/
```

Event Banners

```
uploads/events/
```

Gallery

```
uploads/gallery/
```

---

# Security

Use prepared statements.

Escape output with

```
htmlspecialchars()
```

Validate uploads.

Hash passwords using

```
password_hash()
```

Use

```
password_verify()
```

Regenerate session on login.

Destroy session on logout.

---

# Immediate Goal

Do NOT build new features.

First stabilize the existing system.

Tasks

- Audit every page.
- Fix every schema mismatch.
- Test every CRUD operation.
- Ensure login → dashboard → logout works.
- Verify navbar.
- Remove all PHP warnings and notices.

After the project is stable, continue implementing the remaining modules.