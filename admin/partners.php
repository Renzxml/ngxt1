<?php

include 'header.php';

$db = new db_connect();
if (!$db->connect()) {
    die("DB connection failed: " . $db->error);
}
$conn = $db->conn;

// Update role
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_role'])) {
    $userId = $_POST['user_id']; // fixed from $_POST['id']
    $newRole = $_POST['new_role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $userId);

    if ($stmt->execute()) {
        $message = "User role updated.";
    } else {
        $message = "Error updating role.";
    }
}

// Fetch all users
$result = $conn->query("SELECT * FROM users");
$users = $result->fetch_all(MYSQLI_ASSOC);

?>

<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    margin-top: 120px;
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
}

h2 {
  font-size: 2rem;
  font-weight: 600;
  color: #333;
  font-family: 'Poppins', sans-serif;
  line-height: 1.6;
  padding-left: 50px;
}


.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}


.partners-cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 40px;
}

</style>

<h2>User Management</h2>

<?php if (!empty($message)): ?>
    <div style="background:#def;padding:10px;border:1px solid #aaa;margin:10px auto;width:50%;text-align:center;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div class="partners-cards">
    <?php foreach ($users as $user): ?>
        <?php
            $profilePath = !empty($user['profile_pic']) 
                ? htmlspecialchars($user['profile_pic']) 
                : 'https://res.cloudinary.com/YOUR_CLOUD_NAME/image/upload/v1/uploads/gallery/profile_photos/default.png';
            ?>

        <div class="partner-card">
            <div class="cardprofile">
                <div class="profile-pic">
                    <img src="<?= $profilePath ?>" alt="Profile Picture">
                </div>
                <div class="bottom">
                    <div class="content">
                        <span class="name"><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></span>
                        <span class="about-me"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="bottom-bottom">
                        <form action="" method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <!-- <select name="new_role">
                                <option value="pending" <?= $user['role_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="partners" <?= $user['role_status'] === 'partners' ? 'selected' : '' ?>>Partners</option>
                                <option value="terminated" <?= $user['role_status'] === 'terminated' ? 'selected' : '' ?>>Terminated</option>
                            </select>

                            <button type="submit" name="update_role" class="button">Update Role</button> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php';?>
