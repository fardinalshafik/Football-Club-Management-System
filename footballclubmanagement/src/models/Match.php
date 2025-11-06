<?php

class MatchModel {
    private $conn;
    private $table_name = "matches";

    public $id;
    public $home_team;
    public $away_team;
    public $match_date;
    public $score;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (home_team, away_team, match_date, score) VALUES (:home_team, :away_team, :match_date, :score)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':home_team', $this->home_team);
        $stmt->bindParam(':away_team', $this->away_team);
        $stmt->bindParam(':match_date', $this->match_date);
        $stmt->bindParam(':score', $this->score);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY match_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET home_team = :home_team, away_team = :away_team, match_date = :match_date, score = :score WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':home_team', $this->home_team);
        $stmt->bindParam(':away_team', $this->away_team);
        $stmt->bindParam(':match_date', $this->match_date);
        $stmt->bindParam(':score', $this->score);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE home_team LIKE :keywords OR away_team LIKE :keywords ORDER BY match_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $keywords = "%{$keywords}%";
        $stmt->bindParam(':keywords', $keywords);
        $stmt->execute();
        return $stmt;
    }
}