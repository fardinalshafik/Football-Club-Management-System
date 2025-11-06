# Football Club Management System

## Overview
The Football Club Management System is a web application designed to help manage various aspects of a football club, including user authentication, player management, and match management. This project utilizes PHP, MySQL, and XAMPP for local development.

## Features
- User login and registration
- Admin dashboard for club management
- Player management (CRUD operations)
- Match management (CRUD operations)
- Search and filter capabilities for players and matches
- Responsive frontend design
- Secure database integration

## Project Structure
```
football-club-management
├── public
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── players.php
│   ├── matches.php
│   ├── assets
│   │   ├── css
│   │   │   └── style.css
│   │   └── js
│   │       └── main.js
├── src
│   ├── config
│   │   └── database.php
│   ├── controllers
│   │   ├── AuthController.php
│   │   ├── PlayerController.php
│   │   └── MatchController.php
│   ├── models
│   │   ├── User.php
│   │   ├── Player.php
│   │   └── Match.php
│   ├── views
│   │   ├── partials
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── players
│   │   │   ├── list.php
│   │   │   ├── add.php
│   │   │   └── edit.php
│   │   └── matches
│   │       ├── list.php
│   │       ├── add.php
│   │       └── edit.php
│   └── helpers
│       └── functions.php
├── sql
│   └── football_club_schema.sql
├── .env.example
├── .htaccess
└── README.md
```

## Installation
1. Clone the repository to your local machine.
2. Set up a MySQL database using the provided `sql/football_club_schema.sql` file.
3. Configure your database connection in `src/config/database.php`.
4. Start the XAMPP server and navigate to `http://localhost/football-club-management/public/index.php` in your web browser.

## Usage
- Register a new user through the registration page.
- Log in to access the admin dashboard.
- Manage players and matches through their respective sections.

## Security
- Passwords are hashed before storage.
- Input validation and sanitization are implemented to prevent SQL injection and XSS attacks.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is open-source and available under the MIT License.