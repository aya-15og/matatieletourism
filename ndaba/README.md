# Dr LK Ndaba & Partners - Medical Practice Website

A beautiful, responsive website for Dr LK Ndaba's general practice in Queenstown, built with PHP, HTML, and CSS. The design follows the "Warm Healthcare Elegance" aesthetic with a sophisticated color palette and professional typography.

## Features

- **Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices
- **Modern Aesthetics** - Warm color palette (burgundy, sage green, gold) with elegant typography
- **Appointment Booking** - Integrated appointment request form with validation
- **PHP Backend** - Server-side form processing with email notifications
- **CAPTCHA Protection** - Simple math-based CAPTCHA for spam prevention
- **Smooth Interactions** - Smooth scrolling, hover effects, and transitions
- **Professional Layout** - Clear sections for services, about, contact, and hours

## Project Structure

```
dr_ndaba_php/
├── index.php                 # Main website file
├── submit_appointment.php    # Backend form handler
├── css/
│   └── style.css            # All styling (responsive design)
├── js/
│   └── script.js            # Client-side interactions
├── images/
│   ├── hero-medical-bg.jpg  # Hero section background
│   └── about-doctor-bg.jpg  # About section image
└── README.md                # This file
```

## Setup Instructions

### Requirements

- PHP 7.0 or higher
- Web server (Apache, Nginx, etc.)
- Modern web browser

### Installation

1. **Extract Files**
   ```bash
   unzip dr_ndaba_php.zip
   cd dr_ndaba_php
   ```

2. **Configure Email Settings**
   - Open `submit_appointment.php`
   - Update the `$to` variable with the practice's email address (line 69)
   - Ensure your server is configured to send emails

3. **Upload to Web Server**
   - Upload all files to your web hosting via FTP or file manager
   - Ensure the directory is accessible via your domain

4. **Test the Website**
   - Visit your domain in a web browser
   - Test the appointment form with sample data
   - Verify emails are being received

### Email Configuration

The form sends two emails:
- **To Practice**: Full appointment details for staff review
- **To Patient**: Confirmation email with contact information

If emails are not sending:
1. Check your server's mail configuration
2. Verify the email address in `submit_appointment.php`
3. Check server error logs for mail-related errors
4. Contact your hosting provider for mail setup assistance

## Customization

### Update Contact Information

Edit `index.php` and update:
- **Phone Numbers** (line ~280)
- **Email Address** (line ~290)
- **Physical Address** (line ~265)
- **Opening Hours** (line ~295)

### Change Colors

Edit `css/style.css` and update these color variables:
- `#8b3a46` - Primary burgundy
- `#a94a56` - Burgundy accent
- `#7a9b8e` - Sage green
- `#d4a574` - Gold accent

### Update Services

Edit `index.php` in the Services section (around line ~130) to add or modify services.

### Update Images

Replace images in the `images/` folder:
- `hero-medical-bg.jpg` - Hero section background (recommended: 1920x600px)
- `about-doctor-bg.jpg` - About section image (recommended: 600x400px)

Update image references in `index.php` if filenames change.

## Form Validation

The appointment form includes:
- **Client-side validation** - JavaScript checks before submission
- **Server-side validation** - PHP validates all data
- **CAPTCHA protection** - Simple math problem to prevent spam
- **Email validation** - Ensures valid email format
- **Phone validation** - Checks for valid phone number format
- **Date validation** - Ensures future dates only

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- Optimized CSS with minimal file size
- Smooth animations and transitions
- Mobile-first responsive design
- Fast page load times
- SEO-friendly structure

## Security Features

- Input sanitization on all form fields
- HTML entity encoding to prevent XSS
- CAPTCHA validation
- Server-side form validation
- Proper error handling

## Troubleshooting

### Forms Not Submitting

1. Check browser console for JavaScript errors (F12)
2. Verify `submit_appointment.php` exists and is readable
3. Check server error logs
4. Ensure PHP is enabled on your server

### Emails Not Sending

1. Verify email address in `submit_appointment.php`
2. Check server mail configuration
3. Contact hosting provider
4. Check spam/junk folders

### Images Not Displaying

1. Verify image files exist in `images/` folder
2. Check file permissions (should be readable)
3. Verify image file names match references in HTML
4. Check browser console for 404 errors

### Styling Issues

1. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. Verify `css/style.css` is in the correct location
3. Check for CSS file permission issues
4. Verify no conflicting CSS from other sources

## Maintenance

### Regular Updates

- Keep PHP updated on your server
- Monitor form submissions for spam patterns
- Review and update contact information regularly
- Check for broken links periodically

### Backup

- Regularly backup all files
- Backup database if using additional features
- Keep version history of customizations

## Support & License

This website template is provided as-is. For modifications or custom features, consult with a web developer.

## Contact Information

**Dr LK Ndaba & Partners**
- Address: 32 Owen Street, Queenstown Central, Queenstown, 5319, Eastern Cape, South Africa
- Phone: 045 838 5418 / 045 839 3788
- Mobile: 083 271 6151
- Email: ndabalk@telkomsa.net
- Hours: Monday-Friday 08:00-18:00, Saturday 08:00-12:00, Sunday Closed

---

**Version:** 1.0  
**Last Updated:** December 2024  
**Design:** Warm Healthcare Elegance  
**Technology:** PHP, HTML5, CSS3, JavaScript
