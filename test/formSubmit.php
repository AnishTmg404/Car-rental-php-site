
<?php
include './db.php';

if (isset($_POST['submit'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $address = $_POST['address'];

    // Insert query
    $sql = "INSERT INTO students (full_name, email, phone, gender, course, address)
            VALUES ('$full_name', '$email', '$phone', '$gender', '$course', '$address')";

   if ($conn->query($sql) === TRUE) {
    echo "<script>
        alert('Data has been recorded successfully!');
        window.location.href = 'index.php';
    </script>";
}else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
}
?>