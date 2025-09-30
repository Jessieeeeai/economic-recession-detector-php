<?php
/**
 * 经济数据分析器类
 * Economic Data Analyzer Class
 */

class EconomicAnalyzer {
    private $dataFile;
    private $fredFetcher;
    
    public function __construct() {
        $this->dataFile = __DIR__ . '/../data/indicators_fred.csv';
        $this->fredFetcher = new FREDDataFetcher();
    }
    
    /**
     * 获取当前最新的经济数据
     * Get current latest economic data
     */
    public function getCurrentData() {
        // 尝试获取实时数据，失败则使用本地数据
        try {
            $data = $this->fredFetcher->fetchLatestData();
            if (empty($data)) {
                throw new Exception("No data from FRED API");
            }
        } catch (Exception $e) {
            $data = $this->loadLocalData();
        }
        
        return $this->processCurrentData($data);
    }
    
    /**
     * 获取历史数据用于图表显示
     * Get historical data for chart display
     */
    public function getHistoricalData($months = 60) {
        try {
            $data = $this->fredFetcher->fetchHistoricalData($months);
            if (empty($data)) {
                throw new Exception("No historical data from FRED API");
            }
        } catch (Exception $e) {
            $data = $this->loadLocalData();
        }
        
        return $this->processHistoricalData($data);
    }
    
    /**
     * 处理当前数据，计算各指标状态
     */
    private function processCurrentData($data) {
        if (empty($data)) {
            throw new Exception("No data available for analysis");
        }
        
        $latest = end($data);
        $processed = [];
        
        // 失业率分析 (Sahm Rule)
        $processed['unemployment'] = $this->analyzeUnemployment($data);
        
        // PMI制造业指标
        $processed['pmi'] = $this->analyzePMI($data);
        
        // 信用利差
        $processed['credit'] = $this->analyzeCreditSpread($data);
        
        // 收益率曲线
        $processed['yield'] = $this->analyzeYieldCurve($data);
        
        // 先行经济指标 (LEI)
        $processed['lei'] = $this->analyzeLEI($data);
        
        // 消费指标
        $processed['consumption'] = $this->analyzeConsumption($data);
        
        return $processed;
    }
    
    /**
     * 分析失业率 - 实施Sahm规则
     */
    private function analyzeUnemployment($data) {
        $recentData = array_slice($data, -12); // 最近12个月
        
        if (count($recentData) < 3) {
            return ['value' => 0, 'color' => 'secondary', 'status' => 'insufficient_data'];
        }
        
        // 获取最近3个月的平均失业率
        $recent3Months = array_slice($recentData, -3);
        $recent3AvgUnrate = array_sum(array_column($recent3Months, 'unrate')) / count($recent3Months);
        
        // 获取过去12个月的最低失业率
        $past12MonthsMin = min(array_column($recentData, 'unrate'));
        
        // Sahm规则：3个月平均失业率比过去12个月最低点高0.5个百分点
        $sahmDifference = $recent3AvgUnrate - $past12MonthsMin;
        $sahmTriggered = $sahmDifference >= 0.5;
        
        $currentUnrate = end($recentData)['unrate'] ?? 0;
        
        return [
            'value' => $currentUnrate,
            'color' => $sahmTriggered ? 'danger' : 'success',
            'status' => $sahmTriggered ? 'sahm_triggered' : 'normal',
            'sahm_difference' => $sahmDifference
        ];
    }
    
    /**
     * 分析PMI制造业指标
     */
    private function analyzePMI($data) {
        $recentData = array_slice($data, -12); // 最近12个月
        
        if (empty($recentData)) {
            return ['value' => 0, 'color' => 'secondary', 'status' => 'no_data'];
        }
        
        $current = end($recentData);
        $pmiValue = $current['pmi'] ?? 0;
        
        // PMI阈值判断
        if ($pmiValue > 0) {
            $color = 'success';
            $status = 'expanding';
        } elseif ($pmiValue > -2) {
            $color = 'warning'; 
            $status = 'slowing';
        } else {
            $color = 'danger';
            $status = 'contracting';
        }
        
        return [
            'value' => $pmiValue,
            'color' => $color,
            'status' => $status
        ];
    }
    
    /**
     * 分析信用利差
     */
    private function analyzeCreditSpread($data) {
        $current = end($data);
        $hySpread = $current['hy_oas_bp'] ?? 0;
        
        // 信用利差阈值 (基点)
        if ($hySpread < 300) {
            $color = 'success';
            $status = 'healthy';
        } elseif ($hySpread < 500) {
            $color = 'warning';
            $status = 'elevated';
        } else {
            $color = 'danger';
            $status = 'stressed';
        }
        
        return [
            'value' => $hySpread,
            'color' => $color,
            'status' => $status
        ];
    }
    
