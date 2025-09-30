<?php
/**
 * 风险计算器类
 * Risk Calculator Class
 */

class RiskCalculator {
    
    // 指标权重配置
    private $weights = [
        'unemployment' => 0.25,    // 失业率 25%
        'manufacturing' => 0.25,   // 制造业 25%  
        'consumption' => 0.20,     // 消费 20%
        'credit' => 0.15,          // 信用条件 15%
        'yield_curve' => 0.10,     // 收益率曲线 10%
        'leading' => 0.05          // 先行指标 5%
    ];
    
    // 风险阈值配置
    private $thresholds = [
        'unemployment' => [
            'sahm_threshold' => 0.5    // Sahm规则阈值
        ],
        'pmi' => [
            'red_max' => -2,          // 红色阈值
            'yellow_min' => -2,       // 黄色最小值
            'yellow_max' => 0         // 黄色最大值
        ],
        'credit' => [
            'red_min' => 500,         // 红色最小值(基点)
            'yellow_min' => 300,      // 黄色最小值
            'yellow_max' => 500       // 黄色最大值
        ],
        'yield_curve' => [
            'red_max' => -50,         // 红色最大值(基点)
            'yellow_min' => -50,      // 黄色最小值
            'yellow_max' => 50        // 黄色最大值
        ],
        'lei' => [
            'red_max' => -3,          // 红色最大值(%)
            'yellow_min' => -3,       // 黄色最小值
            'yellow_max' => 0         // 黄色最大值
        ],
        'consumption' => [
            'red_max' => 0,           // 红色最大值(%)
            'yellow_min' => 0,        // 黄色最小值
            'yellow_max' => 1         // 黄色最大值
        ]
    ];
    
    /**
     * 计算综合风险得分
     */
    public function calculateOverallRisk($data) {
        $scores = [];
        
        // 计算各指标得分
        $scores['unemployment'] = $this->calculateUnemploymentScore($data['unemployment'] ?? []);
        $scores['manufacturing'] = $this->calculateManufacturingScore($data['pmi'] ?? []);
        $scores['consumption'] = $this->calculateConsumptionScore($data['consumption'] ?? []);
        $scores['credit'] = $this->calculateCreditScore($data['credit'] ?? []);
        $scores['yield_curve'] = $this->calculateYieldScore($data['yield'] ?? []);
        $scores['leading'] = $this->calculateLeadingScore($data['lei'] ?? []);
        
        // 计算加权平均得分
        $totalScore = 0;
        foreach ($scores as $indicator => $score) {
            $totalScore += $score * $this->weights[$indicator];
        }
        
        return round($totalScore, 3);
    }
    
    /**
     * 根据得分获取风险等级
     */
    public function getRiskLevel($score) {
        if ($score > 0.6) {
            return [
                'level' => 'low',
                'label' => '低风险',
                'description' => '经济健康',
                'color' => 'success',
                'icon' => '🟢'
            ];
        } elseif ($score > 0.3) {
            return [
                'level' => 'medium',
                'label' => '中等风险',
                'description' => '需要关注',
                'color' => 'warning',
                'icon' => '🟡'
            ];
        } else {
            return [
                'level' => 'high',
                'label' => '高风险',
                'description' => '衰退可能',
                'color' => 'danger',
                'icon' => '🔴'
            ];
        }
    }
    
    /**
     * 计算失业率得分 (基于Sahm规则)
     */
    private function calculateUnemploymentScore($unemploymentData) {
        if (empty($unemploymentData) || !isset($unemploymentData['status'])) {
            return 1; // 默认中性得分
        }
        
        switch ($unemploymentData['status']) {
            case 'sahm_triggered':
                return 0; // 红色
            case 'normal':
                return 2; // 绿色
            default:
                return 1; // 黄色
        }
    }
    
    /**
     * 计算制造业得分
     */
    private function calculateManufacturingScore($pmiData) {
        if (empty($pmiData) || !isset($pmiData['value'])) {
            return 1;
        }
        
        $value = $pmiData['value'];
        
        if ($value <= $this->thresholds['pmi']['red_max']) {
            return 0; // 红色
        } elseif ($value <= $this->thresholds['pmi']['yellow_max']) {
            return 1; // 黄色
        } else {
            return 2; // 绿色
        }
    }
    
    /**
     * 计算消费得分
     */
    private function calculateConsumptionScore($consumptionData) {
        if (empty($consumptionData) || !isset($consumptionData['value'])) {
            return 1;
        }
        
        $value = $consumptionData['value'];
        
        if ($value <= $this->thresholds['consumption']['red_max']) {
            return 0; // 红色
        } elseif ($value <= $this->thresholds['consumption']['yellow_max']) {
            return 1; // 黄色
        } else {
            return 2; // 绿色
        }
    }
    
    /**
     * 计算信用得分
     */
    private function calculateCreditScore($creditData) {
        if (empty($creditData) || !isset($creditData['value'])) {
            return 1;
        }
        
        $value = $creditData['value'];
        
        if ($value >= $this->thresholds['credit']['red_min']) {
            return 0; // 红色
        } elseif ($value >= $this->thresholds['credit']['yellow_min']) {
            return 1; // 黄色
        } else {
            return 2; // 绿色
        }
    }
    
