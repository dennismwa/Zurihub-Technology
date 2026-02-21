<?php
/**
 * ZURIHUB TECHNOLOGY - Get Portfolio Projects API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('ZURIHUB', true);
require_once dirname(__DIR__) . '/config/functions.php';

try {
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;
    
    $where = ["is_active = 1"];
    $params = [];
    
    if ($category) {
        $where[] = "category_id = ?";
        $params[] = $category;
    }
    
    if ($featured) {
        $where[] = "is_featured = 1";
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Get total count
    $total = fetchOne("SELECT COUNT(*) as count FROM portfolio_projects WHERE $whereClause", $params)['count'];
    
    // Get projects
    $params[] = $limit;
    $params[] = $offset;
    
    $projects = fetchAll("
        SELECT 
            p.id, p.title, p.slug, p.client_name, p.short_description,
            p.full_description, p.challenge, p.solution, p.results,
            p.thumbnail, p.images, p.technologies, p.features,
            p.project_url, p.completion_date, p.is_featured,
            sc.name as category_name, sc.slug as category_slug
        FROM portfolio_projects p
        LEFT JOIN service_categories sc ON p.category_id = sc.id
        WHERE $whereClause
        ORDER BY p.sort_order ASC, p.created_at DESC
        LIMIT ? OFFSET ?
    ", $params);
    
    // Decode JSON fields
    foreach ($projects as &$project) {
        $project['images'] = json_decode($project['images'], true) ?: [];
        $project['technologies'] = json_decode($project['technologies'], true) ?: [];
        $project['features'] = json_decode($project['features'], true) ?: [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $projects,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Portfolio API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
