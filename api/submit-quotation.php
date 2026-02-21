<?php
/**
 * ZURIHUB TECHNOLOGY - Quotation Submission API
 * Receives quotation requests and sends email notifications
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
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
    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Sanitize input
    $data = [
        'full_name' => sanitize($input['full_name'] ?? ''),
        'email' => sanitize($input['email'] ?? ''),
        'phone' => sanitize($input['phone'] ?? ''),
        'company_name' => sanitize($input['company_name'] ?? ''),
        'project_type' => sanitize($input['project_type'] ?? ''),
        'category_id' => isset($input['category_id']) ? (int)$input['category_id'] : null,
        'package_id' => isset($input['package_id']) ? (int)$input['package_id'] : null,
        'budget_range' => sanitize($input['budget_range'] ?? ''),
        'timeline' => sanitize($input['timeline'] ?? ''),
        'project_description' => sanitize($input['project_description'] ?? ''),
        'requirements' => sanitize($input['requirements'] ?? ''),
        'how_found_us' => sanitize($input['how_found_us'] ?? ''),
        'ip_address' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
    
    // Validation
    $errors = [];
    if (empty($data['full_name'])) {
        $errors[] = 'Full name is required';
    }
    if (empty($data['email']) || !isValidEmail($data['email'])) {
        $errors[] = 'Valid email address is required';
    }
    if (empty($data['project_description'])) {
        $errors[] = 'Project description is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Generate reference number
    $data['reference_no'] = generateRefNo('QT');
    
    // Insert into database
    $fields = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    query(
        "INSERT INTO quotation_requests ($fields) VALUES ($placeholders)",
        array_values($data)
    );
    
    $quotationId = lastInsertId();
    
    // Send email notification
    $emailBody = "
        <h2 style='color:#101F4C;margin-bottom:20px;'>New Quotation Request</h2>
        
        <table style='width:100%;border-collapse:collapse;'>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;width:150px;'>Reference</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['reference_no']}</td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Name</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['full_name']}</td>
            </tr>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Email</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'><a href='mailto:{$data['email']}'>{$data['email']}</a></td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Phone</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['phone']}</td>
            </tr>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Company</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['company_name']}</td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Project Type</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['project_type']}</td>
            </tr>
            <tr style='background:#f8f9fa;'>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Budget Range</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['budget_range']}</td>
            </tr>
            <tr>
                <td style='padding:12px;border:1px solid #e5e7eb;font-weight:600;'>Timeline</td>
                <td style='padding:12px;border:1px solid #e5e7eb;'>{$data['timeline']}</td>
            </tr>
        </table>
        
        <h3 style='color:#101F4C;margin:20px 0 10px;'>Project Description</h3>
        <div style='background:#f8f9fa;padding:15px;border-radius:8px;border:1px solid #e5e7eb;'>
            " . nl2br(htmlspecialchars($data['project_description'])) . "
        </div>
        
        " . ($data['requirements'] ? "
        <h3 style='color:#101F4C;margin:20px 0 10px;'>Specific Requirements</h3>
        <div style='background:#f8f9fa;padding:15px;border-radius:8px;border:1px solid #e5e7eb;'>
            " . nl2br(htmlspecialchars($data['requirements'])) . "
        </div>
        " : "") . "
        
        <div style='margin-top:30px;padding:15px;background:#3d9268;border-radius:8px;text-align:center;'>
            <a href='" . SITE_URL . "/admin/quotation-view.php?id={$quotationId}' 
               style='color:#fff;text-decoration:none;font-weight:600;'>
                View in Admin Dashboard →
            </a>
        </div>
    ";
    
    // Send to admin email with CC
    sendEmail(
        SITE_EMAIL,
        "New Quotation Request: {$data['reference_no']} - {$data['project_type']}",
        $emailBody,
        CC_EMAIL
    );
    
    // Send confirmation to client
    $clientEmailBody = "
        <h2 style='color:#101F4C;'>Thank You for Your Inquiry!</h2>
        
        <p>Dear {$data['full_name']},</p>
        
        <p>We have received your quotation request and our team is reviewing it. 
        We typically respond within 24-48 business hours.</p>
        
        <div style='background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;'>
            <p style='margin:0;'><strong>Reference Number:</strong> {$data['reference_no']}</p>
            <p style='margin:10px 0 0;'><strong>Project Type:</strong> {$data['project_type']}</p>
        </div>
        
        <p>If you have any urgent questions, feel free to contact us:</p>
        <ul style='margin:0;padding-left:20px;'>
            <li>Email: <a href='mailto:info@zurihub.co.ke'>info@zurihub.co.ke</a></li>
            <li>Phone: +254 758 256 440</li>
            <li>WhatsApp: +254 758 256 440</li>
        </ul>
        
        <p style='margin-top:20px;'>Best regards,<br><strong>Zurihub Technology Team</strong></p>
    ";
    
    sendEmail(
        $data['email'],
        "We've Received Your Request - {$data['reference_no']}",
        $clientEmailBody
    );
    
    // Log activity
    logActivity('create', 'quotation_requests', $quotationId, "New quotation: {$data['reference_no']}");
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Quotation submitted successfully',
        'reference_no' => $data['reference_no'],
        'id' => $quotationId
    ]);
    
} catch (Exception $e) {
    error_log("Quotation submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred. Please try again.'
    ]);
}
