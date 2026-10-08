# Class Activity 1: Build and Validate the Form

## How to run
Put the folder in your PHP server directory (e.g. `htdocs` in XAMPP) or run `php -S localhost:8000` inside it,
then open `http://localhost:8000/index.html`.

## Which rules are HTML5 and which are JavaScript?
| Field | Rule | Type |
|---|---|---|
| All essential fields | `required` | HTML5 |
| Email | `type="email"` | HTML5 |
| Student ID (8 digits), Telephone (07 + 8 digits) | `pattern` | HTML5 |
| First/Last name | `minlength="2"` `maxlength="30"` | HTML5 |
| Age (16-100), Appointment date (2026) | `min` / `max` | HTML5 |
| Program select, Gender radio group | `required` | HTML5 |
| At least two interests checked | counting checked boxes | **JavaScript** |
| Password strength (8+ chars, upper, lower, digit, special) | regex tests + `setCustomValidity` | **JavaScript** |
| Confirm password matches | comparison + `setCustomValidity` | **JavaScript** |
| Inline messages, red/green borders, focus on first invalid field, success message | DOM manipulation | **JavaScript** |

Note: `novalidate` on the form switches off the browser's default popups only; the HTML5 attributes still
work and script.js reads their result through `checkValidity()` / `validity`.

## Test cases
| # | Input | Expected result |
|---|---|---|
| 1 | Submit empty form | Errors under every field; focus jumps to Student ID |
| 2 | Student ID `1234` | "Student ID must be exactly 8 digits." |
| 3 | First name `A` | "First name must be at least 2 characters." |
| 4 | Email `john@` | "Enter a valid email..." |
| 5 | Phone `0812345678` | "Phone number must start with 07..." |
| 6 | Age `12` or `150` | "Age must be between 16 and 100." |
| 7 | Date `2025-05-01` | "Appointment date must be within 2026." |
| 8 | No gender chosen | "Please select a gender." |
| 9 | Only one interest ticked | "Please select at least two interests." |
| 10 | Password `abc123` | "Password needs at least 8 characters, an uppercase letter, a special character." Strength: Weak |
| 11 | Password `Secret@123`, confirm `Secret@124` | "Passwords do not match." |
| 12 | **Valid:** `20260123`, Jane, Doe, jane@uni.edu, `0712345678`, 20, Female, IT, `2026-03-15`, 2+ interests, `Secret@123` twice | Green success message, then request is sent to register.php |

## Inspecting the request (Network panel)
1. Press F12 -> **Network** tab -> tick **Preserve log**.
2. Submit the valid form (case 12).
3. Click the `register.php` request: check **Method = POST**, **Status = 200**.
4. Open the **Payload** tab to see all names/values (`student_number`, `interests[]`, `password`...).
   Note the password travels as plain text over HTTP, so a real site must use **HTTPS**.

## The server must validate again
Client-side validation is only for user convenience. Anyone can disable JavaScript, edit the HTML in DevTools
or send a request with curl/Postman. `register.php` therefore re-checks every rule, escapes output with
`htmlspecialchars()` (prevents XSS), whitelists select/radio/checkbox values and hashes the password with
`password_hash()`.

Try it: in DevTools delete the `pattern` attribute on Student ID, enter `abc`, submit, and watch
register.php reject it with "Registration Failed (server validation)".
