# EveLink - Laravel Features Code Review

This document provides a comprehensive review of all required Laravel features used in the EveLink project for live demonstration.

---

## 1. MIGRATIONS — Database Schema Management

**Purpose:** Creating and managing the database schema using Laravel migrations.

### Migration Files Located:
```
database/migrations/
```

### Key Migration Files:

#### `create_users_table.php`
- **File:** `database/migrations/0001_01_01_000000_create_users_table.php`
- **Schema Definition:** Creates the `users` table with columns:
  - `id` (primary key)
  - `username`, `full_name`, `email`, `contact_number`
  - `role` (admin/attendee)
  - `password`, `remember_token`
  - Timestamps

#### `create_events_table.php`
- **File:** `database/migrations/create_events_table.php`
- **Schema Definition:** Creates the `events` table with:
  - `id` (primary key)
  - `event_name`, `description`, `event_date`, `venue`
  - `start_time`, `end_time`, `max_slots`
  - Timestamps

#### `create_registrations_table.php`
- **File:** `database/migrations/create_registrations_table.php`
- **Schema Definition:** Creates the `registrations` table with:
  - `id` (primary key)
  - `event_id` (foreign key to events)
  - `first_name`, `last_name`, `email`, `contact_number`
  - `attendance_status` (Present/Absent/Pending)
  - Timestamps

#### Profile Enhancement Migrations
- `2026_04_29_000001_add_attendee_profile_fields_to_users_table.php`
- `2026_04_29_000001_add_present_at_to_registrations_table.php`

**How to Run:**
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
```

---

## 2. ELOQUENT ORM — Database Interactions & Relationships

**Purpose:** All database interactions use Eloquent ORM, not raw SQL.

### Model Files:

#### `User` Model
- **File:** `app/Models/User.php`
- **Extends:** `Authenticatable` (Eloquent)
- **Traits:** `HasFactory`, `Notifiable`
- **Key Methods:**
  - `getDisplayNameAttribute()` — Accessor for display name
  - `isAdmin()` — Check if user is admin
  - `isAttendee()` — Check if user is attendee
- **Password Casting:** `'password' => 'hashed'`

#### `Event` Model
- **File:** `app/Models/Event.php`
- **Extends:** `Illuminate\Database\Eloquent\Model`
- **Relationships:**
  ```php
  public function registrations(): HasMany
  {
      return $this->hasMany(Registration::class);
  }
  ```
- **Key Accessors:**
  - `getRegisteredCountAttribute()` — Count registrations
  - `getRemainingSlotsAttribute()` — Calculate available slots
  - `getStatusAttribute()` — Determine event status (Open/Full/Concluded)
  - `getFormattedStartTimeAttribute()` — Format start time
  - `getFormattedEndTimeAttribute()` — Format end time
  - `getTimeRangeAttribute()` — Get full time range
- **Custom Methods:**
  - `canAcceptRegistration()` — Check if event is open

#### `Registration` Model
- **File:** `app/Models/Registration.php`
- **Extends:** `Illuminate\Database\Eloquent\Model`
- **Relationships:**
  ```php
  public function event(): BelongsTo
  {
      return $this->belongsTo(Event::class);
  }
  ```
- **Key Accessors:**
  - `getFullNameAttribute()` — Combine first and last name
- **Attribute Casting:**
  - `'present_at' => 'datetime'`

### Eloquent Queries Used Throughout:

**EventController** (`app/Http/Controllers/EventController.php`):
```php
Event::query()
    ->select(['id', 'event_name', 'event_date', ...])
    ->withCount('registrations')
    ->where(...)
    ->orderBy('event_date')
    ->paginate(12)
```

**RegistrationController** (`app/Http/Controllers/RegistrationController.php`):
```php
Registration::query()
    ->with('event')
    ->where('event_id', $selectedEventId)
    ->latest()
    ->paginate(15)
```

**AttendeePortalController** (`app/Http/Controllers/AttendeePortalController.php`):
```php
Registration::query()
    ->where('event_id', $event->id)
    ->where('email', $user->email)
    ->exists()
```

**DashboardController** (`app/Http/Controllers/DashboardController.php`):
```php
Registration::query()
    ->with('event:id,event_name')
    ->latest()
    ->limit(6)
    ->get()
