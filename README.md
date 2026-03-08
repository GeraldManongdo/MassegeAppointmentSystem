# Appointment Booking System

A comprehensive web-based appointment booking system built with PHP, MySQL, and Bootstrap. This system allows customers to book services online while enabling administrators to manage schedules, services, staff, and bookings efficiently.

## 📋 Features

### User Features

- **User Registration & Login** - Secure authentication system
- **Browse Services** - View available services with pricing and duration
- **Book Appointments** - Step-by-step booking process with real-time availability
- **Time Slot Selection** - Dynamic time slot generation based on business hours
- **Appointment Management** - View, cancel, and track appointments
- **Profile Management** - Update personal information and change password
- **Booking History** - Track all past and upcoming appointments

### Admin Features

- **Dashboard Overview** - Statistics and insights at a glance
- **Booking Management** - View, edit, cancel, and manage all bookings
- **Service Management** - Create, update, and delete services (CRUD)
- **User Management** - Manage registered users and their permissions
- **Status Control** - Update appointment and user statuses
- **Reports & Analytics** - View booking statistics and trends

### System Features

- **Prevent Double Booking** - Automatic conflict detection
- **Time Slot Locking** - Temporary reservation during checkout (5 minutes)
- **Cancellation Rules** - 24-hour advance cancellation policy
- **Business Hours Management** - Configurable working hours
- **Responsive Design** - Mobile-friendly Bootstrap interface
- **Modular Architecture** - Clean, maintainable code structure

## 🗂️ Folder Structure

```
MassegeAppointmentSystem/
├── admin/                      # Admin panel pages
│   ├── dashboard.php          # Admin dashboard
│   ├── bookings.php           # Manage bookings
│   ├── services.php           # Manage services
│   └── users.php              # Manage users
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css         # Custom styles
│   ├── js/
│   │   └── main.js           # Custom JavaScript
│   └── images/               # Image files
├── config/                    # Configuration files
│   ├── config.php            # General configuration
│   └── database.php          # Database connection
├── controllers/              # Business logic (future expansion)
├── database/                 # Database files
│   └── appointment_system.sql # Database schema and sample data
├── includes/                 # Common includes
│   ├── header.php           # HTML header
│   ├── footer.php           # HTML footer
│   └── navbar.php           # Navigation bar
├── models/                   # Data models
│   ├── User.php             # User model
│   ├── Service.php          # Service model
│   └── Appointment.php      # Appointment model
├── user/                     # User-facing pages
│   ├── dashboard.php        # User dashboard
│   ├── services.php         # Browse services
│   ├── book.php             # Book appointments
│   ├── appointments.php     # View appointments
│   └── profile.php          # User profile
├── index.php                # Landing page
├── login.php                # Login page
├── register.php             # Registration page
└── logout.php               # Logout handler
```

## ⚙️ Installation

### Prerequisites

- XAMPP (Apache, MySQL, PHP 7.4+)
- Web browser
- Text editor (optional, for customization)

### Step-by-Step Installation

1. **Install XAMPP**
   - Download and install XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
   - Start Apache and MySQL from XAMPP Control Panel

2. **Clone/Copy Project**

   ```bash
   # The project is already in: c:\xampp\htdocs\MassegeAppointmentSystem
   ```

3. **Create Database**
   - Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Click "Import" tab
   - Choose file: `database/appointment_system.sql`
   - Click "Go" to import

4. **Configure Database Connection** (if needed)
   - Edit `config/database.php`
   - Update credentials if different from defaults:
     ```php
     private $host = "localhost";
     private $db_name = "appointment_system";
     private $username = "root";
     private $password = "";
     ```

