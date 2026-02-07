<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
        $phone = filter_input(INPUT_POST, "phone", FILTER_SANITIZE_NUMBER_INT);


        if ($name && $email && $phone) {
            echo "Contact added: $name, $phone, $email";
        } else {
            echo "All detail much be filled";
        }
        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form action="" method="POST">
    <label for="name">Name:</label>
    <input type="text" name="name" ><br>

    <label for="email">Email:</label>
    <input type="email" name="email" ><br>

    <label for="phone">Phone</label>
    <input type="text" name="phone" ><br>

    <label for="img">Contact Image:</label>
    <input type="file" name="image" accept="image/*" required><br>

    <button type="submit">Add Contact</button>
</form>
</body>
</html>