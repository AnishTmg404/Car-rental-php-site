
<?php
include './db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM students WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?msg=deleted");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center mt-5'>Error deleting record: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='text-center mt-5 text-danger'>No record selected for deletion.</div>";
}