/**
 * 亮子财经·经济衰退检测仪 - 主要JavaScript功能
 * Economic Recession Detector - Main JavaScript Functions
 */

// 全局变量
let riskChart = null;
let heatmapChart = null;
let currentData = null;

// 初始化图表
function initializeCharts() {
    // 显示加载状态
    showLoadingState();
    
    // 获取数据并初始化图表
    fetchChartData().then(data => {
        currentData = data;
        initializeRiskChart(data.historical);
        initializeHeatmapChart(data.current);
        hideLoadingState();
    }).catch(error => {
        console.error('Failed to load chart data:', error);
        showErrorState('图表数据加载失败');
    });
}

// 获取图表数据
async function fetchChartData() {
    try {
        const response = await fetch('api/chart-data.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching chart data:', error);
        throw error;
    }
}

// 初始化风险趋势图表
function initializeRiskChart(historicalData) {
    const ctx = document.getElementById('riskChart');
    if (!ctx) return;
    
    // 准备数据
    const labels = historicalData.map(item => item.date);
    const riskScores = historicalData.map(item => item.risk_score * 100);
    
    // 创建渐变背景
    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(255, 193, 7, 0.3)');
    gradient.addColorStop(1, 'rgba(255, 193, 7, 0.05)');
    
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '风险得分 (%)',
                data: riskScores,
                borderColor: '#ffc107',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffc107',
                pointBorderColor: '#000',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#fff',
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#111',
                    titleColor: '#ffc107',
                    bodyColor: '#fff',
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return `风险得分: ${context.parsed.y.toFixed(1)}%`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        parser: 'YYYY-MM-DD',
                        tooltipFormat: 'YYYY年MM月',
                        displayFormats: {
                            month: 'YYYY-MM'
                        }
                    },
                    grid: {
                        color: 'rgba(255, 193, 7, 0.1)'
                    },
                    ticks: {
                        color: '#fff',
                        maxTicksLimit: 12
                    }
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: {
                        color: 'rgba(255, 193, 7, 0.1)'
                    },
                    ticks: {
                        color: '#fff',
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    };
    
    // 销毁现有图表
    if (riskChart) {
        riskChart.destroy();
    }
    
    riskChart = new Chart(ctx, config);
}

// 初始化指标热力图
function initializeHeatmapChart(currentIndicators) {
    const ctx = document.getElementById('heatmapChart');
    if (!ctx) return;
    
    // 准备数据
    const indicators = [
        { name: '失业率', value: currentIndicators.unemployment?.value || 0, status: currentIndicators.unemployment?.status || 'unknown' },
        { name: 'PMI制造业', value: currentIndicators.pmi?.value || 0, status: currentIndicators.pmi?.status || 'unknown' },
        { name: '信用利差', value: currentIndicators.credit?.value || 0, status: currentIndicators.credit?.status || 'unknown' },
        { name: '收益率曲线', value: currentIndicators.yield?.value || 0, status: currentIndicators.yield?.status || 'unknown' },
        { name: '先行指标', value: currentIndicators.lei?.value || 0, status: currentIndicators.lei?.status || 'unknown' },
        { name: '消费增长', value: currentIndicators.consumption?.value || 0, status: currentIndicators.consumption?.status || 'unknown' }
    ];
    
    const labels = indicators.map(item => item.name);
    const values = indicators.map(item => item.value);
    const colors = indicators.map(item => getStatusColor(item.status));
    
    const config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '当前值',
                data: values,
                backgroundColor: colors,
                borderColor: colors.map(color => color.replace('0.7)', '1)')),
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#111',
                    titleColor: '#ffc107',
                    bodyColor: '#fff',
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const indicator = indicators[context.dataIndex];
                            return `${indicator.name}: ${indicator.value.toFixed(2)} (${getStatusText(indicator.status)})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 193, 7, 0.1)'
                    },
                    ticks: {
                        color: '#fff'
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#fff'
                    }
                }
            }
        }
    };
    
    // 销毁现有图表
    if (heatmapChart) {
        heatmapChart.destroy();
    }
    
    heatmapChart = new Chart(ctx, config);
}

// 加载数据表格
function loadDataTable() {
    fetch('api/table-data.php')
        .then(response => response.json())
        .then(data => {
            renderDataTable(data);
        })
        .catch(error => {
            console.error('Error loading table data:', error);
            showTableError('数据表格加载失败');
        });
}

// 渲染数据表格
function renderDataTable(data) {
    const container = document.getElementById('dataTable');
    if (!container || !data || data.length === 0) {
        showTableError('暂无数据');
        return;
    }
    
    // 创建表格HTML
    let tableHtml = `
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>日期</th>
                    <th>风险得分</th>
                    <th>综合风险</th>
                    <th>失业率</th>
                    <th>制造业PMI</th>
                    <th>信用利差</th>
                    <th>收益率曲线</th>
                    <th>先行指标</th>
                    <th>消费增长</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    // 添加数据行
    data.slice(0, 100).forEach(row => { // 限制显示最近100行
        const riskLevel = getRiskLevelFromScore(row.risk_score);
        tableHtml += `
            <tr>
                <td>${formatDate(row.date)}</td>
                <td><strong>${(row.risk_score * 100).toFixed(1)}%</strong></td>
                <td><span class="risk-badge ${riskLevel.level}">${riskLevel.label}</span></td>
                <td>${formatIndicatorValue(row.unemployment, '%')}</td>
                <td>${formatIndicatorValue(row.pmi, '%')}</td>
                <td>${formatIndicatorValue(row.credit_spread, 'bp')}</td>
                <td>${formatIndicatorValue(row.yield_curve, 'bp')}</td>
                <td>${formatIndicatorValue(row.lei, '%')}</td>
                <td>${formatIndicatorValue(row.consumption, '%')}</td>
            </tr>
        `;
    });
    
    tableHtml += `
            </tbody>
        </table>
    `;
    
    container.innerHTML = tableHtml;
}

