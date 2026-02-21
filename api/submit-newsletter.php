<?php
/**
 * ZURIHUB TECHNOLOGY - Newsletter Subscription API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

define('ZURIHUB', true);
require_once dirname(__DIR__) . '/config/functions.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $email = sanitize($input['email'] ?? '');
    $name = sanitize($input['name'] ?? '');
    
    if (empty($email) || !isValidEmail($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Valid email is required']);
        exit;
    }
    
    // Check if already subscribed
    $existing = fetchOne("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$email]);
    
    if ($existing) {
        if ($existing['is_active']) {
            echo json_encode(['success' => true, 'message' => 'You are already subscribed']);
        } else {
            query("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL WHERE id = ?", [$existing['id']]);
            echo json_encode(['success' => true, 'message' => 'Welcome back! Your subscription has been reactivated.']);
        }
        exit;
    }
    
    // Insert new subscriber
    query("INSERT INTO newsletter_subscribers (email, name) VALUES (?, ?)", [$email, $name]);
    
    // Send welcome email
    $emailBody = "
        <h2 style='color:#101F4C;'>Welcome to Zurihub Technology!</h2>
        <p>Thank you for subscribing to our newsletter.</p>
        <p>You'll now receive updates about:</p>
        <ul>
            <li>New services and features</li>
            <li>Industry insights and tips</li>
            <li>Exclusive offers and promotions</li>
            <li>Tech news and updates</li>
        </ul>
        <p>Stay tuned for valuable content!</p>
        <p style='margin-top:20px;'>Best regards,<br><strong>Zurihub Technology Team</strong></p>
    ";
    
    sendEmail($email, "Welcome to Zurihub Technology Newsletter!", $emailBody);
    
    echo json_encode([
        'success' => true,
        'message' => 'Successfully subscribed!'
    ]);
    
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
