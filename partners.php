<?php
$db = new db_class();
if (!$db->connect()) {
    die("DB connection failed: " . $db->error);
}
$conn = $db->conn;

// Handle role update (optional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $newRole = trim($_POST['new_role'] ?? '');

    $stmt = $conn->prepare('UPDATE users SET role_status = ? WHERE id = ?');
    $stmt->bind_param('si', $newRole, $userId);
    $stmt->execute();
    $message = $stmt->affected_rows ? 'User role updated.' : 'Error updating role.';
    $stmt->close();
}

// Fetch all users
$result = $conn->query(
    "SELECT id, fname, lname, email, role_status, profile_pic
     FROM users
     WHERE role_status = 'partners'
     ORDER BY id"
);

$users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-top: 120px;
            font-family: Poppins, Arial, sans-serif;
            background: #f5f5f5;
        }
        h2 {
            font: 600 2rem/1.6 Poppins, sans-serif;
            color: #333;
            padding-left: 50px;
        }
        .partners-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 40px;
        }
        
        /* New Card Styling */
        .partner-card {
            width: 280px;
            height: 280px;
            background: white;
            border-radius: 32px;
            padding: 3px;
            position: relative;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 30px 30px -20px;
            transition: all 0.5s ease-in-out;
        }
        
        .partner-card:hover {
            border-top-right-radius: 55px; /* Changed from left to right */
            box-shadow: rgba(0, 0, 0, 0.2) 0px 40px 40px -20px;
        }
        
        .cardprofile {
            position: absolute;
            width: calc(100% - 6px);
            height: calc(100% - 6px);
            top: 3px;
            left: 3px;
            border-radius: 29px;
            overflow: hidden;
        }
        
        .mail {
            position: absolute;
            right: 2rem; /* Changed from right to left */
            top: 1.4rem;
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 4;
        }
        
        .mail svg {
            stroke: #333;
            stroke-width: 2px;
            transition: all 0.3s ease;
        }
        
        .mail svg:hover {
            stroke: #555;
            transform: scale(1.1);
        }
        
        .profile-pic {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            object-fit: cover;
            object-position: center center;
            border-radius: 29px;
            z-index: 1;
            border: 0px solid #333;
            overflow: hidden;
            transition: all 0.5s ease-in-out 0.2s, z-index 0.5s ease-in-out 0.2s;
        }
        
        .bottom {
            position: absolute;
            bottom: 3px;
            left: 3px;
            right: 3px;
            background: #333;
            top: 80%;
            border-radius: 29px;
            z-index: 2;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 5px 5px 0px inset;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.645, 0.045, 0.355, 1) 0s;
        }
        
        .partner-card:hover .bottom {
            top: 20%;
            border-radius: 29px 29px 29px 80px; /* Changed corner radius */
            transition: all 0.5s cubic-bezier(0.645, 0.045, 0.355, 1) 0.2s;
        }
        
        .partner-card:hover .profile-pic {
            width: 100px;
            height: 100px;
            aspect-ratio: 1;
            top: 10px;
            right: 10px; /* Changed from left to right */
            left: auto;
            border-radius: 50%;
            z-index: 3;
            border: 7px solid #333;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 5px 5px 0px;
            transition: all 0.5s ease-in-out, z-index 0.5s ease-in-out 0.1s;
        }
        
        .partner-card:hover .profile-pic:hover {
            transform: scale(1.1);
        }
        
        .partner-card:hover .profile-pic img {
            transform: scale(1.3);
            transition: all 0.5s ease-in-out 0.5s;
        }
        
        .content {
            position: absolute;
            bottom: 0;
            left: 1.5rem;
            right: 1.5rem;
            height: 160px;
            color: white;
        }
        
        .name {
            display: block;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 1rem;
        }
        
        .about-me {
            display: block;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            opacity: 0.8;
        }
        
        .bottom-bottom {
            position: absolute;
            bottom: 1rem;
            left: 1.5rem;
            right: 1.5rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .more-btn {
            background: white;
            color: #333;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            font-family: Poppins, sans-serif;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 5px 5px 0px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: -8px;
        }
        
        .more-btn:hover {
            background: #00317B;
            color: white;
            transform: translateY(-2px) rotate(90deg);
        }
        
        .more-btn::before,
        .more-btn::after {
            content: '';
            position: absolute;
            background-color: currentColor;
            width: 16px;
            height: 2px;
            transition: all 0.3s ease;
        }
        
        .more-btn::before {
            transform: rotate(90deg);
        }
        
        .more-btn:hover::before,
        .more-btn:hover::after {
            background-color: white;
        }

        /* Rest of the styles remain the same */
        .alert {
            background: #def;
            border: 1px solid #aaa;
            padding: 10px;
            margin: 10px auto;
            width: 50%;
            text-align: center;
        }
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 999;
            overflow-y: auto;
            padding: 20px;
        }
        .custom-modal-dialog {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100%;
            padding: 20px 0;
        }
        .custom-modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            position: relative;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }
        .close-modal:hover {
            color: #333;
        }
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100px;
        }
        .loading:after {
            content: "";
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top-color: #333;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<?php if (isset($message)): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="partners-cards">
<?php foreach ($users as $user): 
    $profilePath = (!empty($user['profile_pic']) && file_exists("uploads/" . $user['profile_pic']))
        ? "uploads/" . $user['profile_pic']
        : "../assets/profiles/default.png";
?>
    <div class="partner-card">
        <div class="cardprofile">
            <button class="mail" onclick="location.href='mailto:<?= htmlspecialchars($user['email']) ?>'">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                </svg>
            </button>

            <img class="profile-pic" src="<?= $profilePath ?>" alt="Profile Picture">

            <div class="bottom">
                <div class="content">
                    <span class="name"><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></span>
                    <span class="about-me">Role: <?= htmlspecialchars($user['role_status']) ?></span>
                </div>

                <div class="bottom-bottom">
                    <button type="button" class="more-btn openAjaxModal"
                            data-id="<?= $user['id'] ?>"></button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- AJAX Modal -->
<div id="ajaxModal" class="custom-modal">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content" id="ajaxModalContent">
            <span class="close-modal">&times;</span>
            <div class="loading"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("click", function(e) {
    // Open modal
    const btn = e.target.closest(".openAjaxModal");
    if (btn) {
        const userId = btn.getAttribute("data-id");
        const modal = document.getElementById("ajaxModal");
        const modalContent = document.getElementById("ajaxModalContent");

        // Reset scroll position
        modal.scrollTop = 0;
        
        modal.style.display = "block";
        document.body.style.overflow = "hidden";
        modalContent.innerHTML = '<span class="close-modal">&times;</span><div class="loading"></div>';

        fetch("partners_portfolio.php?user_id=" + userId)
            .then(res => res.text())
            .then(html => {
                modalContent.innerHTML = '<span class="close-modal">&times;</span>' + html;
                setTimeout(() => {
                    modal.scrollTop = 0;
                }, 10);
            })
            .catch(() => {
                modalContent.innerHTML = '<span class="close-modal">&times;</span><p>Error loading content.</p>';
            });
    }

    // Close modal
    if (e.target.classList.contains("close-modal") || e.target === document.getElementById("ajaxModal")) {
        document.getElementById("ajaxModal").style.display = "none";
        document.body.style.overflow = "auto";
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById("ajaxModal").style.display === "block") {
        document.getElementById("ajaxModal").style.display = "none";
        document.body.style.overflow = "auto";
    }
});
</script>

</body>
</html>