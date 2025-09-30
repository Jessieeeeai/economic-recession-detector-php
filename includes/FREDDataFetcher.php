<?php
/**
 * FRED数据获取器类
 * FRED Data Fetcher Class
 */

class FREDDataFetcher {
    private $apiKey;
    private $baseUrl = 'https://api.stlouisfed.org/fred/series/observations';
    private $cacheDir;
    private $cacheTimeout = 3600; // 1小时缓存
    
    // FRED数据系列映射
    private $fredSeries = [
        'unrate' => 'UNRATE',                    // 失业率
        'pmi' => 'MANEMP',                       // 制造业就业（PMI替代）
        'lei_yoy' => 'USALOLITOAASTSAM',         // OECD复合领先指标
        'hy_oas_bp' => 'BAMLH0A0HYM2',           // 高收益信用利差
        'ig_oas_bp' => 'BAMLC0A0CM',             // 投资级信用利差
        't10y3m_bp' => 'T10Y3M',                 // 10年-3月期利差
        't10y2y_bp' => 'T10Y2Y',                 // 10年-2年期利差
        'consumption_yoy' => 'PCEC96',           // 个人消费支出
        'retail_yoy' => 'RRSFS',                 // 零售销售
        'industrial_production' => 'INDPRO',     // 工业生产
        'capacity_utilization' => 'TCU',         // 产能利用率
        'payems' => 'PAYEMS',                    // 非农就业
        'temp_help' => 'TEMPHELPS'               // 临时工就业
    ];
    
    public function __construct() {
        $this->apiKey = $_ENV['FRED_API_KEY'] ?? '';
        $this->cacheDir = __DIR__ . '/../data/cache/';
        
        // 创建缓存目录
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * 获取最新数据
     */
    public function fetchLatestData() {
        if (empty($this->apiKey)) {
            throw new Exception("FRED API key not configured");
        }
        
        $cacheFile = $this->cacheDir . 'latest_data.json';
        
        // 检查缓存
        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            if (time() - $cacheTime < $this->cacheTimeout) {
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData) {
                    return $cachedData;
                }
            }
        }
        
        // 获取新数据
        $data = $this->fetchFromFRED();
        