    /**
     * 计算收益率曲线得分
     */
    private function calculateYieldScore($yieldData) {
        if (empty($yieldData) || !isset($yieldData['value'])) {
            return 1;
        }
        
        $value = $yieldData['value'];
        
        if ($value <= $this->thresholds['yield_curve']['red_max']) {
            return 0; // 红色 (倒挂)
        } elseif ($value <= $this->thresholds['yield_curve']['yellow_max']) {
            return 1; // 黄色 (平坦)
        } else {
            return 2; // 绿色 (正常)
        }
    }
    
    /**
     * 计算先行指标得分
     */
    private function calculateLeadingScore($leiData) {
        if (empty($leiData) || !isset($leiData['value'])) {
            return 1;
        }
        
        $value = $leiData['value'];
        
        if ($value <= $this->thresholds['lei']['red_max']) {
            return 0; // 红色
        } elseif ($value <= $this->thresholds['lei']['yellow_max']) {
            return 1; // 黄色
        } else {
            return 2; // 绿色
        }
    }
    
    /**
     * 获取详细的风险分析
     */
    public function getDetailedAnalysis($data) {
        $analysis = [
            'overall_score' => $this->calculateOverallRisk($data),
            'indicators' => []
        ];
        
        // 分析各个指标
        foreach ($data as $key => $indicatorData) {
            $analysis['indicators'][$key] = [
                'value' => $indicatorData['value'] ?? 0,
                'status' => $indicatorData['status'] ?? 'unknown',
                'color' => $indicatorData['color'] ?? 'secondary',
                'score' => $this->getIndicatorScore($key, $indicatorData),
                'weight' => $this->getIndicatorWeight($key)
            ];
        }
        
        // 获取风险等级
        $analysis['risk_level'] = $this->getRiskLevel($analysis['overall_score']);
        
        return $analysis;
    }
    
    /**
     * 获取单个指标得分
     */
    private function getIndicatorScore($indicator, $data) {
        switch ($indicator) {
            case 'unemployment':
                return $this->calculateUnemploymentScore($data);
            case 'pmi':
                return $this->calculateManufacturingScore($data);
            case 'consumption':
                return $this->calculateConsumptionScore($data);
            case 'credit':
                return $this->calculateCreditScore($data);
            case 'yield':
                return $this->calculateYieldScore($data);
            case 'lei':
                return $this->calculateLeadingScore($data);
            default:
                return 1;
        }
    }
    
    /**
     * 获取指标权重
     */
    private function getIndicatorWeight($indicator) {
        $mapping = [
            'unemployment' => 'unemployment',
            'pmi' => 'manufacturing',
            'consumption' => 'consumption',
            'credit' => 'credit',
            'yield' => 'yield_curve',
            'lei' => 'leading'
        ];
        
        return $this->weights[$mapping[$indicator]] ?? 0;
    }
    
    /**
     * 计算历史风险趋势
     */
    public function calculateHistoricalTrend($historicalData) {
        $trend = [];
        
        foreach ($historicalData as $dataPoint) {
            $riskScore = $this->calculateRowRiskScore($dataPoint);
            $trend[] = [
                'date' => $dataPoint['date'],
                'risk_score' => $riskScore,
                'risk_level' => $this->getRiskLevel($riskScore)
            ];
        }
        
        return $trend;
    }
    
    /**
     * 计算单行数据的风险得分
     */
    private function calculateRowRiskScore($row) {
        $scores = [];
        
        // 简化的历史数据风险计算
        
        // 失业率得分
        $unrate = (float)($row['unemployment'] ?? 0);
        $scores['unemployment'] = $unrate > 6 ? 0 : ($unrate > 4.5 ? 1 : 2);
        
        // PMI得分
        $pmi = (float)($row['pmi'] ?? 0);
        $scores['manufacturing'] = $pmi < -2 ? 0 : ($pmi < 0 ? 1 : 2);
        
        // 信用利差得分
        $credit = (float)($row['credit_spread'] ?? 0);
        $scores['credit'] = $credit > 500 ? 0 : ($credit > 300 ? 1 : 2);
        
        // 收益率曲线得分
        $yield = (float)($row['yield_curve'] ?? 0);
        $scores['yield_curve'] = $yield < -50 ? 0 : ($yield < 50 ? 1 : 2);
        
        // LEI得分
        $lei = (float)($row['lei'] ?? 0);
        $scores['leading'] = $lei < -3 ? 0 : ($lei < 0 ? 1 : 2);
        
        // 消费得分
        $consumption = (float)($row['consumption'] ?? 0);
        $scores['consumption'] = $consumption < 0 ? 0 : ($consumption < 1 ? 1 : 2);
        
        // 计算加权平均
        $totalScore = 0;
        foreach ($scores as $indicator => $score) {
            $totalScore += $score * ($this->weights[$indicator] ?? 0);
        }
        
        return round($totalScore / 2, 3); // 转换为0-1范围
    }
}
?>