5. **Access the Application**
   - Open browser and navigate to: [http://localhost/MassegeAppointmentSystem](http://localhost/MassegeAppointmentSystem)

## 🔐 Default Login Credentials

### Admin Account

- **Email:** admin@appointmentsystem.com
- **Password:** admin123

### User Account

- Create a new account via the registration page

## 📖 User Guide

### For Customers

1. **Registration**
   - Click "Register" from the home page
   - Fill in your details (name, email, phone, password)
   - Accept terms and conditions
   - Click "Create Account"

2. **Booking an Appointment**
   - Login to your account
   - Navigate to "Services"
   - Select a service you want to book
   - Choose a date (must be future date)
   - Select an available time slot
   - Add any notes (optional)
   - Confirm your booking

3. **Managing Appointments**
   - View all appointments from "My Appointments"
   - Filter by status (Upcoming, Completed, Cancelled)
   - Cancel appointments (24 hours notice required)

4. **Profile Management**
   - Update personal information
   - Change password
   - View account statistics

### For Administrators

1. **Dashboard**
   - View key statistics (total bookings, today's appointments, etc.)
   - Quick access to main features
   - Recent appointments overview

2. **Managing Bookings**
   - View all appointments with filters (date, status, service)
   - Search by customer name or email
   - Update appointment status
   - View customer details
   - Cancel or delete appointments

3. **Managing Services**
   - Add new services with name, description, duration, and price
   - Edit existing services
   - Activate/deactivate services
   - Delete services (warning: affects existing appointments)

4. **Managing Users**
   - View all registered users
   - View user details and booking history
   - Suspend or reactivate user accounts
   - Reset user passwords

## 🛠️ Technical Details

### Database Schema

**Main Tables:**

- `users` - User accounts (customers and admins)
- `services` - Available services
- `appointments` - Booking records
- `business_hours` - Operating hours configuration
- `time_slot_locks` - Temporary slot reservations
- `system_settings` - System configuration
- `notifications` - User notifications

### Key Features Implementation

**Time Slot Generation:**

- Automatically generates available slots based on business hours
- Considers service duration
- Checks for existing bookings to prevent conflicts
- 30-minute intervals by default

**Booking Rules:**

- Service must be active
- Date must be in the future
- Time must be within business hours
- No conflicting appointments
- User must be logged in

**Cancellation Policy:**

- Users can cancel up to 24 hours before appointment
- Late cancellations are marked separately
- Cancellation reason can be provided

## 🎨 Customization

### Changing Business Hours

Edit the `business_hours` table in the database or create an admin interface for it.

### Modifying Time Slot Intervals

In `models/Appointment.php`, locate the `getAvailableTimeSlots()` method:

```php
// Change this line (currently 30 minutes):
$current_time += 1800; // 1800 seconds = 30 minutes
```

### Styling

Edit `assets/css/style.css` to customize colors, fonts, and layout.

### Adding New Features

- Controllers can be added to the `controllers/` folder
- Additional models in `models/` folder
- Follow the existing MVC pattern

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session management for authentication
- Input validation and sanitization
- Role-based access control (User/Admin)
- XSS protection with `htmlspecialchars()`

## 📱 Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (responsive design)

## 🐛 Troubleshooting

### Database Connection Error

- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database name matches the imported database

### Page Not Found

- Check that the project is in `c:\xampp\htdocs\MassegeAppointmentSystem`
- Ensure Apache is running in XAMPP
- Clear browser cache

### Login Issues

- Use default admin credentials
- Check if user account is active (not suspended)
- Verify email and password are correct

### Time Slots Not Showing

- Check business hours are configured correctly
- Ensure the selected date is not on a closed day
- Verify service has active status

## 📝 Future Enhancements

Potential features for future versions:

- Email notifications for appointments
- SMS reminders
- Payment integration
- Multiple staff members management
- Calendar view for admin
- Export reports to PDF/Excel
- Customer reviews and ratings
- Online payment processing
- Multi-language support

## 📄 License

This project is created for educational and commercial purposes. Feel free to modify and use as needed.

## 👨‍💻 Developer Notes

### Code Standards

- PHP 7.4+ compatibility
- PDO for database operations
- Bootstrap 5 for UI
- jQuery for enhanced interactions
- Modular, reusable code structure

### Database Backup

Regularly backup your database:

```bash
# From phpMyAdmin, use Export feature
# Or use mysqldump command
```

## 📞 Support

For issues or questions:

- Check the troubleshooting section
- Review the code comments
- Consult PHP/MySQL documentation

---

**Version:** 1.0.0  
**Last Updated:** March 3, 2026  
**Built with:** PHP, MySQL, Bootstrap 5, jQuery