// 工具函数

// 获取状态颜色
function getStatusColor(status) {
    const colorMap = {
        'normal': 'rgba(25, 135, 84, 0.7)',
        'expanding': 'rgba(25, 135, 84, 0.7)',
        'healthy': 'rgba(25, 135, 84, 0.7)',
        'positive': 'rgba(25, 135, 84, 0.7)',
        'strong': 'rgba(25, 135, 84, 0.7)',
        
        'caution': 'rgba(255, 193, 7, 0.7)',
        'slowing': 'rgba(255, 193, 7, 0.7)',
        'elevated': 'rgba(255, 193, 7, 0.7)',
        'flattening': 'rgba(255, 193, 7, 0.7)',
        'declining': 'rgba(255, 193, 7, 0.7)',
        'moderate': 'rgba(255, 193, 7, 0.7)',
        
        'sahm_triggered': 'rgba(220, 53, 69, 0.7)',
        'contracting': 'rgba(220, 53, 69, 0.7)',
        'stressed': 'rgba(220, 53, 69, 0.7)',
        'inverted': 'rgba(220, 53, 69, 0.7)',
        'deteriorating': 'rgba(220, 53, 69, 0.7)'
    };
    
    return colorMap[status] || 'rgba(108, 117, 125, 0.7)';
}

// 获取状态文本
function getStatusText(status) {
    const textMap = {
        'normal': '正常',
        'expanding': '扩张',
        'healthy': '健康',
        'positive': '正面',
        'strong': '强劲',
        
        'caution': '注意',
        'slowing': '放缓',
        'elevated': '升高',
        'flattening': '平坦化',
        'declining': '下降',
        'moderate': '温和',
        
        'sahm_triggered': 'Sahm规则触发',
        'contracting': '收缩',
        'stressed': '紧张',
        'inverted': '倒挂',
        'deteriorating': '恶化'
    };
    
    return textMap[status] || '未知';
}

// 根据风险得分获取风险等级
function getRiskLevelFromScore(score) {
    if (score > 0.6) {
        return { level: 'low', label: '低风险' };
    } else if (score > 0.3) {
        return { level: 'medium', label: '中等风险' };
    } else {
        return { level: 'high', label: '高风险' };
    }
}

// 格式化日期
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('zh-CN', { 
        year: 'numeric', 
        month: '2-digit',
        day: '2-digit'
    });
}

// 格式化指标值
function formatIndicatorValue(value, unit) {
    if (value === null || value === undefined || isNaN(value)) {
        return '-';
    }
    return `${parseFloat(value).toFixed(1)}${unit}`;
}

// 显示加载状态
function showLoadingState() {
    const containers = ['riskChart', 'heatmapChart'].map(id => 
        document.getElementById(id)?.closest('.card-body')
    ).filter(Boolean);
    
    containers.forEach(container => {
        container.innerHTML = '<div class="data-loading"><span class="loading-spinner"></span>图表加载中...</div>';
    });
}

// 隐藏加载状态
function hideLoadingState() {
    // 加载状态会被图表替换，无需特别处理
}

// 显示错误状态
function showErrorState(message) {
    const containers = ['riskChart', 'heatmapChart'].map(id => 
        document.getElementById(id)?.closest('.card-body')
    ).filter(Boolean);
    
    containers.forEach(container => {
        container.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    });
}

// 显示表格错误
function showTableError(message) {
    const container = document.getElementById('dataTable');
    if (container) {
        container.innerHTML = `<div class="alert alert-danger">${message}</div>`;
    }
}

// 导出数据功能
function exportData() {
    if (!currentData) {
        alert('暂无数据可导出');
        return;
    }
    
    // 创建CSV内容
    const csvContent = generateCSV(currentData.historical);
    
    // 下载文件
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `economic_data_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// 生成CSV内容
function generateCSV(data) {
    const headers = ['日期', '风险得分', '失业率', 'PMI', '信用利差', '收益率曲线', '先行指标', '消费增长'];
    const rows = data.map(row => [
        row.date,
        (row.risk_score * 100).toFixed(2),
        row.unemployment?.toFixed(2) || '',
        row.pmi?.toFixed(2) || '',
        row.credit_spread?.toFixed(0) || '',
        row.yield_curve?.toFixed(0) || '',
        row.lei?.toFixed(2) || '',
        row.consumption?.toFixed(2) || ''
    ]);
    
    return [headers, ...rows].map(row => row.join(',')).join('\n');
}

// 打印报告功能
function printReport() {
    window.print();
}

// 响应式图表调整
window.addEventListener('resize', function() {
    if (riskChart) {
        riskChart.resize();
    }
    if (heatmapChart) {
        heatmapChart.resize();
    }
});

// 页面可见性变化时刷新数据
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // 页面重新可见时，检查是否需要刷新数据
        const lastUpdate = localStorage.getItem('lastDataUpdate');
        const now = Date.now();
        
        // 如果距离上次更新超过5分钟，自动刷新
        if (!lastUpdate || now - parseInt(lastUpdate) > 300000) {
            refreshData();
        }
    }
});

// 刷新数据函数
function refreshData() {
    localStorage.setItem('lastDataUpdate', Date.now().toString());
    initializeCharts();
    loadDataTable();
}