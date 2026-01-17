<?php
/**
 * Email Functions for Booking System
 * Handles all email notifications for bookings and inquiries
 */

// Email configuration
define('SITE_NAME', 'Matatiele Tourism');
define('SITE_URL', 'https://matatiele.co.za');
define('ADMIN_EMAIL', 'admin@matatiele.co.za');
define('FROM_EMAIL', 'bookings@matatiele.co.za');
define('FROM_NAME', 'Matatiele Tourism Bookings');

/**
 * Send confirmation email to guest for partner bookings
 */
function send_partner_booking_confirmation($guest_email, $data) {
    $subject = "Booking Confirmed - {$data['booking_reference']}";
    
    $message = get_email_template('partner_confirmation', [
        'guest_name' => $data['guest_name'],
        'booking_reference' => $data['booking_reference'],
        'stay_name' => $data['stay_name'],
        'stay_location' => $data['stay_location'],
        'check_in_date' => $data['check_in_date'],
        'check_out_date' => $data['check_out_date'],
        'number_of_guests' => $data['number_of_guests'],
        'number_of_nights' => $data['number_of_nights'],
        'total_amount' => $data['total_amount'],
        'special_requests' => $data['special_requests']
    ]);
    
    return send_email($guest_email, $subject, $message);
}

/**
 * Send booking notification to partner property
 */
function send_partner_booking_notification($property_email, $data) {
    $subject = "New Booking Received - {$data['booking_reference']}";
    
    $message = get_email_template('partner_notification', [
        'booking_reference' => $data['booking_reference'],
        'stay_name' => $data['stay_name'],
        'guest_name' => $data['guest_name'],
        'guest_email' => $data['guest_email'],
        'guest_phone' => $data['guest_phone'],
        'check_in_date' => $data['check_in_date'],
        'check_out_date' => $data['check_out_date'],
        'number_of_guests' => $data['number_of_guests'],
        'number_of_nights' => $data['number_of_nights'],
        'total_amount' => $data['total_amount'],
        'commission_amount' => $data['commission_amount'],
        'special_requests' => $data['special_requests']
    ]);
    
    return send_email($property_email, $subject, $message);
}

/**
 * Send inquiry email to non-partner property
 */
function send_inquiry_to_property($property_email, $data) {
    $subject = "New Booking Inquiry from Matatiele Tourism - {$data['booking_reference']}";
    
    $message = get_email_template('inquiry_to_property', [
        'booking_reference' => $data['booking_reference'],
        'stay_name' => $data['stay_name'],
        'guest_name' => $data['guest_name'],
        'guest_email' => $data['guest_email'],
        'guest_phone' => $data['guest_phone'],
        'check_in_date' => $data['check_in_date'],
        'check_out_date' => $data['check_out_date'],
        'number_of_guests' => $data['number_of_guests'],
        'number_of_nights' => $data['number_of_nights'],
        'special_requests' => $data['special_requests']
    ]);
    
    return send_email($property_email, $subject, $message);
}

/**
 * Send inquiry confirmation to guest
 */
function send_inquiry_confirmation_to_guest($guest_email, $data) {
    $subject = "Inquiry Sent - {$data['booking_reference']}";
    
    $message = get_email_template('inquiry_confirmation', [
        'guest_name' => $data['guest_name'],
        'booking_reference' => $data['booking_reference'],
        'stay_name' => $data['stay_name'],
        'stay_location' => $data['stay_location'],
        'stay_contact' => $data['stay_contact'],
        'check_in_date' => $data['check_in_date'],
        'check_out_date' => $data['check_out_date'],
        'number_of_guests' => $data['number_of_guests']
    ]);
    
    return send_email($guest_email, $subject, $message);
}

/**
 * Send booking notification to admin
 */
function send_admin_booking_notification($admin_email, $data) {
    $type = $data['is_partner'] ? 'Confirmed Booking' : 'Inquiry';
    $subject = "New {$type} - {$data['booking_reference']}";
    
    $message = get_email_template('admin_notification', [
        'type' => $type,
        'booking_reference' => $data['booking_reference'],
        'stay_name' => $data['stay_name'],
        'guest_name' => $data['guest_name'],
        'guest_email' => $data['guest_email'],
        'guest_phone' => $data['guest_phone'],
        'check_in_date' => $data['check_in_date'],
        'check_out_date' => $data['check_out_date'],
        'number_of_guests' => $data['number_of_guests'],
        'total_amount' => $data['total_amount'],
        'commission_amount' => $data['commission_amount'],
        'is_partner' => $data['is_partner']
    ]);
    
    return send_email($admin_email, $subject, $message);
}

