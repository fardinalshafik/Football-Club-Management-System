<?php
namespace App\Controllers;
use PDO;
class AuthController {
	private $db;

	public function __construct($database) {
		$this->db = $database;
	}

	public function register($username, $password, $email, $role = 'member') {
		// Validate email
		if (empty($email)) {
			return false;
		}
		// Check for duplicate email
		$stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
		$stmt->execute([$email]);
		if ($stmt->fetch()) {
			return false;
		}
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		$stmt = $this->db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
		return $stmt->execute([$username, $hashedPassword, $email, $role]);
	}

	public function login($username, $password) {
		$stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
		$stmt->execute([$username]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($user && password_verify($password, $user['password'])) {
			if (session_status() === PHP_SESSION_NONE) {
				session_start();
			}
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['username'] = $user['username'];
			$_SESSION['role'] = $user['role'];
			return true;
		}
		return false;
	}

	public function logout() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		session_unset();
		session_destroy();
	}

	public function isLoggedIn() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		return isset($_SESSION['user_id']);
	}

	public function getRole() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		return $_SESSION['role'] ?? null;
	}

	public function requireRole($role) {
		if ($this->getRole() !== $role) {
			header('Location: /public/login.php');
			exit();
		}
	}
}
