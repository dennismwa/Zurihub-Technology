<?php
/**
 * ZURIHUB - Create Support Ticket API (public)
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
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $subject = trim($input['subject'] ?? '');
    $message = trim($input['message'] ?? '');
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $conversationId = !empty($input['conversation_id']) ? (int) $input['conversation_id'] : null;

    $errors = [];
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required';

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    $ticketRef = 'TKT' . date('Ymd') . strtoupper(substr(uniqid(), -5));
    query(
        "INSERT INTO support_tickets (ticket_ref, subject, visitor_name, visitor_email, visitor_phone, conversation_id, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$ticketRef, $subject, $name, $email, $phone ?: null, $conversationId, getClientIP()]
    );
    $ticketId = lastInsertId();
    query(
        "INSERT INTO support_ticket_replies (ticket_id, reply_by, message) VALUES (?, 'visitor', ?)",
        [$ticketId, $message]
    );

    $emailBody = "
        <h2 style='color:#101F4C;'>New Support Ticket: {$ticketRef}</h2>
        <p><strong>From:</strong> {$name} &lt;{$email}&gt;" . ($phone ? " | {$phone}" : "") . "</p>
        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
        <h3>Message</h3>
        <div style='background:#f8f9fa;padding:15px;border-radius:8px;'>" . nl2br(htmlspecialchars($message)) . "</div>
        <p style='margin-top:20px;'><a href='" . SITE_URL . "/admin/ticket-view.php?id={$ticketId}' style='background:#3d9268;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;'>View in Admin</a></p>
    ";
    sendEmail(SITE_EMAIL, "Support Ticket {$ticketRef}: " . $subject, $emailBody, CC_EMAIL);

    echo json_encode([
        'success' => true,
        'ticket_ref' => $ticketRef,
        'ticket_id' => (int) $ticketId,
        'message' => 'Ticket created. We will get back to you soon.'
    ]);

} catch (Exception $e) {
    error_log("Create ticket error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