    /**
     * 分析收益率曲线
     */
    private function analyzeYieldCurve($data) {
        $current = end($data);
        $yieldSpread = $current['t10y3m_bp'] ?? 0;
        
        // 收益率曲线分析
        if ($yieldSpread > 50) {
            $color = 'success';
            $status = 'normal';
        } elseif ($yieldSpread > -50) {
            $color = 'warning';
            $status = 'flattening';
        } else {
            $color = 'danger';
            $status = 'inverted';
        }
        
        return [
            'value' => $yieldSpread,
            'color' => $color,
            'status' => $status
        ];
    }
    
    /**
     * 分析先行经济指标
     */
    private function analyzeLEI($data) {
        $current = end($data);
        $leiYoy = $current['lei_yoy'] ?? 0;
        
        // LEI同比变化阈值
        if ($leiYoy > 0) {
            $color = 'success';
            $status = 'positive';
        } elseif ($leiYoy > -3) {
            $color = 'warning';
            $status = 'declining';
        } else {
            $color = 'danger';
            $status = 'deteriorating';
        }
        
        return [
            'value' => $leiYoy,
            'color' => $color,
            'status' => $status
        ];
    }
    
    /**
     * 分析消费指标
     */
    private function analyzeConsumption($data) {
        $current = end($data);
        $consumptionYoy = $current['consumption_yoy'] ?? 0;
        
        // 消费同比变化阈值
        if ($consumptionYoy > 1) {
            $color = 'success';
            $status = 'strong';
        } elseif ($consumptionYoy > 0) {
            $color = 'warning';
            $status = 'moderate';
        } else {
            $color = 'danger';
            $status = 'contracting';
        }
        
        return [
            'value' => $consumptionYoy,
            'color' => $color,
            'status' => $status
        ];
    }
    
    /**
     * 加载本地CSV数据作为备用
     */
    private function loadLocalData() {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        
        $data = [];
        $handle = fopen($this->dataFile, 'r');
        
        if ($handle) {
            $headers = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                $data[] = array_combine($headers, $row);
            }
            
            fclose($handle);
        }
        
        return $data;
    }
    
    /**
     * 处理历史数据用于图表
     */
    private function processHistoricalData($data) {
        $processed = [];
        
        foreach ($data as $row) {
            $processed[] = [
                'date' => $row['date'] ?? '',
                'risk_score' => $this->calculateRowRiskScore($row),
                'unemployment' => (float)($row['unrate'] ?? 0),
                'pmi' => (float)($row['pmi'] ?? 0),
                'credit_spread' => (float)($row['hy_oas_bp'] ?? 0),
                'yield_curve' => (float)($row['t10y3m_bp'] ?? 0),
                'lei' => (float)($row['lei_yoy'] ?? 0),
                'consumption' => (float)($row['consumption_yoy'] ?? 0)
            ];
        }
        
        return $processed;
    }
    
    /**
     * 计算单行数据的风险得分
     */
    private function calculateRowRiskScore($row) {
        // 这里使用简化的风险计算逻辑
        $scores = [];
        
        // 失业率得分 (Sahm规则简化版)
        $unrate = (float)($row['unrate'] ?? 0);
        $scores['unemployment'] = $unrate > 5 ? 0 : ($unrate > 4 ? 1 : 2);
        
        // PMI得分
        $pmi = (float)($row['pmi'] ?? 0);
        $scores['pmi'] = $pmi < -2 ? 0 : ($pmi < 0 ? 1 : 2);
        
        // 信用利差得分
        $credit = (float)($row['hy_oas_bp'] ?? 0);
        $scores['credit'] = $credit > 500 ? 0 : ($credit > 300 ? 1 : 2);
        
        // 收益率曲线得分
        $yield = (float)($row['t10y3m_bp'] ?? 0);
        $scores['yield'] = $yield < -50 ? 0 : ($yield < 50 ? 1 : 2);
        
        // 计算加权平均 (简化权重)
        $totalScore = ($scores['unemployment'] * 0.3 + 
                      $scores['pmi'] * 0.25 + 
                      $scores['credit'] * 0.25 + 
                      $scores['yield'] * 0.2) / 2; // 除以2转换为0-1范围
        
        return round($totalScore, 3);
    }
}
?>