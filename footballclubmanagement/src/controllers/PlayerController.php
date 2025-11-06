<?php
namespace App\Controllers;
use PDO;
use Exception;

class PlayerController
{
	protected $db;

	public function __construct($database)
	{
		$this->db = $database;
	}

	public function getAllPlayers()
	{
		$stmt = $this->db->query("SELECT * FROM players");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			try {
				$stmt = $this->db->prepare("INSERT INTO players (name, position, age, nationality, stats, contract) VALUES (?, ?, ?, ?, ?, ?)");
				$result = $stmt->execute([
					$_POST['name'],
					$_POST['position'],
					$_POST['age'],
					$_POST['nationality'] ?? null,
					$_POST['stats'] ?? null,
					$_POST['contract'] ?? null
				]);
				
				if ($result) {
					header('Location: players.php');
					exit();
				} else {
					throw new Exception("Failed to insert player");
				}
			} catch (Exception $e) {
				error_log("Player creation error: " . $e->getMessage());
				return false;
			}
		} else {
			require_once '../src/views/players/add.php';
		}
	}

	public function edit($id)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			try {
				$stmt = $this->db->prepare("UPDATE players SET name = ?, position = ?, age = ?, nationality = ?, stats = ?, contract = ? WHERE id = ?");
				$result = $stmt->execute([
					$_POST['name'],
					$_POST['position'],
					$_POST['age'],
					$_POST['nationality'] ?? null,
					$_POST['stats'] ?? null,
					$_POST['contract'] ?? null,
					$id
				]);
				
				if ($result) {
					header('Location: players.php');
					exit();
				}
			} catch (Exception $e) {
				error_log("Player update error: " . $e->getMessage());
			}
		} else {
			$stmt = $this->db->prepare("SELECT * FROM players WHERE id = ?");
			$stmt->execute([$id]);
			$player = $stmt->fetch(PDO::FETCH_ASSOC);
			require '../src/views/players/edit.php';
		}
	}

	public function delete($id)
	{
		$stmt = $this->db->prepare("DELETE FROM players WHERE id = ?");
		$stmt->execute([$id]);
		header('Location: /players.php');
	}

	public function search($query)
	{
		$stmt = $this->db->prepare("SELECT * FROM players WHERE name LIKE ? OR position LIKE ?");
		$stmt->execute(["%$query%", "%$query%"]);
		$players = $stmt->fetchAll(PDO::FETCH_ASSOC);
		require '../src/views/players/list.php';
	}
}
