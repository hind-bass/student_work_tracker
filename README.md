# Student Work Tracker — Course & Assignment Management Web App

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?logo=symfony&logoColor=white)
![Doctrine](https://img.shields.io/badge/Doctrine-ORM%203-FC6A31)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)

Student Work Tracker is a web application that helps students **organize their courses and assignments**, follow their progress on a dashboard, and stay focused with a built-in **Pomodoro timer**. It is built with **Symfony 7** following the MVC pattern, with Doctrine ORM for persistence and Twig for server-side rendering.

---

## Table of Contents

- [Objectives](#objectives)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Data Model](#data-model)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Routes](#routes)
- [Screenshots](#screenshots)
- [Future Work](#future-work)
- [Author](#author)

---

## Objectives

- Give each student a personal space to track courses, deadlines and workload.
- Visualize progress (completed vs. remaining work, overdue assignments, work per course).
- Practice building a secure, multi-user web application with a modern PHP framework.

## Features

- **Authentication** — registration, login and logout with Symfony Security (hashed passwords, CSRF-protected forms).
- **Per-user data isolation** — every course and assignment belongs to a user; all queries are scoped to the logged-in user.
- **Course management** — create, view, edit and delete courses with code, color, professor, credits, semester and description.
- **Assignment management** — create, view, edit and delete assignments linked to a course, with:
  - due date, priority (*low, medium, high, urgent*) and status (*to do, in progress, completed, cancelled*);
  - completion percentage, estimated vs. actual hours, notes;
  - quick status updates from the dashboard through an AJAX (JSON) endpoint.
- **Dashboard** — counts by status, overall progress percentage, upcoming deadlines, overdue assignments, recent activity, and a **Chart.js** chart of assignments per course.
- **Pomodoro timer** — focus and break modes (25 / 5 / 10 / 40 minutes), with session statistics stored in the browser (localStorage).

## Tech Stack

| Layer | Technologies |
|---|---|
| Backend | PHP 8.2, Symfony 7.3 (Framework, Security, Form, Validator), Doctrine ORM 3, Doctrine Migrations |
| Frontend | Twig, Bootstrap 5.3, Bootstrap Icons, Chart.js 4, Symfony AssetMapper, Stimulus, Turbo |
| Database | MySQL 8 |
| Tooling | Composer, Symfony CLI, PHPUnit (configured) |

## Architecture

The application follows Symfony's **MVC** structure with a dedicated service layer:

```mermaid
flowchart LR
    B[Browser] --> C[Controllers]
    C --> F[Forms + Validation]
    C --> S[StatisticsService]
    S --> R[Doctrine Repositories]
    C --> R
    R --> D[(MySQL)]
    C --> T[Twig templates]
    T --> B
```

- **Controllers** handle routing and access control (`#[IsGranted('ROLE_USER')]`).
- **Form types** (`CourseType`, `AssignmentType`, `RegistrationFormType`) handle input binding and validation.
- **`StatisticsService`** aggregates dashboard metrics and prepares chart data.
- **Repositories** contain the custom queries (counts by status / course, upcoming and overdue assignments, recent activity).
- **PHP 8.1 enums** (`AssignmentStatus`, `AssignmentPriority`) are mapped directly by Doctrine.

## Data Model

```mermaid
erDiagram
    USER ||--o{ COURSE : owns
    USER ||--o{ ASSIGNMENT : owns
    COURSE ||--o{ ASSIGNMENT : contains
    USER { int id string email string firstName string lastName }
    COURSE { int id string name string code string color string professor int credits string semester }
    ASSIGNMENT { int id string title datetime dueDate string priority string status int completionPercentage float estimatedHours float actualHours }
```

Deleting a course removes its assignments (cascade + orphan removal).

## Project Structure

```
student_work_tracker/
├── assets/                 # JS (Stimulus controllers) and CSS
├── config/                 # Symfony configuration (security, doctrine, routes, ...)
├── migrations/             # Doctrine migrations
├── public/                 # Front controller (index.php) and public CSS
├── src/
│   ├── Controller/         # Assignment, Course, Dashboard, Home, Pomodoro, Registration, Security
│   ├── Entity/             # User, Course, Assignment
│   ├── Enum/               # AssignmentStatus, AssignmentPriority
│   ├── Form/               # Form types
│   ├── Repository/         # Doctrine repositories with custom queries
│   ├── Service/            # StatisticsService
│   └── schema_database.sql # Reference SQL schema
├── templates/              # Twig views
└── tests/
```

## Getting Started

### Prerequisites

- PHP 8.2+ with the `pdo_mysql` extension
- Composer
- MySQL 8
- (Optional) [Symfony CLI](https://symfony.com/download)

### Installation

```bash
git clone https://github.com/hind-bass/student_work_tracker.git
cd student_work_tracker
composer install
```

Create a `.env.local` file to configure your database connection:

```dotenv
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/university_manager_v2?serverVersion=8.0.32&charset=utf8mb4"
```

Create the database and run the migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Start the development server:

```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

Then open http://localhost:8000, create an account, and start adding courses and assignments.

## Routes

| Route | Description |
|---|---|
| `/register`, `/login`, `/logout` | Authentication |
| `/` or `/dashboard` | Dashboard with statistics and chart |
| `/home` | Home page |
| `/course/` | Course list, plus `/new`, `/{id}`, `/{id}/edit` |
| `/assignment/` | Assignment list, plus `/new`, `/{id}`, `/{id}/edit` |
| `POST /assignment/{id}/status` | Quick status update (JSON) |
| `/pomodoro/` | Pomodoro timer |

## Screenshots

<!-- TODO: add screenshots to docs/screenshots/ and reference them here, for example:
![Dashboard](docs/screenshots/dashboard.png)
![Assignments](docs/screenshots/assignments.png)
![Pomodoro timer](docs/screenshots/pomodoro.png)
-->

## Future Work

- Add unit and functional tests (PHPUnit is already configured).
- Deadline reminders by email using Symfony Mailer.
- Calendar view of deadlines.
- Persist Pomodoro sessions in the database and link them to assignments.
- Docker setup aligned with the MySQL configuration.

## Author

**Hind Bassou** — Computer Engineering student, National School of Applied Sciences of Oujda (ENSAO)
GitHub: [@hind-bass](https://github.com/hind-bass)
