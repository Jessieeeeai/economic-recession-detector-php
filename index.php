<?php
/**
 * 亮子财经·经济衰退检测仪 PHP版本
 * Economic Recession Detector - PHP Version
 * 
 * 基于FRED实时数据的智能经济分析系统
 * Intelligent Economic Analysis System Based on Real-time FRED Data
 */

session_start();

// 包含核心类文件
require_once 'includes/EconomicAnalyzer.php';
require_once 'includes/FREDDataFetcher.php';
require_once 'includes/LanguageManager.php';
require_once 'includes/RiskCalculator.php';

// 初始化语言设置
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'zh';
}

// 处理语言切换
if (isset($_POST['language'])) {
    $_SESSION['language'] = $_POST['language'];
}

// 初始化语言管理器
$lang = new LanguageManager($_SESSION['language']);

// 初始化数据分析器
$analyzer = new EconomicAnalyzer();
$riskCalculator = new RiskCalculator();

// 获取当前经济数据
try {
    $currentData = $analyzer->getCurrentData();
    $riskScore = $riskCalculator->calculateOverallRisk($currentData);
    $riskLevel = $riskCalculator->getRiskLevel($riskScore);
} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang->get('page_title'); ?></title>
    
    <!-- CSS样式 -->
    <link href="assets/css/main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>
<body class="bg-dark text-light">
    <!-- 导航栏 -->
    <nav class="navbar navbar-dark bg-black border-bottom border-warning">
        <div class="container-fluid">
            <div class="navbar-brand">
                <i class="fas fa-chart-line text-warning me-2"></i>
                <strong><?php echo $lang->get('brand_name'); ?></strong>
                <span class="text-warning">·<?php echo $lang->get('app_title'); ?></span>
            </div>
            
            <!-- 语言切换器 -->
            <div class="d-flex align-items-center">
                <form method="POST" class="d-flex align-items-center me-3">
                    <label class="me-2 small text-muted">
                        <i class="fas fa-globe"></i> <?php echo $lang->get('language_selector'); ?>
                    </label>
                    <select name="language" class="form-select form-select-sm bg-dark text-light border-warning" 
                            onchange="this.form.submit()" style="width: auto;">
                        <option value="zh" <?php echo $_SESSION['language'] === 'zh' ? 'selected' : ''; ?>>中文</option>
                        <option value="en" <?php echo $_SESSION['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                    </select>
                </form>
                
                <!-- 刷新按钮 -->
                <button class="btn btn-outline-warning btn-sm" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> <?php echo $lang->get('refresh_data'); ?>
                </button>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <div class="container-fluid py-4">
        <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <!-- 标题和描述 -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 text-center mb-3">
                    <span class="text-warning"><?php echo $lang->get('app_title'); ?></span>
                </h1>
                <p class="text-center text-muted lead">
                    <?php echo $lang->get('subtitle'); ?>
                </p>
            </div>
        </div>

        <!-- 风险评级卡片 -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-black border-warning h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-tachometer-alt"></i> <?php echo $lang->get('current_risk_level'); ?>
                        </h5>
                        <?php if (isset($riskLevel)): ?>
                        <div class="risk-display mb-3">
                            <div class="risk-score display-4 mb-2 
                                <?php echo $riskLevel['color'] === 'green' ? 'text-success' : 
                                         ($riskLevel['color'] === 'yellow' ? 'text-warning' : 'text-danger'); ?>">
                                <?php echo number_format($riskScore * 100, 1); ?>%
                            </div>
                            <div class="risk-level h5">
                                <?php echo $riskLevel['icon'] . ' ' . $riskLevel['label']; ?>
                            </div>
                            <small class="text-muted"><?php echo $riskLevel['description']; ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 核心指标摘要 -->
            <div class="col-md-8">
                <div class="card bg-black border-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-chart-bar"></i> <?php echo $lang->get('key_indicators'); ?>
                        </h5>
                        <div class="row">
                            <?php if (isset($currentData)): ?>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="indicator-item">
                                    <div class="indicator-label small text-muted"><?php echo $lang->get('unemployment_rate'); ?></div>
                                    <div class="indicator-value h6 text-<?php echo $currentData['unemployment']['color']; ?>">
                                        <?php echo number_format($currentData['unemployment']['value'], 1); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="indicator-item">
                                    <div class="indicator-label small text-muted"><?php echo $lang->get('pmi_indicator'); ?></div>
                                    <div class="indicator-value h6 text-<?php echo $currentData['pmi']['color']; ?>">
                                        <?php echo number_format($currentData['pmi']['value'], 1); ?>%
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="indicator-item">
                                    <div class="indicator-label small text-muted"><?php echo $lang->get('credit_spread'); ?></div>
                                    <div class="indicator-value h6 text-<?php echo $currentData['credit']['color']; ?>">
                                        <?php echo number_format($currentData['credit']['value'], 0); ?>bp
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3 mb-3">
                                <div class="indicator-item">
                                    <div class="indicator-label small text-muted"><?php echo $lang->get('yield_curve'); ?></div>
                                    <div class="indicator-value h6 text-<?php echo $currentData['yield']['color']; ?>">
                                        <?php echo number_format($currentData['yield']['value'], 0); ?>bp
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 图表区域 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-black border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-chart-line"></i> <?php echo $lang->get('main_chart_title'); ?>
                        </h5>
                        <canvas id="riskChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 指标热力图 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-black border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-th"></i> <?php echo $lang->get('indicators_chart_title'); ?>
                        </h5>
                        <canvas id="heatmapChart" height="60"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 详细数据表格 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-black border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="fas fa-table"></i> <?php echo $lang->get('detailed_results'); ?>
                        </h5>
                        <div id="dataTable" class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <!-- 数据表格将通过JavaScript加载 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-black border-top border-warning py-3 mt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        © 2024 <?php echo $lang->get('brand_name'); ?> - <?php echo $lang->get('data_source_fred'); ?>
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> <?php echo $lang->get('last_updated'); ?>: 
                        <span id="lastUpdated"><?php echo date('Y-m-d H:i:s'); ?></span>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // 初始化页面数据
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            loadDataTable();
        });
        
        // 刷新数据函数
        function refreshData() {
            location.reload();
        }
    </script>
</body>
</html>