```

---

## 3. BLADE TEMPLATING — Views & Components

**Purpose:** All views use Blade templating engine.

### View Directory Structure:
```
resources/views/
├── layouts/
│   └── app.blade.php (main layout)
├── auth/
│   ├── login.blade.php
├── dashboard/
│   └── index.blade.php
├── events/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── registrations/
│   └── index.blade.php
├── attendance/
│   └── index.blade.php
├── portal/
│   ├── login.blade.php
│   ├── register.blade.php
│   ├── home.blade.php
│   ├── show.blade.php
│   └── registrations.blade.php
```

### Blade Features Used:

#### Layouts and Inheritance:
```blade
@extends('layouts.app')
@section('content')
    ...
@endsection
```

#### Blade Directives:
- `@if`, `@else`, `@elseif`, `@endif` — Conditional rendering
- `@foreach`, `@forelse`, `@endforeach` — Looping
- `@auth`, `@guest` — Authentication checks
- `@csrf` — CSRF token in forms
- `@method()` — HTTP method spoofing
- `{{ }}` — Echo with escaping
- `{!! !!}` — Echo without escaping
- `@json()` — JSON encoding

#### Form Components:
```blade
<form method="POST" action="{{ route('events.store') }}">
    @csrf
    <input type="text" name="event_name" value="{{ old('event_name') }}">
    @error('event_name')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</form>
```

#### Pagination:
```blade
{{ $events->links() }}
{{ $events->appends(request()->query())->links() }}
```

#### Control Structures:
```blade
@forelse($registrations as $reg)
    <tr>
        <td>{{ $reg->full_name }}</td>
    </tr>
@empty
    <tr><td colspan="5">No registrations found</td></tr>
