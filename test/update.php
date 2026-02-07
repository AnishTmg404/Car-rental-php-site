<?php
include './db.php';

// Make sure 'id' is provided
if (!isset($_GET['id'])) {
    die("<div class='text-center mt-5 text-danger'>No student selected! Go back to <a href='display.php'>list</a>.</div>");
}

$id = intval($_GET['id']); // prevent injection

// Fetch selected student
$sql = "SELECT * FROM students WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("<div class='text-center mt-5 text-danger'>No record found! Go back to <a href='index.php'>list</a>.</div>");
}

$row = $result->fetch_assoc();

// Handle update
if (isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $address = $_POST['address'];

    $update = "UPDATE students SET 
        full_name = '$full_name',
        email = '$email',
        phone = '$phone',
        gender = '$gender',
        course = '$course',
        address = '$address'
        WHERE id = $id";

    if ($conn->query($update) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Error updating record: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h4>Update Student Details</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= $row['full_name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= $row['phone'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?= ($row['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($row['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Course</label>
                    <input type="text" name="course" class="form-control" value="<?= $row['course'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="3" class="form-control" required><?= $row['address'] ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Back</a>
                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>