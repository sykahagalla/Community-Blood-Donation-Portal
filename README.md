# Community Blood Donation Portal

A comprehensive web-based platform connecting blood donors, recipients, and hospitals to streamline the blood donation process and save lives.

## 🩸 Features

### For Donors
- Register and create donor profiles with blood type and location
- View active blood requests matching their blood type
- Track donation history and eligibility status
- Receive real-time notifications for urgent blood needs
- View upcoming blood donation drives

### For Hospitals
- Post urgent blood requests with priority levels
- Search for available donors by blood type and location
- Manage blood request status (active/fulfilled/cancelled)
- Track donation history and statistics
- Access real-time donor availability dashboard

### For Administrators
- Approve/reject user registrations
- Comprehensive analytics and reporting
- Monitor blood requests and donations
- View system-wide statistics and trends
- Manage blood donation drives

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server (XAMPP/WAMP recommended)
- Modern web browser

### Setup Instructions

1. **Extract Files**
   - Extract the ZIP file to your XAMPP `htdocs` directory
   - Path should be: `C:/xampp/htdocs/Community Blood Donation Portal/`

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `blood_donation_portal`
   - Import the SQL schema file:
     - Click on the database
     - Go to "Import" tab
     - Choose file: `database/schema.sql`
     - Click "Go"

3. **Configure Database Connection**
   - Open `config/database.php`
   - Update credentials if needed (default works with XAMPP):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'blood_donation_portal');
     ```

4. **Access the Application**
   - Open your web browser
   - Navigate to: `http://localhost/Community%20Blood%20Donation%20Portal/`

## 🔐 Default Admin Credentials

```
Email: admin@bloodportal.com
Password: admin123
```

**Important:** Change the admin password immediately after first login!

## 📖 User Guide

### Getting Started as a Donor

1. Click "Become a Donor" on the homepage
2. Fill in your personal information including:
   - Full name and contact details
   - Blood type
   - Address and city
3. Wait for admin approval (usually 24-48 hours)
4. Once approved, log in to view blood requests

### Getting Started as a Hospital

1. Click "Register Hospital" on the homepage
2. Provide hospital details:
   - Hospital name and registration number
   - Contact information
   - Address
3. Wait for admin verification
4. After approval, create blood requests and manage donations

### Creating a Blood Request (Hospital)

1. Log in to your hospital account
2. Click "Create Request" in the sidebar
3. Fill in the required information:
   - Blood type needed
   - Number of units
   - Urgency level
   - Required date
4. Submit the request
5. Eligible donors will be notified automatically

## 🛠️ Technical Stack

- **Frontend:** HTML5, CSS3, JavaScript, TailwindCSS
- **Backend:** PHP 7.4+
- **Database:** MySQL with PDO
- **Icons:** Font Awesome 6
- **Charts:** Chart.js

## 📁 Project Structure

```
Community Blood Donation Portal/
├── admin/              # Admin dashboard and management
│   ├── actions/        # Backend actions
│   ├── includes/       # Reusable components
│   └── dashboard.php
├── auth/               # Authentication system
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/             # Configuration files
│   ├── config.php
│   └── database.php
├── database/           # Database schema
│   └── schema.sql
├── donor/              # Donor portal
│   ├── includes/
│   └── dashboard.php
├── hospital/           # Hospital portal
│   ├── actions/
│   ├── includes/
│   ├── create_request.php
│   └── dashboard.php
├── uploads/            # File uploads directory
├── index.php           # Landing page
└── README.md
```

## 🔒 Security Features

- Password hashing using bcrypt
- PDO prepared statements (SQL injection prevention)
- CSRF token protection
- Session security
- Input sanitization
- XSS protection

## 📊 Database Schema

The system uses 9 main tables:
- **users** - Authentication and user accounts
- **donors** - Donor profiles and information
- **hospitals** - Hospital/blood bank details
- **blood_requests** - Blood donation requests
- **donations** - Donation records
- **notifications** - User notifications
- **blood_drives** - Community blood drive events
- **admin_logs** - System audit logs

## 🌟 Key Functionalities

### Real-time Matching
- Automatically matches blood requests with eligible donors
- Location-based donor search
- Blood type compatibility checking

### Eligibility Tracking
- Tracks last donation date
- Calculates next eligible donation date (90-day interval)
- Prevents donations before eligibility

### Notification System
- Email/SMS notifications for urgent requests
- Status update notifications
- Approval/rejection notifications

### Analytics Dashboard
- Blood type distribution charts
- Donation trend analysis
- Request fulfillment statistics
- Geographic distribution maps

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL service is running
- Check database credentials in `config/database.php`
- Ensure database exists and schema is imported

### Page Not Found (404)
- Check the URL encoding (spaces should be %20)
- Verify files are in correct directory
- Check Apache configuration

### Pending Approval Screen
- Contact admin to approve your account
- Or use admin panel to approve manually

## 📝 License

This project is developed for educational and community service purposes.

## 👥 Support

For support and queries:
- Email: info@bloodlife.lk
- Phone: +94 11 234 5678

## 🎯 Future Enhancements

- Mobile app integration
- SMS/Email notification system
- Blood bank inventory management
- Appointment scheduling system
- Donor rewards and badges
- Blood donation certificate generation
- Multi-language support
- Advanced geolocation with maps

## 🙏 Acknowledgments

Developed as part of the Community Blood Donation initiative to save lives and improve healthcare accessibility in Sri Lanka.

---

**Version:** 1.0.0  
**Last Updated:** March 2026  
**Developed by:** Community Blood Donation Team
