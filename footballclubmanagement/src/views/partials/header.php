<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php<?php echo isset($_SESSION['role']) ? '?view=' . $_SESSION['role'] : ''; ?>">
            <i class="fas fa-futbol me-2"></i>Football Club Manager
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php<?php echo isset($_SESSION['role']) ? '?view=' . $_SESSION['role'] : ''; ?>">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="players.php">
                        <i class="fas fa-users me-1"></i>Players
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="matches.php">
                        <i class="fas fa-calendar-alt me-1"></i>Matches
                    </a>
                </li>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="staff.php">
                        <i class="fas fa-user-tie me-1"></i>Staff
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'coach'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus-circle me-1"></i>Add New
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="players_add.php">
                            <i class="fas fa-user-plus me-2"></i>Add Player
                        </a></li>
                        <li><a class="dropdown-item" href="matches_add.php">
                            <i class="fas fa-calendar-plus me-2"></i>Add Match
                        </a></li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a class="dropdown-item" href="staff_add.php">
                            <i class="fas fa-user-tie me-2"></i>Add Staff
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <?php 
                            $roleIcons = [
                                'admin' => 'fas fa-crown',
                                'coach' => 'fas fa-chalkboard-teacher',
                                'player' => 'fas fa-running',
                                'staff' => 'fas fa-user-tie',
                                'member' => 'fas fa-users'
                            ];
                            $userRole = $_SESSION['role'] ?? 'member';
                            ?>
                            <i class="<?php echo $roleIcons[$userRole] ?? 'fas fa-user-circle'; ?> me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                            <small class="ms-1 text-muted">(<?php echo ucfirst($userRole); ?>)</small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>