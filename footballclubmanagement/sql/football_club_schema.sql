-- Football Club Management System Database Schema
-- Created: September 25, 2025

-- Create database
CREATE DATABASE IF NOT EXISTS football_club_management;
USE football_club_management;

-- Users table for authentication and user management
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'coach', 'player', 'member', 'staff') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Teams table to manage different teams in the club
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    coach_id INT,
    division VARCHAR(50),
    founded_year YEAR,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_coach_id (coach_id)
);

-- Players table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL, -- Changed from ENUM to VARCHAR to match controller expectations
    age INT NOT NULL CHECK (age >= 16 AND age <= 50),
    team_id INT,
    jersey_number INT,
    height DECIMAL(5,2), -- in cm
    weight DECIMAL(5,2), -- in kg
    nationality VARCHAR(50),
    date_of_birth DATE,
    contract_start DATE,
    contract_end DATE,
    salary DECIMAL(10,2),
    stats TEXT, -- Added for controller compatibility
    contract VARCHAR(255), -- Added for controller compatibility
    status ENUM('active', 'injured', 'suspended', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
    UNIQUE KEY unique_jersey_team (team_id, jersey_number),
    INDEX idx_team_id (team_id),
    INDEX idx_position (position),
    INDEX idx_status (status)
);

-- Matches table
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    match_date DATETIME NOT NULL,
    score VARCHAR(10), -- Format: "2-1" or NULL if not played yet
    home_score INT DEFAULT NULL,
    away_score INT DEFAULT NULL,
    venue VARCHAR(100),
    match_type ENUM('league', 'cup', 'friendly', 'championship') DEFAULT 'league',
    status ENUM('scheduled', 'live', 'completed', 'postponed', 'cancelled') DEFAULT 'scheduled',
    attendance INT,
    referee VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_match_date (match_date),
    INDEX idx_home_team (home_team),
    INDEX idx_away_team (away_team),
    INDEX idx_status (status)
);

-- Staff table for coaches, trainers, medical staff, etc.
CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    department ENUM('coaching', 'medical', 'administrative', 'technical') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    hire_date DATE,
    salary DECIMAL(10,2),
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_status (status)
);

-- Match events table for goals, cards, substitutions, etc.
CREATE TABLE match_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    player_id INT,
    event_type ENUM('goal', 'yellow_card', 'red_card', 'substitution_in', 'substitution_out') NOT NULL,
    minute INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
    INDEX idx_match_id (match_id),
    INDEX idx_player_id (player_id),
    INDEX idx_event_type (event_type)
);

-- Player statistics table
CREATE TABLE player_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    season VARCHAR(20) NOT NULL, -- e.g., "2024-25"
    matches_played INT DEFAULT 0,
    goals_scored INT DEFAULT 0,
    assists INT DEFAULT 0,
    yellow_cards INT DEFAULT 0,
    red_cards INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    UNIQUE KEY unique_player_season (player_id, season),
    INDEX idx_player_id (player_id),
    INDEX idx_season (season)
);

-- Training sessions table
CREATE TABLE training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT,
    coach_id INT,
    session_date DATETIME NOT NULL,
    duration INT, -- in minutes
    type ENUM('practice', 'tactical', 'fitness', 'recovery') DEFAULT 'practice',
    location VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
    FOREIGN KEY (coach_id) REFERENCES staff(id) ON DELETE SET NULL,
    INDEX idx_team_id (team_id),
    INDEX idx_session_date (session_date)
);

-- Insert sample data

-- Insert sample users
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@club.com', 'admin'),
('coach1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach1@club.com', 'coach'),
('manager1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager1@club.com', 'manager');

-- Insert sample teams
INSERT INTO teams (name, division, founded_year, description) VALUES
('First Team', 'Premier League', 1995, 'Main professional team'),
('Youth Team', 'Youth League', 2000, 'Under-21 development team'),
('Academy Team', 'Academy League', 2005, 'Under-18 training team');

