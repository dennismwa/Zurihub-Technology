<?php
/**
 * ZURIHUB TECHNOLOGY - Get Pricing Packages API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('ZURIHUB', true);
require_once dirname(__DIR__) . '/config/functions.php';

try {
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    
    $where = ["pp.is_active = 1"];
    $params = [];
    
    if ($category) {
        $where[] = "pp.category_id = ?";
        $params[] = $category;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $packages = fetchAll("
        SELECT 
            pp.id, pp.name, pp.slug, pp.description, pp.price, pp.original_price,
            pp.currency, pp.billing_type, pp.features, pp.is_popular, pp.sort_order,
            sc.id as category_id, sc.name as category_name, sc.slug as category_slug
        FROM pricing_packages pp
        LEFT JOIN service_categories sc ON pp.category_id = sc.id
        WHERE $whereClause
        ORDER BY pp.category_id, pp.sort_order, pp.price
    ", $params);
    
    // Decode features and group by category
    $grouped = [];
    foreach ($packages as $pkg) {
        $pkg['features'] = json_decode($pkg['features'], true) ?: [];
        
        $catId = $pkg['category_id'] ?? 0;
        if (!isset($grouped[$catId])) {
            $grouped[$catId] = [
                'id' => $catId,
                'name' => $pkg['category_name'] ?? 'General',
                'slug' => $pkg['category_slug'] ?? 'general',
                'packages' => []
            ];
        }
        $grouped[$catId]['packages'][] = $pkg;
    }
    
    // Get categories
    $categories = fetchAll("SELECT id, name, slug FROM service_categories WHERE is_active = 1 ORDER BY sort_order");
    
    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'packages' => array_values($grouped),
        'flat' => $packages
    ]);
    
} catch (Exception $e) {
    error_log("Pricing API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