        // 保存到缓存
        if (!empty($data)) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }
    
    /**
     * 获取历史数据
     */
    public function fetchHistoricalData($months = 60) {
        if (empty($this->apiKey)) {
            throw new Exception("FRED API key not configured");
        }
        
        $cacheFile = $this->cacheDir . "historical_{$months}m.json";
        
        // 检查缓存
        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            if (time() - $cacheTime < $this->cacheTimeout * 24) { // 24小时缓存
                $cachedData = json_decode(file_get_contents($cacheFile), true);
                if ($cachedData) {
                    return $cachedData;
                }
            }
        }
        
        // 获取新数据
        $startDate = date('Y-m-d', strtotime("-{$months} months"));
        $data = $this->fetchFromFRED($startDate);
        
        // 保存到缓存
        if (!empty($data)) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }
    
    /**
     * 从FRED API获取数据
     */
    private function fetchFromFRED($startDate = null) {
        $allData = [];
        
        // 设置默认开始日期（最近5年）
        if (!$startDate) {
            $startDate = date('Y-m-d', strtotime('-5 years'));
        }
        
        foreach ($this->fredSeries as $key => $seriesId) {
            try {
                $seriesData = $this->fetchSeries($seriesId, $startDate);
                
                // 将数据合并到主数组
                foreach ($seriesData as $date => $value) {
                    if (!isset($allData[$date])) {
                        $allData[$date] = ['date' => $date];
                    }
                    $allData[$date][$key] = $value;
                }
                
                // 添加小延迟以避免API限制
                usleep(100000); // 0.1秒
                
            } catch (Exception $e) {
                error_log("Error fetching FRED series {$seriesId}: " . $e->getMessage());
            }
        }
        
        // 按日期排序
        ksort($allData);
        
        // 计算衍生指标
        $processedData = $this->processData(array_values($allData));
        
        return $processedData;
    }
    
    /**
     * 获取单个FRED数据系列
     */
    private function fetchSeries($seriesId, $startDate) {
        $url = $this->baseUrl . '?' . http_build_query([
            'series_id' => $seriesId,
            'api_key' => $this->apiKey,
            'file_type' => 'json',
            'observation_start' => $startDate,
            'observation_end' => date('Y-m-d'),
            'frequency' => 'm', // 月度数据
            'aggregation_method' => 'avg'
        ]);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Economic Recession Detector PHP/1.0'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            throw new Exception("Failed to fetch data from FRED API for series: {$seriesId}");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['observations'])) {
            throw new Exception("Invalid response format from FRED API for series: {$seriesId}");
        }
        
        $seriesData = [];
        
        foreach ($data['observations'] as $obs) {
            $date = $obs['date'];
            $value = $obs['value'];
            
            // 跳过无效值
            if ($value === '.' || !is_numeric($value)) {
                continue;
            }
            
            $seriesData[$date] = (float)$value;
        }
        
        return $seriesData;
    }
    
    /**
     * 处理数据，计算同比变化和其他衍生指标
     */
    private function processData($data) {
        $processed = [];
        
        for ($i = 0; $i < count($data); $i++) {
            $current = $data[$i];
            $processed[$i] = $current;
            
            // 计算同比变化 (需要12个月前的数据)
            if ($i >= 12) {
                $prev12 = $data[$i - 12];
                
                // PMI同比变化
                if (isset($current['pmi']) && isset($prev12['pmi']) && $prev12['pmi'] > 0) {
                    $processed[$i]['pmi'] = (($current['pmi'] - $prev12['pmi']) / $prev12['pmi']) * 100;
                }
                
                // LEI同比变化
                if (isset($current['lei_yoy']) && isset($prev12['lei_yoy']) && $prev12['lei_yoy'] > 0) {
                    $processed[$i]['lei_yoy'] = (($current['lei_yoy'] - $prev12['lei_yoy']) / $prev12['lei_yoy']) * 100;
                }
                
                // 消费同比变化
                if (isset($current['consumption_yoy']) && isset($prev12['consumption_yoy']) && $prev12['consumption_yoy'] > 0) {
                    $processed[$i]['consumption_yoy'] = (($current['consumption_yoy'] - $prev12['consumption_yoy']) / $prev12['consumption_yoy']) * 100;
                }
                
                // 零售销售同比变化
                if (isset($current['retail_yoy']) && isset($prev12['retail_yoy']) && $prev12['retail_yoy'] > 0) {
                    $processed[$i]['retail_yoy'] = (($current['retail_yoy'] - $prev12['retail_yoy']) / $prev12['retail_yoy']) * 100;
                }
            }
            
            // 转换利差为基点 (如果需要)
            if (isset($processed[$i]['t10y3m_bp'])) {
                $processed[$i]['t10y3m_bp'] *= 100; // 转换为基点
            }
            if (isset($processed[$i]['t10y2y_bp'])) {
                $processed[$i]['t10y2y_bp'] *= 100; // 转换为基点
            }
        }
        
        return $processed;
    }
    
    /**
     * 检查API连接状态
     */
    public function testConnection() {
        if (empty($this->apiKey)) {
            return false;
        }
        
        try {
            $testUrl = 'https://api.stlouisfed.org/fred/series?' . http_build_query([
                'series_id' => 'UNRATE',
                'api_key' => $this->apiKey,
                'file_type' => 'json'
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Economic Recession Detector PHP/1.0'
                ]
            ]);
            
            $response = file_get_contents($testUrl, false, $context);
            
            if ($response === FALSE) {
                return false;
            }
            
            $data = json_decode($response, true);
            return isset($data['seriess']);
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * 清除缓存
     */
    public function clearCache() {
        $files = glob($this->cacheDir . '*.json');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
?>