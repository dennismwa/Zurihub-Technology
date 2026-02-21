<?php
/**
 * ZURIHUB TECHNOLOGY - Contact Form Submission API
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
    
    $data = [
        'full_name' => sanitize($input['full_name'] ?? ''),
        'email' => sanitize($input['email'] ?? ''),
        'phone' => sanitize($input['phone'] ?? ''),
        'subject' => sanitize($input['subject'] ?? ''),
        'message' => sanitize($input['message'] ?? ''),
        'ip_address' => getClientIP()
    ];
    
    // Validation
    $errors = [];
    if (empty($data['full_name'])) $errors[] = 'Name is required';
    if (empty($data['email']) || !isValidEmail($data['email'])) $errors[] = 'Valid email is required';
    if (empty($data['message'])) $errors[] = 'Message is required';
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Insert
    query(
        "INSERT INTO contact_messages (full_name, email, phone, subject, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
        [$data['full_name'], $data['email'], $data['phone'], $data['subject'], $data['message'], $data['ip_address']]
    );
    
    $messageId = lastInsertId();
    
    // Send notification email
    $emailBody = "
        <h2 style='color:#101F4C;'>New Contact Message</h2>
        
        <table style='width:100%;border-collapse:collapse;'>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;width:120px;'>Name</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['full_name']}</td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Email</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'><a href='mailto:{$data['email']}'>{$data['email']}</a></td>
            </tr>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Phone</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['phone']}</td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Subject</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['subject']}</td>
            </tr>
        </table>
        
        <h3 style='color:#101F4C;margin:20px 0 10px;'>Message</h3>
        <div style='background:#f8f9fa;padding:15px;border-radius:8px;border:1px solid #e5e7eb;'>
            " . nl2br(htmlspecialchars($data['message'])) . "
        </div>
        
        <div style='margin-top:30px;text-align:center;'>
            <a href='" . SITE_URL . "/admin/messages.php' 
               style='display:inline-block;background:#3d9268;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;'>
                View in Admin
            </a>
        </div>
    ";
    
    sendEmail(
        SITE_EMAIL,
        "New Contact: " . ($data['subject'] ?: 'General Inquiry') . " - from {$data['full_name']}",
        $emailBody,
        CC_EMAIL
    );
    
    logActivity('create', 'contact_messages', $messageId, "Contact from: {$data['full_name']}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Contact submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
