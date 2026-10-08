// ---------- Client-side validation (HTML5 rules + JavaScript-only rules) ----------
const form = document.getElementById('regForm');
const successMsg = document.getElementById('successMsg');
const password = document.getElementById('password');
const confirmPw = document.getElementById('confirmPassword');
const strengthEl = document.getElementById('strength');

const showError = (id, msg) => { document.getElementById(id + '-error').textContent = msg; };

// Turn the browser's HTML5 validity flags into clear messages
function html5Message(el) {
  const v = el.validity, label = el.dataset.label || el.name;
  if (v.valueMissing)    return `${label} is required.`;
  if (v.customError)     return el.validationMessage;          // set by JavaScript rules
  if (v.typeMismatch)    return `Enter a valid ${label.toLowerCase()} (e.g. name@university.edu).`;
  if (v.patternMismatch) return el.title;
  if (v.tooShort)        return `${label} must be at least ${el.minLength} characters.`;
  if (v.tooLong)         return `${label} must be at most ${el.maxLength} characters.`;
  if (v.rangeUnderflow || v.rangeOverflow)
    return el.dataset.rangeMsg || `${label} must be between ${el.min} and ${el.max}.`;
  return el.validationMessage;
}

// --- JavaScript-only rules ---
function passwordProblems(pw) {
  const p = [];
  if (pw.length < 8)        p.push('at least 8 characters');
  if (!/[A-Z]/.test(pw))    p.push('an uppercase letter');
  if (!/[a-z]/.test(pw))    p.push('a lowercase letter');
  if (!/[0-9]/.test(pw))    p.push('a number');
  if (!/[^A-Za-z0-9]/.test(pw)) p.push('a special character');
  return p;
}

function updateStrength() {
  const missing = passwordProblems(password.value).length;
  if (!password.value) { strengthEl.textContent = ''; strengthEl.className = 'strength'; return; }
  const [text, cls] = missing === 0 ? ['Strong', 'strong'] : missing <= 2 ? ['Medium', 'medium'] : ['Weak', 'weak'];
  strengthEl.textContent = 'Strength: ' + text;
  strengthEl.className = 'strength ' + cls;
}

function checkPasswordRules() {
  const problems = passwordProblems(password.value);
  password.setCustomValidity(password.value && problems.length ? 'Password needs ' + problems.join(', ') + '.' : '');
  confirmPw.setCustomValidity(confirmPw.value && confirmPw.value !== password.value ? 'Passwords do not match.' : '');
}

// Validate one normal field; returns true if valid
function validateField(el) {
  if (el === password || el === confirmPw) checkPasswordRules();
  const ok = el.checkValidity();
  showError(el.id, ok ? '' : html5Message(el));
  el.classList.toggle('invalid', !ok);
  el.classList.toggle('valid', ok);
  return ok;
}

// Radio group (HTML5 required) and checkbox group (JavaScript: at least two)
function validateRadios() {
  const ok = !!form.querySelector('input[name="gender"]:checked');
  showError('gender', ok ? '' : 'Please select a gender.');
  document.getElementById('gender').classList.toggle('invalid', !ok);
  return ok;
}
function validateCheckboxes() {
  const ok = form.querySelectorAll('input[name="interests[]"]:checked').length >= 2;
  showError('interests', ok ? '' : 'Please select at least two interests.');
  document.getElementById('interests').classList.toggle('invalid', !ok);
  return ok;
}

// Live feedback while the user types / leaves a field
const singles = [...form.querySelectorAll('input:not([type=radio]):not([type=checkbox]), select')];
singles.forEach(el => {
  el.addEventListener('blur',  () => validateField(el));
  el.addEventListener('input', () => { if (el.classList.contains('invalid')) validateField(el); });
});
password.addEventListener('input', () => { updateStrength(); if (confirmPw.value) validateField(confirmPw); });
form.querySelectorAll('input[name="gender"]').forEach(r => r.addEventListener('change', validateRadios));
form.querySelectorAll('input[name="interests[]"]').forEach(c => c.addEventListener('change', validateCheckboxes));

// Submit handler
form.addEventListener('submit', function (e) {
  e.preventDefault();                       // always stop first; we decide if it may go
  successMsg.hidden = true;

  // Validate everything, in the order the fields appear on the page
  const invalid = [];
  form.querySelectorAll('input, select').forEach(el => {
    if (el.type === 'radio') {
      if (el.id === 'genderMale' && !validateRadios()) invalid.push(el);
    } else if (el.type === 'checkbox') {
      if (el.id === 'intProg' && !validateCheckboxes()) invalid.push(el);
    } else if (!validateField(el)) {
      invalid.push(el);
    }
  });

  if (invalid.length) {                      // unsuccessful: focus the first invalid field
    invalid[0].focus();
    return;
  }

  // Successful: show message, then really submit so the request appears in the Network panel
  successMsg.hidden = false;
  successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
  setTimeout(() => form.submit(), 1500);
});
