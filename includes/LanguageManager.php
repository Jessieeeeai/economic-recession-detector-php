<?php
/**
 * 多语言管理器类
 * Multi-language Manager Class
 */

class LanguageManager {
    private $language;
    private $translations;
    
    public function __construct($language = 'zh') {
        $this->language = $language;
        $this->loadTranslations();
    }
    
    /**
     * 获取翻译文本
     */
    public function get($key, $default = null) {
        return $this->translations[$this->language][$key] ?? $default ?? $key;
    }
    
    /**
     * 设置语言
     */
    public function setLanguage($language) {
        $this->language = $language;
        $this->loadTranslations();
    }
    
    /**
     * 获取当前语言
     */
    public function getLanguage() {
        return $this->language;
    }
    
    /**
     * 加载翻译字典
     */
    private function loadTranslations() {
        $this->translations = [
            'zh' => [
                // 页面基础
                'page_title' => '亮子财经·经济衰退检测仪',
                'brand_name' => '亮子财经',
                'app_title' => '经济衰退检测仪',
                'subtitle' => '基于FRED实时数据的智能经济分析系统',
                'language_selector' => '语言 / Language',
                'refresh_data' => '刷新实时数据',
                'last_updated' => '最后更新',
                'data_source_fred' => '数据来源: 美联储经济数据库(FRED)',
                
                // 风险等级
                'current_risk_level' => '当前风险等级',
                'low_risk' => '低风险',
                'medium_risk' => '中等风险', 
                'high_risk' => '高风险',
                'healthy_economy' => '经济健康',
                'needs_attention' => '需要关注',
                'recession_possible' => '衰退可能',
                
                // 核心指标
                'key_indicators' => '核心指标概览',
                'unemployment_rate' => '失业率',
                'pmi_indicator' => 'PMI制造业指数',
                'credit_spread' => '信用利差',
                'yield_curve' => '收益率曲线',
                'lei_indicator' => '先行经济指标',
                'consumption_growth' => '消费增长',
                
                // 图表标题
                'main_chart_title' => '美国经济衰退风险指数 (基于多维度指标)',
                'indicators_chart_title' => '六大核心指标风险热力图',
                'detailed_results' => '详细分析结果',
                'risk_timeline' => '风险时间线',
                'historical_analysis' => '历史分析',
                
                // 数据表格
                'date' => '日期',
                'risk_score' => '风险得分',
                'overall_risk' => '综合风险',
                'unemployment' => '失业率',
                'manufacturing' => '制造业',
                'credit_conditions' => '信用状况',
                'yield_spread' => '利率利差',
                'leading_indicators' => '先行指标',
                'consumer_spending' => '消费支出',
                
                // 状态描述
                'normal' => '正常',
                'caution' => '注意',
                'warning' => '警告',
                'critical' => '严重',
                'expanding' => '扩张',
                'slowing' => '放缓',
                'contracting' => '收缩',
                'healthy' => '健康',
                'elevated' => '升高',
                'stressed' => '紧张',
                'flattening' => '平坦化',
                'inverted' => '倒挂',
                'positive' => '正面',
                'declining' => '下降',
                'deteriorating' => '恶化',
                'strong' => '强劲',
                'moderate' => '温和',
                
                // 说明文档
                'indicator_explanations' => '核心经济指标详细解释',
                'risk_calculation' => '风险得分计算方法',
                'data_coverage' => '数据覆盖1995年至今30年历史',
                'daily_update' => '数据每日自动更新',
                'sahm_rule' => 'Sahm规则',
                'sahm_triggered' => 'Sahm规则已触发',
                
                // 错误信息
                'no_data' => '暂无数据',
                'insufficient_data' => '数据不足',
                'data_error' => '数据获取错误',
                'api_error' => 'API连接失败',
                
                // 操作按钮
                'view_details' => '查看详情',
                'export_data' => '导出数据',
                'print_report' => '打印报告',
                'share_analysis' => '分享分析',
                
                // 时间范围
                'last_month' => '最近1个月',
                'last_3months' => '最近3个月',
                'last_6months' => '最近6个月',
                'last_year' => '最近1年',
                'last_3years' => '最近3年',
                'last_5years' => '最近5年',
                'all_data' => '全部数据',
                
                // 图例和提示
                'click_for_details' => '点击查看详情',
                'hover_for_info' => '悬停查看信息',
                'data_loading' => '数据加载中...',
                'chart_loading' => '图表加载中...'
            ],
            
            'en' => [
                // 页面基础
                'page_title' => 'Giant Cutie·Economic Recession Detector',
                'brand_name' => 'Giant Cutie',
                'app_title' => 'Economic Recession Detector',
                'subtitle' => 'Intelligent Economic Analysis System Based on Real-time FRED Data',
                'language_selector' => 'Language / 语言',
                'refresh_data' => 'Refresh Real-time Data',
                'last_updated' => 'Last Updated',
                'data_source_fred' => 'Data Source: Federal Reserve Economic Data (FRED)',
                
                // 风险等级
                'current_risk_level' => 'Current Risk Level',
                'low_risk' => 'Low Risk',
                'medium_risk' => 'Medium Risk',
                'high_risk' => 'High Risk',
                'healthy_economy' => 'Healthy Economy',
                'needs_attention' => 'Needs Attention',
                'recession_possible' => 'Recession Possible',
                
                // 核心指标
                'key_indicators' => 'Key Indicators Overview',
                'unemployment_rate' => 'Unemployment Rate',
                'pmi_indicator' => 'PMI Manufacturing Index',
                'credit_spread' => 'Credit Spread',
                'yield_curve' => 'Yield Curve',
                'lei_indicator' => 'Leading Economic Indicators',
                'consumption_growth' => 'Consumption Growth',
                
                // 图表标题
                'main_chart_title' => 'US Economic Recession Risk Index (Multi-dimensional Indicators)',
                'indicators_chart_title' => 'Six Core Indicators Risk Heatmap',
                'detailed_results' => 'Detailed Analysis Results',
                'risk_timeline' => 'Risk Timeline',
                'historical_analysis' => 'Historical Analysis',
                
                // 数据表格
                'date' => 'Date',
                'risk_score' => 'Risk Score',
                'overall_risk' => 'Overall Risk',
                'unemployment' => 'Unemployment',
                'manufacturing' => 'Manufacturing',
                'credit_conditions' => 'Credit Conditions',
                'yield_spread' => 'Yield Spread',
                'leading_indicators' => 'Leading Indicators',
                'consumer_spending' => 'Consumer Spending',
                
                // 状态描述
                'normal' => 'Normal',
                'caution' => 'Caution',
                'warning' => 'Warning',
                'critical' => 'Critical',
                'expanding' => 'Expanding',
                'slowing' => 'Slowing',
                'contracting' => 'Contracting',
                'healthy' => 'Healthy',
                'elevated' => 'Elevated',
                'stressed' => 'Stressed',
                'flattening' => 'Flattening',
                'inverted' => 'Inverted',
                'positive' => 'Positive',
                'declining' => 'Declining',
                'deteriorating' => 'Deteriorating',
                'strong' => 'Strong',
                'moderate' => 'Moderate',
                
                // 说明文档
                'indicator_explanations' => 'Detailed Economic Indicators Explanation',
                'risk_calculation' => 'Risk Score Calculation Method',
                'data_coverage' => 'Data covers 30-year history from 1995 to present',
                'daily_update' => 'Data automatically updated daily',
                'sahm_rule' => 'Sahm Rule',
                'sahm_triggered' => 'Sahm Rule Triggered',
                
                // 错误信息
                'no_data' => 'No Data Available',
                'insufficient_data' => 'Insufficient Data',
                'data_error' => 'Data Fetch Error',
                'api_error' => 'API Connection Failed',
                
                // 操作按钮
                'view_details' => 'View Details',
                'export_data' => 'Export Data',
                'print_report' => 'Print Report',
                'share_analysis' => 'Share Analysis',
                
                // 时间范围
                'last_month' => 'Last Month',
                'last_3months' => 'Last 3 Months',
                'last_6months' => 'Last 6 Months',
                'last_year' => 'Last Year',
                'last_3years' => 'Last 3 Years',
                'last_5years' => 'Last 5 Years',
                'all_data' => 'All Data',
                
                // 图例和提示
                'click_for_details' => 'Click for Details',
                'hover_for_info' => 'Hover for Information',
                'data_loading' => 'Loading Data...',
                'chart_loading' => 'Loading Chart...'
            ]
        ];
    }
    