/**
 * Send admin inquiry notification
 */
function send_admin_inquiry_notification($admin_email, $data) {
    return send_admin_booking_notification($admin_email, $data);
}

/**
 * Get email template with replaced variables
 */
function get_email_template($template, $data) {
    $templates = [
        'partner_confirmation' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #2563eb, #0ea5e9); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e7eb; }
        .booking-details { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-row:last-child { border-bottom: none; }
        .label { font-weight: 600; color: #6b7280; }
        .value { font-weight: 600; color: #1f2937; }
        .success-badge { background: #10b981; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
        .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Booking Confirmed!</h1>
        </div>
        <div class="content">
            <p>Dear {{guest_name}},</p>
            <p>Great news! Your booking has been confirmed.</p>
            
            <div class="success-badge">✓ Booking Reference: {{booking_reference}}</div>
            
            <div class="booking-details">
                <h3>Booking Details</h3>
                <div class="detail-row">
                    <span class="label">Accommodation:</span>
                    <span class="value">{{stay_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Location:</span>
                    <span class="value">{{stay_location}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-in:</span>
                    <span class="value">{{check_in_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-out:</span>
                    <span class="value">{{check_out_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guests:</span>
                    <span class="value">{{number_of_guests}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Nights:</span>
                    <span class="value">{{number_of_nights}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Amount:</span>
                    <span class="value">R {{total_amount}}</span>
                </div>
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>You will receive payment instructions shortly</li>
                <li>The property has been notified of your booking</li>
                <li>Please contact the property directly for any special arrangements</li>
            </ul>
            
            <p>If you have any questions, please don\'t hesitate to contact us.</p>
            
            <p>Safe travels!<br>The Matatiele Tourism Team</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Matatiele Tourism. All rights reserved.</p>
            <p>This booking was made through Matatiele Tourism Platform</p>
        </div>
    </div>
</body>
</html>
        ',
        
        'partner_notification' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1f2937; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e7eb; }
        .booking-details { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: 600; color: #6b7280; }
        .value { font-weight: 600; color: #1f2937; }
        .commission-note { background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏨 New Booking Received</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>You have received a new booking through Matatiele Tourism Platform.</p>
            
            <div class="booking-details">
                <h3>Booking Reference: {{booking_reference}}</h3>
                <div class="detail-row">
                    <span class="label">Property:</span>
                    <span class="value">{{stay_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Name:</span>
                    <span class="value">{{guest_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Email:</span>
                    <span class="value">{{guest_email}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Phone:</span>
                    <span class="value">{{guest_phone}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-in:</span>
                    <span class="value">{{check_in_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-out:</span>
                    <span class="value">{{check_out_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guests:</span>
                    <span class="value">{{number_of_guests}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Booking Total:</span>
                    <span class="value">R {{total_amount}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Platform Commission (9.5%):</span>
                    <span class="value">R {{commission_amount}}</span>
                </div>
            </div>
            
            <div class="commission-note">
                <strong>Payment Details:</strong><br>
                The platform commission of R {{commission_amount}} will be deducted from the booking payment. You will receive R ' . '{{net_amount}}' . ' after commission.
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Please confirm the booking with the guest if needed</li>
                <li>Prepare the accommodation for the arrival date</li>
                <li>Contact the guest directly for any special arrangements</li>
            </ul>
            
            <p>Thank you for being a valued partner!</p>
            <p>Best regards,<br>Matatiele Tourism Team</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Matatiele Tourism. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
        ',
        
        'inquiry_to_property' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #2563eb, #0ea5e9); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e7eb; }
        .inquiry-details { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: 600; color: #6b7280; display: block; margin-bottom: 5px; }
        .value { color: #1f2937; }
        .partner-invite { background: #eff6ff; padding: 20px; border-left: 4px solid #2563eb; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 New Booking Inquiry</h1>
            <p>From Matatiele Tourism Platform</p>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>A potential guest has submitted a booking inquiry for your property through the <strong>Matatiele Tourism Platform</strong>.</p>
            
            <div class="inquiry-details">
                <h3>Inquiry Reference: {{booking_reference}}</h3>
                <div class="detail-row">
                    <span class="label">Property:</span>
                    <span class="value">{{stay_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Name:</span>
                    <span class="value">{{guest_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Email:</span>
                    <span class="value">{{guest_email}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Guest Phone:</span>
                    <span class="value">{{guest_phone}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Requested Check-in:</span>
                    <span class="value">{{check_in_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Requested Check-out:</span>
                    <span class="value">{{check_out_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Number of Guests:</span>
                    <span class="value">{{number_of_guests}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Duration:</span>
                    <span class="value">{{number_of_nights}} night(s)</span>
                </div>
            </div>
            
            <p><strong>Action Required:</strong></p>
            <p>Please contact the guest directly at <strong>{{guest_email}}</strong> or <strong>{{guest_phone}}</strong> to confirm availability and pricing.</p>
            
            <div class="partner-invite">
                <h4>🤝 Become a Partner Property!</h4>
                <p>Join our partner program to receive instant bookings with automatic confirmations and increase your visibility. Benefits include:</p>
                <ul>
                    <li>Instant booking confirmations</li>
                    <li>Automated payment processing</li>
                    <li>Featured placement on our platform</li>
                    <li>Access to our growing customer base</li>
                </ul>
                <p><strong>Commission:</strong> Only 9.5% on confirmed bookings</p>
                <p>Interested? Contact us at <a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a></p>
            </div>
            
            <p>Thank you,<br>Matatiele Tourism Team</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Matatiele Tourism. All rights reserved.</p>
            <p><strong>Note:</strong> This inquiry was submitted through Matatiele Tourism (matatiele.co.za)</p>
        </div>
    </div>
</body>
</html>
        ',
        
        'inquiry_confirmation' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #fff; padding: 30px; border: 1px solid #e5e7eb; }
        .inquiry-details { background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .label { font-weight: 600; color: #6b7280; }
        .value { font-weight: 600; color: #1f2937; }
        .info-box { background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📨 Inquiry Sent Successfully</h1>
        </div>
        <div class="content">
            <p>Dear {{guest_name}},</p>
            <p>Thank you for your interest! Your booking inquiry has been sent to the property.</p>
            
            <div class="info-box">
                <strong>⚠️ Important Information:</strong><br>
                This property is not a partner accommodation. Your inquiry has been forwarded directly to them via email. The property will contact you directly to confirm availability and finalize booking details. Response times may vary.
            </div>
            
            <div class="inquiry-details">
                <h3>Your Inquiry Details</h3>
                <div class="detail-row">
                    <span class="label">Reference Number:</span>
                    <span class="value">{{booking_reference}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Property:</span>
                    <span class="value">{{stay_name}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Location:</span>
                    <span class="value">{{stay_location}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-in Date:</span>
                    <span class="value">{{check_in_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Check-out Date:</span>
                    <span class="value">{{check_out_date}}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Number of Guests:</span>
                    <span class="value">{{number_of_guests}}</span>
                </div>
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>The property will review your inquiry</li>
                <li>They will contact you directly at your email or phone</li>
                <li>You can also contact them directly at: {{stay_contact}}</li>
                <li>Please confirm availability and payment directly with the property</li>
            </ul>
            
            <p>If you don\'t hear back within 48 hours, we recommend contacting the property directly.</p>
            
            <p>Thank you for using Matatiele Tourism!<br>Safe travels!</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Matatiele Tourism. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
        ',
        
        'admin_notification' => '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1f2937; color: white; padding: 20px; text-align: center; }
        .content { background: #fff; padding: 20px; border: 1px solid #e5e7eb; }
        .booking-details { background: #f9fafb; padding: 15px; margin: 15px 0; }
        .detail-row { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Admin Notification: {{type}}</h2>
        </div>
        <div class="content">
            <div class="booking-details">
                <strong>Reference:</strong> {{booking_reference}}<br>
                <strong>Type:</strong> {{type}}<br>
                <strong>Property:</strong> {{stay_name}}<br>
                <strong>Guest:</strong> {{guest_name}}<br>
                <strong>Email:</strong> {{guest_email}}<br>
                <strong>Phone:</strong> {{guest_phone}}<br>
                <strong>Check-in:</strong> {{check_in_date}}<br>
                <strong>Check-out:</strong> {{check_out_date}}<br>
                <strong>Guests:</strong> {{number_of_guests}}<br>
                <strong>Total:</strong> R {{total_amount}}<br>
                <strong>Commission:</strong> R {{commission_amount}}<br>
                <strong>Partner:</strong> {{is_partner}}
            </div>
        </div>
    </div>
</body>
</html>
        '
    ];
    
    $template = $templates[$template] ?? '';
    
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    
    return $template;
}

/**
 * Send email using PHP mail function
 * For production, consider using PHPMailer or similar library
 */
function send_email($to, $subject, $message) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}