-- Insert sample staff
INSERT INTO staff (name, position, department, phone, email, hire_date, status) VALUES
('John Smith', 'Head Coach', 'coaching', '+1234567890', 'john.smith@club.com', '2023-01-15', 'active'),
('Sarah Johnson', 'Assistant Coach', 'coaching', '+1234567891', 'sarah.johnson@club.com', '2023-02-01', 'active'),
('Dr. Michael Brown', 'Team Doctor', 'medical', '+1234567892', 'michael.brown@club.com', '2022-08-10', 'active'),
('Emma Wilson', 'Physiotherapist', 'medical', '+1234567893', 'emma.wilson@club.com', '2023-03-20', 'active');

-- Insert sample players
INSERT INTO players (name, position, age, team_id, jersey_number, height, weight, nationality, date_of_birth, stats, contract, status) VALUES
('David Martinez', 'Goalkeeper', 28, 1, 1, 185.0, 78.5, 'Spain', '1996-05-15', 'Clean sheets: 12, Saves: 145', '2024-2027', 'active'),
('Carlos Rodriguez', 'Defender', 25, 1, 3, 180.0, 75.0, 'Argentina', '1999-03-22', 'Tackles: 89, Interceptions: 67', '2023-2026', 'active'),
('James Wilson', 'Defender', 27, 1, 4, 183.0, 80.0, 'England', '1997-11-08', 'Headers won: 45, Clearances: 112', '2022-2025', 'active'),
('Marco Silva', 'Midfielder', 24, 1, 8, 175.0, 70.0, 'Brazil', '2000-07-12', 'Passes: 1234, Assists: 8', '2023-2027', 'active'),
('Alex Thompson', 'Midfielder', 26, 1, 10, 178.0, 72.5, 'USA', '1998-09-03', 'Goals: 5, Key passes: 67', '2022-2025', 'active'),
('Robert Johnson', 'Forward', 23, 1, 9, 182.0, 76.0, 'Canada', '2001-04-18', 'Goals: 15, Shots: 89', '2024-2028', 'active'),
('Luis Garcia', 'Forward', 29, 1, 11, 177.0, 74.0, 'Mexico', '1995-12-25', 'Goals: 12, Assists: 6', '2021-2024', 'active');

-- Insert sample matches
INSERT INTO matches (home_team, away_team, match_date, score, home_score, away_score, venue, match_type, status) VALUES
('Our Club', 'City United', '2024-09-20 15:00:00', '2-1', 2, 1, 'Home Stadium', 'league', 'completed'),
('Riverside FC', 'Our Club', '2024-09-27 19:30:00', NULL, NULL, NULL, 'Riverside Stadium', 'league', 'scheduled'),
('Our Club', 'Mountain Rangers', '2024-10-05 16:00:00', NULL, NULL, NULL, 'Home Stadium', 'cup', 'scheduled'),
('Valley Town', 'Our Club', '2024-10-12 14:00:00', NULL, NULL, NULL, 'Valley Arena', 'league', 'scheduled');

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_matches_date_desc ON matches(match_date DESC);
CREATE INDEX idx_players_name ON players(name);

-- Create views for common queries

-- View for active players with team information
CREATE VIEW active_players_view AS
SELECT 
    p.id,
    p.name,
    p.position,
    p.age,
    p.jersey_number,
    t.name as team_name,
    p.status
FROM players p
LEFT JOIN teams t ON p.team_id = t.id
WHERE p.status = 'active';

-- View for upcoming matches
CREATE VIEW upcoming_matches_view AS
SELECT 
    id,
    home_team,
    away_team,
    match_date,
    venue,
    match_type,
    status
FROM matches
WHERE match_date > NOW() AND status = 'scheduled'
ORDER BY match_date ASC;

-- View for recent match results
CREATE VIEW recent_results_view AS
SELECT 
    id,
    home_team,
    away_team,
    match_date,
    score,
    venue,
    match_type
FROM matches
WHERE status = 'completed' AND match_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY match_date DESC;

-- Update script to fix user roles mismatch
-- This script updates the existing users table to support all required roles

USE football_club_management;

-- First, add the new roles to the ENUM
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'coach', 'player', 'member', 'staff') DEFAULT 'member';

-- Update existing 'manager' role to 'admin' (if any exist)
UPDATE users SET role = 'admin' WHERE role = 'manager';

-- Show current users and their roles
SELECT username, email, role FROM users;