<?php
/**
 * 图表数据API接口
 * Chart Data API Endpoint
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// 包含必要的类文件
require_once '../includes/EconomicAnalyzer.php';
require_once '../includes/FREDDataFetcher.php';
require_once '../includes/RiskCalculator.php';

try {
    // 初始化分析器
    $analyzer = new EconomicAnalyzer();
    $riskCalculator = new RiskCalculator();
    
    // 获取当前数据
    $currentData = $analyzer->getCurrentData();
    
    // 获取历史数据 (最近60个月)
    $historicalData = $analyzer->getHistoricalData(60);
    
    // 计算历史风险趋势
    $historicalTrend = $riskCalculator->calculateHistoricalTrend($historicalData);
    
    // 准备响应数据
    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'current' => $currentData,
            'historical' => $historicalTrend
        ]
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