    /**
     * 获取所有可用语言
     */
    public function getAvailableLanguages() {
        return [
            'zh' => '中文',
            'en' => 'English'
        ];
    }
    
    /**
     * 格式化日期
     */
    public function formatDate($date, $format = null) {
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        
        if ($this->language === 'zh') {
            return $format ? date($format, $timestamp) : date('Y年n月j日', $timestamp);
        } else {
            return $format ? date($format, $timestamp) : date('M j, Y', $timestamp);
        }
    }
    
    /**
     * 格式化数字
     */
    public function formatNumber($number, $decimals = 2) {
        if ($this->language === 'zh') {
            return number_format($number, $decimals, '.', ',');
        } else {
            return number_format($number, $decimals, '.', ',');
        }
    }
    
    /**
     * 格式化百分比
     */
    public function formatPercent($number, $decimals = 1) {
        return $this->formatNumber($number, $decimals) . '%';
    }
    
    /**
     * 获取风险等级描述
     */
    public function getRiskDescription($score) {
        if ($score > 0.6) {
            return [
                'level' => $this->get('low_risk'),
                'description' => $this->get('healthy_economy'),
                'color' => 'success'
            ];
        } elseif ($score > 0.3) {
            return [
                'level' => $this->get('medium_risk'),
                'description' => $this->get('needs_attention'),
                'color' => 'warning'
            ];
        } else {
            return [
                'level' => $this->get('high_risk'),
                'description' => $this->get('recession_possible'),
                'color' => 'danger'
            ];
        }
    }
}
?>