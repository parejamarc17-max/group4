# Car Rental System - Booking Fix Guide

## Quick Start to Fix Booking Issues

### Step 1: Initialize Database
1. Open your browser and go to: `http://localhost/car_rental_system/setup.php`
2. Click the **"Click here to set up database automatically"** button (if no tables exist)
3. Wait for the setup to complete - you should see a list of created tables
4. The page will show sample login credentials

### Step 2: Test the Booking System
1. Log in with test credentials:
   - **Username:** `customer1`
   - **Password:** `admin123`
   
2. Go to the Cars page (http://localhost/car_rental_system/car.php)
3. Click "Rent Now" on any car
4. Fill in the booking form:
   - Your Name (e.g., "John Doe")
   - Email (e.g., "john@example.com")
   - Phone (e.g., "1234567890" or "+1-234-567-8900")
   - Pickup Date
   - Return Date
5. Click "Confirm Booking"

### Step 3: Verify Booking
1. You should see a "Booking successful!" message
2. Log in as admin (username: `admin`, password: `admin123`)
3. Go to Dashboard → Rentals (`http://localhost/car_rental_system/admin/rentals.php`)
4. You should see your booking in the rental list

---

## Troubleshooting

### Problem: Booking still shows "Booking failed"

**Step 1: Check the error message**
- The error message should now show the actual database error
- Example: "Booking failed: Table 'car_rental_db.rentals' doesn't exist"

**Step 2: Run database setup again**
- Go to http://localhost/car_rental_system/setup.php
- Click "Re-run Database Setup" button

**Step 3: Check database status**
- Visit http://localhost/car_rental_system/setup.php
- Look for "Rentals in database" count
- Should show 0 or greater (depending on previous bookings)

### Problem: "Pickup date cannot be in the past"
- Make sure you select today or a future date for pickup
- The system doesn't allow bookings for past dates

### Problem: "Invalid phone number"
- Phone number must be 10-15 characters
- Allowed formats: "1234567890", "+1-234-567-8900", "(123) 456-7890"
- Must contain only digits, spaces, hyphens, and parentheses

### Problem: Car not showing as "rented" after booking
- Check the admin cars list (Dashboard → Manage Cars)
- The car status should change to "rented" after successful booking
- If not: Database update might be failing separately from the insert

---

## Test Scripts

If you need more detailed debugging:

1. **Database Verification:**
   - Visit `http://localhost/car_rental_system/debug_booking.php`
   - Shows table structures and sample data

2. **Booking Insert Tests:**
   - Visit `http://localhost/car_rental_system/processor/customer_booking.php`
   - Tests different INSERT variations to find which one works with your database

---

## Default Login Credentials

After database setup, use these to test:

**Admin Account:**
- Username: `admin`
- Password: `admin123`
- Access: Dashboard, Manage Cars, View Rentals

**Customer Account:**
- Username: `customer1`
- Password: `admin123`
- Access: Browse cars, make bookings

**Worker Account:**
- Username: `worker1`
- Password: `admin123`
- Access: Worker dashboard (if implemented)

---

## Database Connection Settings

If booking still fails, verify your database credentials in `config/database.php`:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost' for XAMPP
define('DB_NAME', 'car_rental_db');  // Database name
define('DB_USER', 'root');           // Usually 'root' for XAMPP
define('DB_PASS', '');               // Usually empty for XAMPP
```

---

## What Changed

The booking system has been updated with:

1. **Better Error Messages** - Now shows the actual database error instead of generic "Booking failed"
2. **Flexible Database Inserts** - Tries multiple column combinations to work with different database setups
3. **Database Schema Validation** - Includes SQL file with proper table structure
4. **Easy Setup Tool** - One-click database initialization

---

## Still Having Issues?

1. Check the error message displayed - it should now be more specific
2. Run the setup.php page and look at the "Rentals Table Structure" section
3. Make sure the rentals table is created correctly
4. Verify database connection in config/database.php
5. Check if PHP error logs have additional information (seen system-wide)

The system should now work! Bookings should appear in the admin rentals page immediately after submission.
