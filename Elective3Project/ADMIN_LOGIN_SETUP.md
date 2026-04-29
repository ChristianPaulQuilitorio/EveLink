# Admin Portal Login - Quick Reference

## Admin Credentials

**Login URL**: `http://localhost:8000/login` (for local) or your domain's `/login`

### Admin Account
```
Username: admin
OR
Email: admin@evelink.local

Password: password123
```

## What Was Fixed

✅ **Database Migration Applied**
- Added `contact_number` column (VARCHAR 11, nullable)
- Added `role` column (VARCHAR 50, default 'attendee')
- Admin user now has `role = 'admin'` assigned

✅ **Admin User Seeded**
- Created admin account with proper role
- Ready to access admin dashboard at `/dashboard`

## Admin Portal Features

Once logged in, you can:
- View all events on the dashboard
- Create new events
- Manage event registrations
- Track attendance (mark Present/Absent)
- Export registration data to XLSX format
- Export attendance data to XLSX format
- Access the attendee portal link from the login page

## Supabase Setup

If you're using Supabase:

1. Go to your Supabase project's SQL Editor
2. Copy the entire content from: `database/supabase/supabase_complete_schema.sql`
3. Paste it into the Supabase SQL Editor
4. Click "Run"

This will create:
- ✅ Users table with contact_number and role columns
- ✅ Events table
- ✅ Registrations table
- ✅ Dashboard statistics view
- ✅ All necessary indexes
- ✅ Admin user seed data (optional)

## If Login Still Doesn't Work

1. **Clear cache and sessions**:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan config:clear
   ```

2. **Verify database connection** in `.env`:
   ```
   DB_CONNECTION=pgsql (or mysql/sqlite)
   DB_HOST=your_host
   DB_PORT=5432 (or 3306)
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

3. **Check AuthController logs**:
   - Look for errors in `storage/logs/laravel.log`

4. **Verify the admin user exists**:
   ```bash
   php artisan tinker
   > User::where('email', 'admin@evelink.local')->first()
   ```
   Should show: `role = 'admin'`

## Login Flow

1. Visit `/login`
2. Enter username or email
3. Enter password
4. If successful → redirected to `/dashboard`
5. If unsuccessful → error message shown

## Attendee Portal Access

- **Attendee URL**: `/portal`
- **Attendee Login**: `/portal/login`
- **Attendee Register**: `/portal/register`
- **Link from Admin**: Admin login page has "Attendee Portal" link

---

**Last Updated**: April 29, 2026  
**Status**: ✅ Ready to Use
