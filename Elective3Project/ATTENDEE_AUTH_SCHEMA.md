# Attendee Authentication Schema

## Overview
This document defines the complete schema for the EventLink attendee login/register system. All form fields, validation rules, database constraints, and error messages are documented here for consistency and reference.

---

## 1. REGISTER FORM SCHEMA

### Form Fields Reference

| Field | Type | Required | Min/Max | Pattern | Unique | Placeholder |
|-------|------|----------|---------|---------|--------|-------------|
| **username** | text | ✅ Yes | 3/50 | `[a-zA-Z0-9_]+` | ✅ DB | `attendee123` |
| **full_name** | text | ✅ Yes | 2/100 | `[a-zA-Z\s]+` | ❌ No | `Juan Dela Cruz` |
| **email** | email | ✅ Yes | -/100 | RFC 5322 | ✅ DB | `juan@example.com` |
| **contact_number** | text | ✅ Yes | 11/11 | `09[0-9]{9}` | ✅ DB | `09123456789` |
| **password** | password | ✅ Yes | 8/- | `(?=.*[a-z])(?=.*[A-Z])(?=.*\d)` | ❌ No | `Create a strong password` |
| **password_confirmation** | password | ✅ Yes | 8/- | Must match password | ❌ No | `Re-enter password` |

### Field Specifications

#### Username
- **Rules**: Required, alphanumeric + underscore only, 3-50 characters, unique in database
- **Regex Pattern**: `^[a-zA-Z0-9_]{3,50}$`
- **Autocomplete**: `username`
- **Error Messages**:
  - "Username must be at least 3 characters." (min length)
  - "Username must contain only letters, numbers, and underscores." (regex)
  - "The username has already been taken." (unique constraint)

#### Full Name
- **Rules**: Required, letters and spaces only, 2-100 characters
- **Regex Pattern**: `^[a-zA-Z\s]{2,100}$`
- **Autocomplete**: `name`
- **Error Messages**:
  - "Full name must be at least 2 characters." (min length)
  - "Full name must contain only letters and spaces." (regex)

#### Email Address
- **Rules**: Required, valid email format, 100 char max, unique in database
- **Format**: RFC 5322 email validation
- **Autocomplete**: `email`
- **Error Messages**:
  - "The email must be a valid email address." (format)
  - "The email has already been taken." (unique constraint)

#### Contact Number
- **Rules**: Required, Philippine format (11 digits starting with 09), numeric only, unique in database
- **Regex Pattern**: `^09[0-9]{9}$`
- **Length**: Exactly 11 digits
- **Autocomplete**: `tel`
- **Input Mode**: `numeric`
- **Error Messages**:
  - "Contact number must be 11 digits starting with 09." (format)
  - "This contact number is already registered." (unique constraint)
- **Example**: `09123456789`

#### Password
- **Rules**: Required, minimum 8 characters, must include uppercase, lowercase, and numeric characters
- **Regex Pattern**: `^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$`
- **Autocomplete**: `new-password`
- **Strength Requirements**:
  - ✅ At least one lowercase letter (a-z)
  - ✅ At least one uppercase letter (A-Z)
  - ✅ At least one number (0-9)
  - ✅ Minimum 8 characters
- **Error Messages**:
  - "Password must contain uppercase, lowercase, and numeric characters." (strength)

#### Password Confirmation
- **Rules**: Required, must match password field exactly
- **Validation**: `confirmed` rule
- **Autocomplete**: `new-password`
- **Error Messages**:
  - "Passwords do not match." (confirmed rule)
  - "Please confirm your password." (required)

### Register Form HTML Structure
```html
<form method="POST" action="{{ route('portal.register.store') }}">
  <label>Username
    <input type="text" name="username" pattern="[a-zA-Z0-9_]{3,50}" required />
  </label>
  <label>Full Name
    <input type="text" name="full_name" pattern="[a-zA-Z\s]{2,100}" required />
  </label>
  <label>Email Address
    <input type="email" name="email" required />
  </label>
  <label>Contact Number
    <input type="text" name="contact_number" pattern="09[0-9]{9}" inputmode="numeric" maxlength="11" required />
  </label>
  <label>Password
    <input type="password" name="password" minlength="8" required />
    <small>Min 8 characters, uppercase, lowercase, and number required</small>
  </label>
  <label>Confirm Password
    <input type="password" name="password_confirmation" required />
  </label>
  <button type="submit">Create Account</button>
</form>
```

---

## 2. LOGIN FORM SCHEMA

### Form Fields Reference

| Field | Type | Required | Max Length | Accepts | Placeholder |
|-------|------|----------|------------|---------|-------------|
| **login** | text | ✅ Yes | 100 | Email OR Username | `attendee@example.com or attendee123` |
| **password** | password | ✅ Yes | - | Min 8 chars | `Enter your password` |

### Field Specifications

