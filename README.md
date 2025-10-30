# Car Rental System

A comprehensive full-stack car rental web application built with PHP, MySQL, JavaScript, HTML, CSS, and Bootstrap. This system provides a complete solution for managing car rentals with user authentication, admin dashboard, booking management, and responsive design.

## Features

### 🚗 **Core Features**
- **User Authentication**: Login/Register system with role-based access control
- **Car Management**: Add, edit, delete, and manage car inventory
- **Booking System**: Complete booking workflow with admin approval
- **Dashboard**: Separate dashboards for users and administrators
- **Responsive Design**: Mobile-first design with Bootstrap 5
- **Theme Toggle**: Dark/Light mode switching
- **Search & Filter**: Advanced car filtering and search functionality

### 👤 **User Features**
- User registration and login
- Browse available cars with detailed information
- Book cars with date selection and location
- View booking history and status
- User profile management
- Password change functionality

### 🔧 **Admin Features**
- Admin dashboard with statistics
- Car management (CRUD operations)
- Booking approval/rejection system
- User management
- Revenue tracking and reports
- Admin activity logging

### 🎨 **UI/UX Features**
- Modern, clean interface
- Smooth animations and transitions
- Touch-friendly design
- Form validation
- Flash messages for user feedback
- Image galleries for cars
- Responsive grid layouts

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3.2
- **Icons**: Bootstrap Icons
- **Server**: Apache (XAMPP recommended)

## Installation & Setup

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Clone/Download Project
```bash
# Clone the repository or download the ZIP file
# Extract to your XAMPP htdocs folder
# Example: C:\xampp\htdocs\Car-rental-php
```

### Step 2: Database Setup
1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Import the database schema:
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go" to import

### Step 3: Configuration
1. Update database configuration in `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'car_rental_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Step 4: File Permissions
Ensure the uploads directory is writable:
```bash
# Create uploads directory if it doesn't exist
mkdir public/assets/uploads/cars
# Set proper permissions (Linux/Mac)
chmod 755 public/assets/uploads/cars
```

### Step 5: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/Car-rental-php/public/`
3. The application should load successfully

## Default Login Credentials

### Admin Account
- **URL**: `http://localhost/Car-rental-php/public/admin.php`
- **Email**: admin@carrental.com
- **Password**: password
- **Role**: Administrator

### User Account
- **URL**: `http://localhost/Car-rental-php/public/auth/login.php`
- **Email**: user@example.com
- **Password**: password123
- **Role**: Regular User

### Quick Access Links
- **Home Page**: `http://localhost/Car-rental-php/public/index.php`
- **User Login**: `http://localhost/Car-rental-php/public/auth/login.php`
- **Admin Login**: `http://localhost/Car-rental-php/public/admin.php`

## Project Structure

```
Car-rental-php/
├── config/
│   ├── database.php          # Database configuration
│   └── auth.php             # Authentication helpers
├── database/
│   └── schema.sql           # Database schema
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── styles.css   # Main styles
│   │   │   ├── theme.css    # Theme styles
│   │   │   ├── cars.css     # Car-specific styles
│   │   │   └── auth.css     # Authentication styles
│   │   ├── js/
│   │   │   ├── main.js      # Main JavaScript
│   │   │   └── theme-toggle.js # Theme switching
│   │   └── uploads/         # File uploads
│   ├── auth/
│   │   ├── login.php        # Login page
│   │   ├── register.php     # Registration page
│   │   └── logout.php       # Logout handler
│   ├── admin/
│   │   ├── dashboard.php    # Admin dashboard
│   │   ├── manage-cars.php  # Car management
│   │   └── add-car.php      # Add new car
│   ├── views/
│   │   ├── admin/           # Admin views
│   │   └── user/            # User views
│   ├── index.php            # Homepage
│   ├── cars.php             # Car listing
│   ├── car-details.php      # Car details page
│   ├── book-car.php         # Booking form
│   ├── dashboard.php         # User dashboard
│   ├── navbar.php           # Navigation
│   ├── footer.php           # Footer
│   └── url.php              # URL helpers
└── README.md
```

## Key Features Explained

### Authentication System
- Secure password hashing with PHP's `password_hash()`
- CSRF protection for forms
- Session management
- Role-based access control (User/Admin)

### Database Design
- Normalized database structure
- Foreign key relationships
- Indexes for performance
- JSON fields for flexible data storage

### Responsive Design
- Mobile-first approach
- Bootstrap 5 grid system
- Custom CSS variables for theming
- Touch-friendly interface elements

### Security Features
- Input sanitization and validation
- SQL injection prevention with prepared statements
- XSS protection
- File upload validation
- Admin activity logging

## Customization

### Adding New Car Categories
1. Update the `category` enum in the database
2. Add options to the category select in `add-car.php`
3. Update filter options in `cars.php`

### Modifying Theme Colors
1. Edit CSS variables in `assets/css/styles.css`
2. Update theme toggle colors in `assets/js/theme-toggle.js`

### Adding New Features
1. Create new PHP files in appropriate directories
2. Update navigation in `navbar.php`
3. Add database tables if needed
4. Update authentication checks

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP MySQL service is running
   - Verify database credentials in `config/database.php`
   - Ensure database `car_rental_db` exists

2. **File Upload Issues**
   - Check `public/assets/uploads/cars/` directory exists
   - Verify file permissions (755 or 777)
   - Check PHP upload limits in php.ini

3. **Page Not Found (404)**
   - Ensure you're accessing `public/` directory
   - Check Apache mod_rewrite is enabled
   - Verify .htaccess file exists (if using URL rewriting)

4. **Session Issues**
   - Check PHP session configuration
   - Ensure session directory is writable
   - Clear browser cookies and cache

### Performance Optimization

1. **Database Optimization**
   - Add indexes for frequently queried columns
   - Use LIMIT for pagination
   - Optimize queries with EXPLAIN

2. **Frontend Optimization**
   - Minify CSS and JavaScript files
   - Optimize images
   - Enable browser caching

3. **Server Optimization**
   - Enable PHP OPcache
   - Configure Apache caching
   - Use CDN for static assets

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support and questions:
- Email: support@carrental.com
- Phone: +1 (555) 123-4567
- Documentation: [Project Wiki](wiki-url)

## Changelog

### Version 1.0.0
- Initial release
- Complete car rental system
- User and admin dashboards
- Booking management
- Responsive design
- Theme toggle functionality

---

**Built with ❤️ using PHP, MySQL, and Bootstrap**