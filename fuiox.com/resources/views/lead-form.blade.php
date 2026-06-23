<!DOCTYPE html>
<html>
<head>
    <title>Lead Form</title>
</head>
<body>

<h2>Lead Form</h2>

<form method="POST" action="/submit-lead">
    @csrf

    <input type="text" name="name" placeholder="Name" required><br><br>

    <input type="text" name="whatsapp" placeholder="WhatsApp Number" required><br><br>

    <input type="email" name="email" placeholder="Email" required><br><br>

    <input type="text" name="requirement" placeholder="Requirement" required><br><br>

    <select name="occupation" required>
        <option value="student">Student</option>
        <option value="employee">Employee</option>
        <option value="none">None</option>
    </select><br><br>

    <button type="submit">Submit</button>

</form>

</body>
</html>