#### Login (Email or Username)
- **Rules**: Required, accepts either valid email address OR registered username, max 100 characters
- **Smart Detection**: System auto-detects field type:
  - If contains `@` and valid email format → queries `users.email`
  - Otherwise → queries `users.username`
- **Autocomplete**: `username`
- **Error Messages**:
  - "Please enter your email or username." (required)
  - "Invalid credentials. Please check your username/email and password." (auth failed)

#### Password
- **Rules**: Required, minimum 8 characters
- **Autocomplete**: `current-password`
- **Error Messages**:
  - "Password is required." (required)
  - "Invalid credentials. Please check your username/email and password." (auth failed)

### Login Form HTML Structure
```html
<form method="POST" action="{{ route('portal.login.store') }}">
  <label for="login">Email or Username
    <input id="login" type="text" name="login" maxlength="100" required />
  </label>
  <label for="password">Password
    <input id="password" type="password" name="password" minlength="8" required />
  </label>
  <button type="submit">Log in</button>
</form>
```

---

## 3. DATABASE SCHEMA

### Users Table Structure
```sql
CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL COMMENT 'Alphanumeric + underscore, 3-50 chars',
  full_name VARCHAR(100) NOT NULL COMMENT 'Letters and spaces, 2-100 chars',
  email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Valid email format',
  contact_number VARCHAR(11) UNIQUE NULLABLE COMMENT 'Philippine format: 09XXXXXXXXX',
  role ENUM('admin', 'attendee') DEFAULT 'attendee' COMMENT 'User role',
  password VARCHAR(255) NOT NULL COMMENT 'Hashed password (bcrypt)',
  remember_token VARCHAR(100) NULLABLE COMMENT 'Remember-me token',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE INDEX idx_username (username),
  UNIQUE INDEX idx_email (email),
  UNIQUE INDEX idx_contact_number (contact_number),
  INDEX idx_role (role)
);
```

### Constraints
- **username**: UNIQUE, NOT NULL, VARCHAR(50)
- **email**: UNIQUE, NOT NULL, VARCHAR(100)
- **contact_number**: UNIQUE, NULLABLE, VARCHAR(11)
- **role**: DEFAULT 'attendee', ENUM('admin', 'attendee')

---

## 4. CONTROLLER VALIDATION RULES

### Register Validation (AttendeeAuthController@register)
```php
$validated = $request->validate([
    'username' => [
        'required',
        'string',
        'min:3',
        'max:50',
        'regex:/^[a-zA-Z0-9_]+$/',
        'unique:users,username',
    ],
    'full_name' => [
        'required',
        'string',
        'min:2',
        'max:100',
        'regex:/^[a-zA-Z\s]+$/',
    ],
    'email' => [
        'required',
        'email',
        'max:100',
        'unique:users,email',
    ],
    'contact_number' => [
        'required',
        'string',
        'size:11',
        'regex:/^09[0-9]{9}$/',
        'unique:users,contact_number',
    ],
    'password' => [
        'required',
        'string',
        'min:8',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
        'confirmed',
    ],
    'password_confirmation' => ['required', 'string'],
], [
    // Custom error messages defined for better UX
]);
```

### Login Validation (AttendeeAuthController@login)
```php
$validated = $request->validate([
    'login' => ['required', 'string', 'max:100'],
    'password' => ['required', 'string', 'min:8'],
], [
    // Custom error messages defined for better UX
]);

// Smart field detection
$field = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

// Authenticate with role check
Auth::attempt([
    $field => $validated['login'],
    'password' => $validated['password'],
    'role' => 'attendee'
], true);
```

---

## 5. VALIDATION RULES BREAKDOWN

### Username
```
Required | String | Min: 3 | Max: 50 | Pattern: ^[a-zA-Z0-9_]+$ | Unique
```

### Full Name
```
Required | String | Min: 2 | Max: 100 | Pattern: ^[a-zA-Z\s]+$ | Not Unique
```

### Email
```
Required | Email Format | Max: 100 | Unique
```

### Contact Number
```
Required | String | Exact: 11 | Pattern: ^09[0-9]{9}$ | Unique
```

### Password (Register)
```
Required | String | Min: 8 | Pattern: (?=.*[a-z])(?=.*[A-Z])(?=.*\d) | Confirmed
```

### Login Field
```
Required | String | Max: 100 | Email OR Username (auto-detected)
```

### Password (Login)
```
Required | String | Min: 8 | Hashed comparison
```

---

## 6. ERROR MESSAGES REFERENCE

