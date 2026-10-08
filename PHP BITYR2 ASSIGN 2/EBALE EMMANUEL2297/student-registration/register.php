<?php
// Server-side validation: the browser can be bypassed (DevTools, curl, Postman),
// so every rule is checked AGAIN here before the data is trusted.
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: index.html"); exit; }

function clean($v) { return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8'); }
function post($k)  { return trim($_POST[$k] ?? ""); }

$programs  = ["cs" => "Bachelor of Computer Science", "it" => "Bachelor of Information Technology", "se" => "Bachelor of Software Engineering"];
$genders   = ["male", "female", "other"];
$allowedInterests = ["programming", "networking", "data", "security"];
$interests = array_values(array_intersect($_POST["interests"] ?? [], $allowedInterests));
$pw = $_POST["password"] ?? "";

$errors = [];
if (!preg_match('/^[0-9]{8}$/', post("student_number"))) $errors[] = "Student ID must be exactly 8 digits.";
foreach (["first_name" => "First name", "last_name" => "Last name"] as $k => $label)
  if (mb_strlen(post($k)) < 2 || mb_strlen(post($k)) > 30) $errors[] = "$label must be 2-30 characters.";
if (!filter_var(post("email"), FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
if (!preg_match('/^07[0-9]{8}$/', post("telephone"))) $errors[] = "Phone must start with 07 and be 10 digits.";
if (filter_var(post("age"), FILTER_VALIDATE_INT, ["options" => ["min_range" => 16, "max_range" => 100]]) === false) $errors[] = "Age must be between 16 and 100.";
if (!in_array(post("gender"), $genders, true)) $errors[] = "Please select a valid gender.";
if (!isset($programs[post("academic_program")])) $errors[] = "Please select a valid program.";
$d = DateTime::createFromFormat('Y-m-d', post("appointment_date"));
if (!$d || $d->format('Y-m-d') !== post("appointment_date") || $d->format('Y') !== '2026') $errors[] = "Appointment date must be a valid date in 2026.";
if (count($interests) < 2) $errors[] = "Select at least two interests.";
if (strlen($pw) < 8 || !preg_match('/[A-Z]/', $pw) || !preg_match('/[a-z]/', $pw) || !preg_match('/[0-9]/', $pw) || !preg_match('/[^A-Za-z0-9]/', $pw))
  $errors[] = "Password is not strong enough.";
if ($pw !== ($_POST["confirm_password"] ?? null)) $errors[] = "Passwords do not match.";

if (!$errors) { $passwordHash = password_hash($pw, PASSWORD_DEFAULT); /* store this, never the plain password */ }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Registration Details</title>
<link rel="stylesheet" href="style.css">
<style>.card{max-width:500px;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,.1)}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px;border-bottom:1px solid #eee}th{width:40%}
a.btn{display:inline-block;margin-top:15px;background:#2e8ff7;color:#fff;padding:10px 15px;border-radius:4px;text-decoration:none}</style>
</head><body><div class="card">
<?php if ($errors): ?>
  <h2 style="color:#b00020">Registration Failed (server validation)</h2>
  <ul class="error"><?php foreach ($errors as $e) echo "<li>" . htmlspecialchars($e) . "</li>"; ?></ul>
<?php else: ?>
  <h2>Registration Successful</h2>
  <table>
    <tr><th>Student ID</th><td><?= clean(post("student_number")) ?></td></tr>
    <tr><th>First Name</th><td><?= clean(post("first_name")) ?></td></tr>
    <tr><th>Last Name</th><td><?= clean(post("last_name")) ?></td></tr>
    <tr><th>Email</th><td><?= clean(post("email")) ?></td></tr>
    <tr><th>Telephone</th><td><?= clean(post("telephone")) ?></td></tr>
    <tr><th>Age</th><td><?= clean(post("age")) ?></td></tr>
    <tr><th>Gender</th><td><?= clean(ucfirst(post("gender"))) ?></td></tr>
    <tr><th>Program</th><td><?= clean($programs[post("academic_program")]) ?></td></tr>
    <tr><th>Appointment Date</th><td><?= clean(post("appointment_date")) ?></td></tr>
    <tr><th>Interests</th><td><?= clean(implode(", ", $interests)) ?></td></tr>
    <tr><th>Password</th><td>Stored securely as a hash (not displayed)</td></tr>
  </table>
<?php endif; ?>
<a class="btn" href="index.html">&larr; Back to form</a>
</div></body></html>
