<?php
namespace App\Controllers;
use PDO;

class MatchController
{
	protected $db;

	public function __construct($database)
	{
		$this->db = $database;
	}

	public function getAllMatches()
	{
		$stmt = $this->db->query("SELECT * FROM matches");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function create()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$stmt = $this->db->prepare("INSERT INTO matches (home_team, away_team, match_date, score) VALUES (?, ?, ?, ?)");
			$stmt->execute([
				$_POST['home_team'],
				$_POST['away_team'],
				$_POST['match_date'],
				$_POST['score']
			]);
			header('Location: /matches.php');
		} else {
			require_once '../src/views/matches/add.php';
		}
	}

	public function edit($id)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$stmt = $this->db->prepare("UPDATE matches SET home_team = ?, away_team = ?, match_date = ?, score = ? WHERE id = ?");
			$stmt->execute([
				$_POST['home_team'],
				$_POST['away_team'],
				$_POST['match_date'],
				$_POST['score'],
				$id
			]);
			header('Location: /matches.php');
		} else {
			$stmt = $this->db->prepare("SELECT * FROM matches WHERE id = ?");
			$stmt->execute([$id]);
			$match = $stmt->fetch(PDO::FETCH_ASSOC);
			require '../src/views/matches/edit.php';
		}
	}

	public function delete($id)
	{
		$stmt = $this->db->prepare("DELETE FROM matches WHERE id = ?");
		$stmt->execute([$id]);
		header('Location: /matches.php');
	}

	public function search($query)
	{
		$stmt = $this->db->prepare("SELECT * FROM matches WHERE home_team LIKE ? OR away_team LIKE ? OR match_date LIKE ?");
		$stmt->execute(["%$query%", "%$query%", "%$query%"]);
		$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
		require '../src/views/matches/list.php';
	}

	public function addMatch($data)
	{
		$stmt = $this->db->prepare("INSERT INTO matches (home_team, away_team, match_date, score) VALUES (?, ?, ?, ?)");
		return $stmt->execute([
			$data['home_team'],
			$data['away_team'], 
			$data['match_date'],
			$data['score'] ?? null
		]);
	}

	public function editMatch($data)
	{
		$stmt = $this->db->prepare("UPDATE matches SET home_team = ?, away_team = ?, match_date = ?, score = ? WHERE id = ?");
		return $stmt->execute([
			$data['home_team'],
			$data['away_team'],
			$data['match_date'],
			$data['score'] ?? null,
			$data['id']
		]);
	}

	public function deleteMatch($id)
	{
		$stmt = $this->db->prepare("DELETE FROM matches WHERE id = ?");
		return $stmt->execute([$id]);
	}
}