### Register Form Errors
| Field | Error | Message |
|-------|-------|---------|
| username | required | The username field is required. |
| username | min | Username must be at least 3 characters. |
| username | max | Username may not be greater than 50 characters. |
| username | regex | Username must contain only letters, numbers, and underscores. |
| username | unique | The username has already been taken. |
| full_name | required | The full name field is required. |
| full_name | min | Full name must be at least 2 characters. |
| full_name | max | Full name may not be greater than 100 characters. |
| full_name | regex | Full name must contain only letters and spaces. |
| email | required | The email field is required. |
| email | email | The email must be a valid email address. |
| email | max | The email may not be greater than 100 characters. |
| email | unique | The email has already been taken. |
| contact_number | required | The contact number field is required. |
| contact_number | size | Contact number must be exactly 11 digits. |
| contact_number | regex | Contact number must be 11 digits starting with 09. |
| contact_number | unique | This contact number is already registered. |
| password | required | The password field is required. |
| password | min | The password must be at least 8 characters. |
| password | regex | Password must contain uppercase, lowercase, and numeric characters. |
| password | confirmed | The password confirmation does not match. |

### Login Form Errors
| Field | Error | Message |
|-------|-------|---------|
| login | required | Please enter your email or username. |
| login | max | Login field cannot exceed 100 characters. |
| password | required | Password is required. |
| password | min | Password must be at least 8 characters. |
| auth | failed | Invalid credentials. Please check your username/email and password. |

---

## 7. UX CONSIDERATIONS

### Form Behavior
- **Auto-detection**: Login field automatically detects email vs username format
- **Real-time Feedback**: Client-side HTML5 validation provides instant feedback
- **Clear Placeholders**: All fields have contextual placeholder text
- **Helper Text**: Password field includes "Min 8 characters, uppercase, lowercase, and number required"
- **Input Modes**: Contact number uses `inputmode="numeric"` for mobile UX

### Mobile Optimization
- `autocomplete` attributes enable auto-fill on mobile/password managers
- `inputmode="numeric"` triggers numeric keyboard for phone input
- `maxlength` and `pattern` attributes provide soft input restrictions
- Responsive form layout adapts to mobile viewports

### Accessibility
- All inputs have associated `<label>` elements
- Error messages linked to specific fields
- Pattern/title attributes provide context for regex validation
- Semantic HTML structure

---

## 8. EXAMPLE VALID INPUTS

### Register Form Examples
```
Username: john_doe123
Full Name: John Doe
Email: john.doe@example.com
Contact Number: 09987654321
Password: StrongPass123
Password Confirmation: StrongPass123
```

```
Username: maria_santos_45
Full Name: Maria Santos Garcia
Email: maria@company.com.ph
Contact Number: 09123456789
Password: MyP@ss2024
Password Confirmation: MyP@ss2024
```

### Login Form Examples
```
Login: john_doe123
Password: StrongPass123
```

```
Login: john.doe@example.com
Password: StrongPass123
```

---

## 9. INVALID INPUTS (Examples)

| Field | Invalid Input | Reason |
|-------|---------------|--------|
| username | `ab` | Less than 3 characters |
| username | `john-doe` | Contains hyphen (only alphanumeric + underscore allowed) |
| username | `john.doe` | Contains period (only alphanumeric + underscore allowed) |
| full_name | `John123` | Contains numbers |
| full_name | `J` | Less than 2 characters |
| email | `invalid-email` | Missing @ and domain |
| contact_number | `9123456789` | Missing leading 0 |
| contact_number | `0912345678` | Only 10 digits (needs 11) |
| contact_number | `09123456789123` | More than 11 digits |
| contact_number | `09-123-456-789` | Contains hyphens |
| password | `short1` | Only 7 characters (needs 8) |
| password | `nouppercase123` | No uppercase letter |
| password | `NOLOWERCASE123` | No lowercase letter |
| password | `NoNumbers` | No numeric character |

---

## 10. IMPLEMENTATION CHECKLIST

- ✅ Register form has all 6 required fields with proper validation
- ✅ Login form supports email OR username with auto-detection
- ✅ Password strength requirements enforced (uppercase + lowercase + number)
- ✅ Contact number validation for Philippine format (09XXXXXXXXX)
- ✅ Username pattern restricted to alphanumeric + underscore
- ✅ Full name pattern restricted to letters + spaces
- ✅ All fields have clear error messages
- ✅ Database constraints enforce uniqueness
- ✅ HTML5 input attributes provide client-side validation
- ✅ Autocomplete attributes optimize mobile experience
- ✅ Role-based access control enforced (role: attendee)
- ✅ Session handling with regeneration after login

---

## 11. RELATED FILES

- **Controller**: `app/Http/Controllers/AttendeeAuthController.php`
- **Views**: 
  - `resources/views/portal/register.blade.php`
  - `resources/views/portal/login.blade.php`
- **Model**: `app/Models/User.php`
- **Migration**: `database/migrations/2026_04_29_000001_add_attendee_profile_fields_to_users_table.php`
- **Routes**: `routes/web.php` (portal prefix routes)

---

**Last Updated**: April 29, 2026  
**Schema Version**: 1.0  
**Status**: Active ✅
