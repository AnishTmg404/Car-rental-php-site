index.php

<?php
include "./db.php";
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>

    <div class="container">
        <div class="row justify-content-center py-5">
            <div class="col-lg-6 col-md-8 col-12">
                <div class="card shadow-lg rounded-4 border-0">
                    <div class="card-body p-4">
                        <form method="POST" action="./formSubmit.php">
                            <h2 class="mb-4 text-center">Student Registration Form</h2>
                            
                            <div class="mb-4">
                                <label>Full Name:</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>

                            <div class="mb-4">
                                <label>Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-4">
                                <label>Phone:</label>
                                <input type="text" class="form-control" name="phone" required>
                            </div>

                            <div class="mb-4">
                                <label>Gender:</label>
                                <select name="gender" required class="form-select">
                                    <option value="">-- Select Gender --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label>Course:</label>
                                <input type="text" name="course" required class="form-control">
                            </div>

                            <div class="mb-4">
                                <label>Address:</label>
                                <textarea name="address" rows="3" required class="form-control"></textarea>
                            </div>

                            <input type="submit" name="submit" value="Submit" class="btn btn-success">
                            <a href="./select.php" class="btn btn-secondary mx-2">Manage data</a>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>s