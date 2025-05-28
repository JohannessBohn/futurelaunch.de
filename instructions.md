# FutureLaunch Newsletter System Instructions

## Overview
FutureLaunch is a simple, file-based newsletter management system that doesn't require a database connection. It includes:

- A clean, responsive homepage with newsletter subscription form
- An admin dashboard to manage subscribers
- Easy setup process

## Getting Started

1. **Run the Setup Script**
   - Navigate to: http://localhost/LeqendAIO/futurelaunch.de/setup.php
   - Click "Set Up Now" to create all necessary files and folders

2. **Admin Dashboard**
   - After setup, access the dashboard: http://localhost/LeqendAIO/futurelaunch.de/dashboard.php
   - Login with:
     - Username: `admin`
     - Password: `admin123`

3. **Homepage**
   - View your newsletter subscription page: http://localhost/LeqendAIO/futurelaunch.de/index.html

## System Structure

- `dashboard.php` - Admin interface for managing subscribers
- `setup.php` - Installation script
- `subscribe.php` - Handles newsletter signups
- `data/` - Stores all subscriber information in JSON format
- `logs/` - Contains system and newsletter logs

## Security Notes

- **Important:** Change the default admin password in `dashboard.php`
- For production use, implement proper password hashing
- Consider adding additional security measures like rate limiting

## Need Help?

If you encounter any issues, check:
1. XAMPP is running (Apache service)
2. Folder permissions (data directory should be writable)
3. PHP version (7.4+ recommended)

## Customization

You can customize the appearance by editing:
- `index.html` - Homepage design
- `dashboard.php` - Admin dashboard layout
- CSS within these files

Enjoy your new newsletter system!
