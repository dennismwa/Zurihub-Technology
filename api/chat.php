<?php
/**
 * ZURIHUB - Chat Support API (public)
 * Start conversation, send message, get messages
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

define('ZURIHUB', true);
require_once dirname(__DIR__) . '/config/functions.php';

function generateToken() {
    return bin2hex(random_bytes(24));
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $action = $input['action'] ?? '';

        if ($action === 'start') {
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            if (empty($name) || empty($email) || !isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Valid name and email are required']);
                exit;
            }
            $token = generateToken();
            query(
                "INSERT INTO chat_conversations (visitor_name, visitor_email, visitor_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
                [$name, $email, $token, getClientIP(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]
            );
            $conversationId = lastInsertId();
            echo json_encode([
                'success' => true,
                'conversation_id' => (int) $conversationId,
                'visitor_token' => $token,
                'message' => 'Chat started'
            ]);
            exit;
        }

        if ($action === 'message') {
            $conversationId = (int)($input['conversation_id'] ?? 0);
            $token = $input['visitor_token'] ?? '';
            $message = trim($input['message'] ?? '');
            if (!$conversationId || !$token || $message === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'conversation_id, visitor_token and message required']);
                exit;
            }
            $conv = fetchOne("SELECT id FROM chat_conversations WHERE id = ? AND visitor_token = ? AND status = 'open'", [$conversationId, $token]);
            if (!$conv) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Conversation not found or closed']);
                exit;
            }
            $preview = strlen($message) > 160 ? substr($message, 0, 157) . '...' : $message;
            query("INSERT INTO chat_messages (conversation_id, sender_type, message) VALUES (?, 'visitor', ?)", [$conversationId, $message]);
            query("UPDATE chat_conversations SET last_message_at = NOW(), last_message_preview = ? WHERE id = ?", [$preview, $conversationId]);
            $msgId = lastInsertId();
            $row = fetchOne("SELECT id, message, created_at FROM chat_messages WHERE id = ?", [$msgId]);
            echo json_encode(['success' => true, 'message' => $row]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }

    if ($method === 'GET') {
        $conversationId = (int)($_GET['conversation_id'] ?? 0);
        $token = $_GET['visitor_token'] ?? '';
        if (!$conversationId || !$token) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'conversation_id and visitor_token required']);
            exit;
        }
        $conv = fetchOne("SELECT id, visitor_name, status FROM chat_conversations WHERE id = ? AND visitor_token = ?", [$conversationId, $token]);
        if (!$conv) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Conversation not found']);
            exit;
        }
        query("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_type = 'staff'", [$conversationId]);
        $messages = fetchAll(
            "SELECT id, sender_type, message, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC",
            [$conversationId]
        );
        echo json_encode(['success' => true, 'conversation' => $conv, 'messages' => $messages]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    error_log("Chat API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
