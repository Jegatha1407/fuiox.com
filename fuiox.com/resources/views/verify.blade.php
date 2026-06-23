<!DOCTYPE html>
<html>
<head>
    <title>Verify Account</title>
</head>
<body>

<h2>Account Verification</h2>

@if(session('error'))
    <p style="color:red">{{ session('error') }}</p>
@endif

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

<form method="POST" action="/verify-submit">
    @csrf

    <input type="hidden" name="id" value="{{ $id }}">

    <input type="text" name="name" placeholder="Name" required><br><br>

    <input type="text" name="phone" placeholder="Phone" required><br><br>

    <input type="email" name="email" placeholder="Email" required><br><br>

    <button type="submit">Submit</button>
</form>

</body>
</html>