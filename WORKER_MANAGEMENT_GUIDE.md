# Worker Management System - Implementation Guide

## Overview
This guide explains the transformation of the `users.php` into a comprehensive `worker_list.php` with activity tracking, worker approval workflow, and firing capabilities.

## Changes Made

### 1. **New File Created: `worker_list.php`** 📁
- **Location:** `/admin/worker_list.php`
- **Purpose:** Central hub for managing all approved workers
- **Features:**
  - Display all active workers with their details
  - Show last login/logout times
  - Fire workers with confirmation dialog
  - Real-time activity tracking
  - Responsive card-based layout

### 2. **Database Updates Required** 🗄️
Execute the SQL migration file to add necessary columns and tables:

**Location:** `/database-sql/worker_management_migration.sql`

**Required Changes:**
```sql
-- Add status column to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT NULL;

-- Add user_id column to worker_applications
ALTER TABLE worker_applications 
ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL;

-- Create worker_activity table
CREATE TABLE IF NOT EXISTS worker_activity (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  activity_type ENUM('login', 'logout') NOT NULL,
  activity_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (activity_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3. **Updated Files**

#### A. **`admin/pending_workers.php`** 📝
**Changes:**
- When admin approves a worker:
  - Creates a new user account automatically
  - Generates unique username based on worker name
  - Sets temporary password
  - Links worker application to user account
  - Redirects to `worker_list.php?approved=1`
- Added success/error message display
- Updated navigation to include Worker List

#### B. **Login & Logout Tracking** 🔐

**File: `processor/login_process.php`**
- Logs worker login activity to `worker_activity` table
- Records login time and IP address
- Automatically triggered on successful worker login

**File: `p_login/logout.php`**
- Logs worker logout activity to `worker_activity` table
- Records logout time
- Works for all user types (worker, admin)

#### C. **Navigation Updates** 🧭
**Updated Files:**
- `/admin/dashboard.php`
- `/admin/manage_car.php`
- `/admin/products.php`
- `/admin/rentals.php`
- `/admin/sales.php`
- `/admin/admin.php`
- `/admin/pending_workers.php`

**Change:** Replaced `users.php` link with `worker_list.php`
- Old: `<a href="users.php">👥 Users</a>`
- New: `<a href="worker_list.php">👷 Worker List</a>`

### 4. **Workflow: Worker Registration to Active List**

```
1. Worker Registration
   └─ /p_login/register_worker.php
      └─ /processor/register_worker_process.php
         └─ Creates entry in worker_applications (status: pending)

2. Admin Approval
   └─ /admin/pending_workers.php
      └─ Admin clicks "Approve"
         └─ System creates user account
         └─ Links user_id to worker_applications
         └─ Updates status to 'approved'
         └─ Redirects to worker_list.php?approved=1

3. Worker Active
   └─ /admin/worker_list.php
      └─ Shows all approved workers
      └─ Displays login/logout activity
      └─ Admin can fire workers
```

## Key Features

### 👷 Worker List Display
- Shows worker name, username, email, phone
- Joined date
- Last login/logout times with full timestamps
- Address from application
- Action buttons (Fire Worker)

### 🔐 Activity Tracking
- **Login:** Recorded when worker logs in with IP address
- **Logout:** Recorded when worker logs out
- **Timestamps:** Full date and time for each activity
- **History:** All activities stored in `worker_activity` table

### 🚫 Fire Worker Feature
- Admin can fire any worker from the list
- Confirmation dialog: "⚠️ WARNING! Are you sure you want to fire {name}?"
- Worker marked with `status = 'fired'` in users table
- Fired workers hidden from active worker list
- Action logged in system

## Implementation Steps

### Step 1: Execute Database Migration
1. Open phpMyAdmin or your database management tool
2. Run the SQL queries from `worker_management_migration.sql`
3. Verify new columns and table are created

### Step 2: Verify Files
- ✅ `/admin/worker_list.php` - Created
- ✅ `/processor/login_process.php` - Updated
- ✅ `/p_login/logout.php` - Updated
- ✅ `/admin/pending_workers.php` - Updated
- ✅ All admin navigation files - Updated
- ✅ `/database-sql/worker_management_migration.sql` - Created

### Step 3: Test the Workflow
1. **Test Worker Registration:**
   - Register a new worker via `/p_login/register_worker.php`
   - Verify entry appears in Pending Workers

2. **Test Approval:**
   - Go to `/admin/pending_workers.php`
   - Click "Approve" on a pending worker
   - Verify redirect to worker_list.php with success message
   - Verify worker now appears in active list

3. **Test Login Tracking:**
   - Login as an approved worker
   - Check database: `worker_activity` table should have login entry
   - Logout and verify logout entry is recorded

4. **Test Worker Firing:**
   - In `/admin/worker_list.php`, click "Fire Worker"
   - Confirm the warning dialog
   - Verify worker is marked as fired and removed from list

## Database Queries for Verification

```sql
-- Check active workers
SELECT u.id, u.full_name, u.username, u.status, u.created_at
FROM users u
WHERE u.role = 'worker' AND (u.status IS NULL OR u.status != 'fired');

-- Check worker activity
SELECT wa.*, u.full_name, u.username
FROM worker_activity wa
JOIN users u ON wa.user_id = u.id
ORDER BY wa.activity_time DESC
LIMIT 20;

-- Check fired workers
SELECT u.id, u.full_name, u.status
FROM users u
WHERE u.role = 'worker' AND u.status = 'fired';
```

## Security Features Implemented

1. **CSRF Protection:** Forms include CSRF token validation
2. **SQL Injection Prevention:** Prepared statements used for all queries
3. **Password Security:** Worker passwords hashed using PASSWORD_DEFAULT
4. **Session Management:** Role-based access control (requireAdmin())
5. **XSS Prevention:** htmlspecialchars() used for all user output
6. **Confirmation Dialogs:** JavaScript confirms before firing workers

## Notes

- **Old `users.php`:** Can be kept for reference or deleted if not needed
- **Default Worker Password:** Generated as temporary - workers should change on first login
- **Auto-generated Usernames:** Format is `firstname_lastname_uniqueid` to ensure uniqueness
- **Activity Tracking:** Only applies to worker logins/logouts, not admin activity
- **Status Field:** Can be extended for other statuses (suspended, on_leave, etc.)

## Troubleshooting

### Worker not appearing in list after approval
- Check if `worker_activity` table exists
- Verify `user_id` is properly linked in `worker_applications`
- Check user's role is set to 'worker'

### Activity times not showing
- Ensure `worker_activity` table was created
- Check if worker has logged in since migration
- Verify database timezone is correct

### Fire button not working
- Check CSRF token is present in form
- Verify admin is authenticated
- Check database `status` column exists in users table

---

**Last Updated:** April 22, 2026
**Version:** 1.0