@endforelse
```

---

## 4. FORM VALIDATION — Laravel Validation

**Purpose:** All forms use Laravel's built-in validation.

### Validation Files & Methods:

#### EventController
- **File:** `app/Http/Controllers/EventController.php`
- **Store Method** (Lines ~76-93):
  ```php
  $validated = $request->validate([
      'event_name' => ['required', 'string', 'max:100'],
      'description' => ['nullable', 'string'],
      'event_date' => ['required', 'date', 'after_or_equal:today'],
      'start_time' => ['nullable', 'date_format:H:i'],
      'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
      'venue' => ['required', 'string', 'max:150'],
      'max_slots' => ['required', 'integer', 'min:1'],
  ]);
  Event::create($validated);
  ```

- **Update Method** (Lines ~106-122):
  ```php
  $rules = [
      'event_name' => ['required', 'string', 'max:100'],
      'event_date' => ['required', 'date'],
      'venue' => ['required', 'string', 'max:150'],
      'max_slots' => ['required', 'integer', 'min:' . max(1, $event->registrations_count)],
  ];
  $validated = $request->validate($rules);
  ```

#### AttendeeAuthController
- **File:** `app/Http/Controllers/AttendeeAuthController.php`
- **Login Validation**:
  ```php
  $validated = $request->validate([
      'login' => ['required', 'string', 'max:100'],
      'password' => ['required', 'string', 'min:8'],
  ], [
      'login.required' => 'Please enter your email or username.',
      'password.required' => 'Password is required.',
  ]);
  ```

- **Register Validation** (Comprehensive):
  ```php
  $validated = $request->validate([
      'username' => ['required', 'string', 'min:3', 'max:50', 
                     'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username'],
      'full_name' => ['required', 'string', 'min:2', 'max:100',
                      'regex:/^[a-zA-Z\s]+$/'],
      'email' => ['required', 'email', 'max:100', 'unique:users,email'],
      'contact_number' => ['required', 'string', 'size:11',
                           'regex:/^09[0-9]{9}$/', 'unique:users,contact_number'],
      'password' => ['required', 'string', 'min:8',
                     'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/'],
  ]);
  ```

#### AuthController
- **File:** `app/Http/Controllers/AuthController.php`
- **Admin Login Validation**:
  ```php
  $credentials = $request->validate([
      'login' => ['required', 'string'],
      'password' => ['required', 'string'],
  ]);
  ```

### Validation Rules Used:
- `required` — Field is required
- `string` — Must be string
- `max:X` — Maximum length
- `min:X` — Minimum length
- `size:X` — Exact size
- `regex:/pattern/` — Regex pattern matching
- `unique:table,column` — Unique in database
- `email` — Valid email format
- `date` — Valid date format
- `date_format:format` — Specific date format
- `after:field` — After another field value
- `nullable` — Can be null
- `after_or_equal:today` — Date validation

### Error Handling in Views:
```blade
@if ($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
@endif

@error('field_name')
    <span class="invalid-feedback">{{ $message }}</span>
@enderror
```

---

## 5. MIDDLEWARE — Route Protection

**Purpose:** Protecting authenticated routes from guest access.

### Middleware Files:

#### Location: `app/Http/Middleware/`
- Custom middlewares for role-based access control

### Middleware Usage in Routes:

**File:** `routes/web.php`

#### Authentication Middleware:
```php
Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', EventController::class);
    Route::resource('registrations', RegistrationController::class)->except(['show']);
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
```

#### Attendee Portal Middleware:
```php
Route::middleware(['auth', 'role:attendee'])->group(function (): void {
    Route::post('/events/{event}/join', [AttendeePortalController::class, 'join'])->name('events.join');
    Route::post('/events/{event}/quit', [AttendeePortalController::class, 'quit'])->name('events.quit');
    Route::get('/my-registrations', [AttendeePortalController::class, 'registrations'])->name('registrations');
    Route::post('/logout', [AttendeeAuthController::class, 'logout'])->name('logout');
});
```

#### Guest Routes (No Auth Required):
```php
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::prefix('portal')->name('portal.')->group(function (): void {
    Route::get('/', [AttendeePortalController::class, 'index'])->name('home');
    Route::get('/events/{event}', [AttendeePortalController::class, 'show'])->name('events.show');
    Route::get('/login', [AttendeeAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('/login', [AttendeeAuthController::class, 'login'])->name('portal.login.store');
    Route::get('/register', [AttendeeAuthController::class, 'showRegister'])->name('portal.register');
    Route::post('/register', [AttendeeAuthController::class, 'register'])->name('portal.register.store');
});
```

### Middleware Types Used:
- `auth` — Ensures user is authenticated
- `role:admin` — Ensures user has admin role
- `role:attendee` — Ensures user has attendee role
- `guest` — Implicit (for public routes)

### How Middleware Works:
When a user tries to access a protected route:
1. Laravel checks if `auth` middleware passes (user is logged in)
2. If yes, checks `role:admin` or `role:attendee` middleware
3. If user lacks required role, redirects to unauthorized page
4. If user is not authenticated, redirects to login

---

## 6. HASH FACADE — Password Hashing & Verification

**Purpose:** Secure password hashing and verification.

### Hash Usage:

#### Hashing Passwords in Seeder:
- **File:** `database/seeders/AdminSideSeeder.php`
  ```php
  use Illuminate\Support\Facades\Hash;
  
  DB::table('users')->updateOrInsert(
      ['email' => 'admin@evelink.local'],
      [
          'username' => 'admin',
          'password' => Hash::make('password123'),
          ...
      ]
  );
  ```

#### Hashing in User Factory:
- **File:** `database/factories/UserFactory.php`
  ```php
  use Illuminate\Support\Facades\Hash;
  
  public function definition(): array
  {
      return [
          ...
          'password' => static::$password ??= Hash::make('password'),
          ...
      ];
  }
  ```

#### Eloquent Model Attribute Casting:
- **File:** `app/Models/User.php`
  ```php
  protected function casts(): array
  {
      return [
          'password' => 'hashed',  // Auto-hashes on assignment
      ];
  }
  ```

#### Password Verification During Login:
- **File:** `app/Http/Controllers/AuthController.php`
  ```php
  if (! Auth::attempt([$field => $credentials['login'], 
                       'password' => $credentials['password'], 
                       'role' => 'admin'], true)) {
      throw ValidationException::withMessages([
          'login' => 'Invalid credentials. Please check your username/email and password.',
      ]);
  }
  ```
  - `Auth::attempt()` automatically hashes the provided password and compares it with the stored hash

- **File:** `app/Http/Controllers/AttendeeAuthController.php`
  ```php
  if (! Auth::attempt([$field => $validated['login'], 
                       'password' => $validated['password'], 
                       'role' => 'attendee'], true)) {
      throw ValidationException::withMessages([
          'login' => 'Invalid credentials. Please check your username/email and password.',
      ]);
  }
  ```

### Hash Security Features:
- Uses Laravel's default hashing algorithm (bcrypt or Argon2)
- Automatically adds salt to prevent rainbow table attacks
- One-way hashing (irreversible)
- Passwords never stored in plain text

---

## 7. SESSION & AUTH — Managing Login State

**Purpose:** Managing user login state and session management.

### Authentication Configuration:

#### Config File: `config/auth.php`
- Defines guards (web, api)
- Defines providers (users model)
- Password reset configuration

#### User Model Authentication:
- **File:** `app/Models/User.php`
  ```php
  class User extends Authenticatable  // Extends Authenticatable
  {
      use HasFactory, Notifiable;
      ...
  }
  ```

### Session Management:

#### Logging In (Creating Sessions):
- **File:** `app/Http/Controllers/AuthController.php` (Admin)
  ```php
  if (Auth::attempt([$field => $credentials['login'], 
                     'password' => $credentials['password'], 
                     'role' => 'admin'], true)) {  // true = remember me
      $request->session()->regenerate();
      return redirect()->intended(route('dashboard'));
  }
  ```

- **File:** `app/Http/Controllers/AttendeeAuthController.php` (Attendee)
  ```php
  if (Auth::attempt([$field => $validated['login'], 
                     'password' => $validated['password'], 
                     'role' => 'attendee'], true)) {
      $request->session()->regenerate();
      return redirect()->intended(route('portal.home'));
  }
  ```

#### Logging Out (Destroying Sessions):
- **File:** `app/Http/Controllers/AuthController.php`
  ```php
  public function logout(Request $request): RedirectResponse
  {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect()->route('login');
  }
  ```

- **File:** `app/Http/Controllers/AttendeeAuthController.php`
  ```php
  public function logout(Request $request): RedirectResponse
  {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect()->route('portal.login');
  }
  ```

### Session Features Used:

#### Checking Authentication:
```php
if (Auth::check()) {
    // User is logged in
}

if (Auth::guest()) {
    // User is not logged in
}
```

#### Getting Current User:
```php
$user = Auth::user();           // Get current user
$id = Auth::id();               // Get current user ID
$role = Auth::user()?->role;    // Get user's role
```

#### Session Methods:
- `$request->session()->put('key', 'value')` — Store in session
- `$request->session()->get('key')` — Retrieve from session
- `$request->session()->flush()` — Clear all session data
- `$request->session()->regenerate()` — Regenerate session ID (security)
- `$request->session()->regenerateToken()` — Regenerate CSRF token (security)
- `$request->session()->invalidate()` — Invalidate current session

### CSRF Protection (Session Related):

#### In Blade Templates:
```blade
<form method="POST" action="{{ route('events.store') }}">
    @csrf
    ...
</form>
```

#### Configuration: `config/session.php`
- Session driver: `cookie` or `file`
- Session timeout: `2880` minutes (48 hours default)
- Secure cookies for HTTPS
- HTTP-only cookies to prevent XSS access

#### Session Flash Data:
```php
return redirect()->route('events.index')->with('success', 'Event created successfully.');
```

In Blade:
```blade
@if (session()->has('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
```

### Login State in Views:

#### Guest Users:
```blade
@guest
    <a href="{{ route('portal.login') }}">Login</a>
@endguest
```

#### Authenticated Users:
```blade
@auth
    <span>Welcome, {{ Auth::user()->full_name }}</span>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button>Logout</button>
    </form>
@endauth
```

#### Role-Based Conditional Display:
```blade
@if (Auth::user()?->isAdmin())
    <!-- Admin Dashboard -->
@elseif (Auth::user()?->isAttendee())
    <!-- Attendee Portal -->
@endif
```

---

## Summary Table

| Feature | Files | Status |
|---------|-------|--------|
| **Migrations** | `database/migrations/` | ✅ Implemented |
| **Eloquent ORM** | `app/Models/` | ✅ Implemented |
| **Blade Templating** | `resources/views/` | ✅ Implemented |
| **Form Validation** | Controllers (all) | ✅ Implemented |
| **Middleware** | `routes/web.php` | ✅ Implemented |
| **Hash Facade** | Seeders, Factories, Auth Controllers | ✅ Implemented |
| **Session & Auth** | Auth Controllers, Blade views | ✅ Implemented |

---

## Commands for Demonstration

### Database Operations:
```bash
# Run all migrations
php artisan migrate

# Rollback all migrations
php artisan migrate:rollback

# Refresh database (rollback + migrate + seed)
php artisan migrate:refresh --seed

# Create new migration
php artisan make:migration create_table_name
```

### Artisan Commands:
```bash
# Run seeders
php artisan db:seed

# Specific seeder
php artisan db:seed --class=AdminSideSeeder

# Start development server
php artisan serve
```

### Testing Authentication:
- Admin: `admin` / `password123`
- Attendee: Create account via portal registration

---

## Live Demonstration Checklist

- [ ] Show Migration files explaining schema
- [ ] Demonstrate Eloquent relationships (Event -> Registrations)
- [ ] Show Blade templates with directives and components
- [ ] Show Form validation rules in controllers
- [ ] Show Middleware protecting admin/attendee routes
- [ ] Show Hash::make() and Auth::attempt() usage
- [ ] Show Session management (regenerate, logout)
- [ ] Show auth() helper and @auth directive in views
- [ ] Test login flow with session creation
- [ ] Test logout with session destruction
