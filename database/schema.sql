-- Create the database
CREATE DATABASE IF NOT EXISTS quadribol;
USE quadribol;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    house_color VARCHAR(50),
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Players table
CREATE TABLE IF NOT EXISTS players (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    team_id INT,
    position VARCHAR(50) NOT NULL,
    agility INT DEFAULT 0,
    strength INT DEFAULT 0,
    speed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id)
);

-- Matches table
CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team1_id INT,
    team2_id INT,
    team1_score INT DEFAULT 0,
    team2_score INT DEFAULT 0,
    match_date DATETIME,
    status ENUM('scheduled', 'in_progress', 'completed') DEFAULT 'scheduled',
    winner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team1_id) REFERENCES teams(id),
    FOREIGN KEY (team2_id) REFERENCES teams(id),
    FOREIGN KEY (winner_id) REFERENCES teams(id)
);

-- Insert initial data for teams
INSERT INTO teams (name, house_color) VALUES
('Grifinória', '#740001'),
('Sonserina', '#1A472A'),
('Corvinal', '#0E1A40'),
('Lufa-Lufa', '#FFD800');

-- Insert sample players
INSERT INTO players (name, team_id, position, agility, strength, speed) VALUES
('Harry Potter', 1, 'Apanhador', 95, 75, 90),
('Draco Malfoy', 2, 'Apanhador', 85, 80, 85),
('Cho Chang', 3, 'Apanhadora', 88, 70, 88),
('Cedric Diggory', 4, 'Apanhador', 90, 85, 85);
