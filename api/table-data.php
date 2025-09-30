<?php
/**
 * 数据表格API接口
 * Data Table API Endpoint
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// 包含必要的类文件
require_once '../includes/EconomicAnalyzer.php';
require_once '../includes/FREDDataFetcher.php';

try {
    // 获取查询参数
    $months = isset($_GET['months']) ? intval($_GET['months']) : 60;
    $months = min(max($months, 12), 120); // 限制在12-120个月之间
    
    // 初始化分析器
    $analyzer = new EconomicAnalyzer();
    
    // 获取历史数据
    $historicalData = $analyzer->getHistoricalData($months);
    
    if (empty($historicalData)) {
        throw new Exception('No historical data available');
    }
    
    // 按日期降序排列 (最新的在前)
    usort($historicalData, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    // 准备响应数据
    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'data_count' => count($historicalData),
        'date_range' => [
            'start' => end($historicalData)['date'] ?? null,
            'end' => $historicalData[0]['date'] ?? null
        ],
        'data' => $historicalData
    ];
    
    // 输出JSON响应
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // 错误处理
    http_response_code(500);
    
    $errorResponse = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}
?>