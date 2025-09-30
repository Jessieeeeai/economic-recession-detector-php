# 📊 亮子财经·经济衰退检测仪 - PHP版本

## 🎯 项目概述

这是经济衰退检测仪的**PHP版本**，将原有的Python/Streamlit应用转换为更广泛兼容的PHP Web应用。通过分析13个核心经济指标，实时评估美国经济的衰退风险。

## ✨ 核心特性

### 🌐 跨平台兼容
- **PHP 7.4+** 支持，几乎所有Web服务器兼容
- **无依赖部署** - 纯PHP实现，无需Python环境
- **轻量级设计** - 启动速度快，资源占用低
- **移动端友好** - 响应式设计，完美支持手机访问

### 📊 功能特性
- **实时FRED数据** - 直接集成美联储经济数据库
- **13个核心指标** - 失业率、PMI、信用利差、收益率曲线等
- **智能风险评级** - 基于权重算法的综合风险评估
- **交互式图表** - Chart.js驱动的现代化数据可视化
- **历史数据分析** - 30年经济周期数据回溯
- **多语言支持** - 中英文无缝切换

### 🎨 用户体验
- **黑金主题** - 专业的金融风格界面设计
- **实时刷新** - 支持数据自动更新和手动刷新
- **数据导出** - CSV格式数据导出功能
- **打印报告** - 支持完整经济分析报告打印

## 🚀 快速开始

### 系统要求
```bash
- PHP >= 7.4
- Web服务器 (Apache/Nginx/PHP内置服务器)
- cURL扩展
- JSON扩展
- 可选：Composer (用于依赖管理)
```

### 1. 下载安装
```bash
# 下载项目文件
git clone https://github.com/liangzi/economic-recession-detector-php.git
cd economic-recession-detector-php

# 或直接解压项目包
unzip economic-recession-detector-php.zip
cd economic-recession-detector-php
```

### 2. 环境配置
```bash
# 复制环境变量配置文件
cp config/.env.example .env

# 编辑配置文件，添加FRED API Key (可选)
nano .env
```

### 3. 启动应用

**方法1: PHP内置服务器 (开发)**
```bash
php -S localhost:8000
```

**方法2: Apache/Nginx (生产)**
```bash
# 将文件部署到Web服务器根目录
# 访问: http://your-domain.com
```

**方法3: 使用Composer (推荐)**
```bash
composer install
composer run serve
```

### 4. 访问应用
打开浏览器访问: `http://localhost:8000`

## 📁 项目结构

```
economic-recession-detector-php/
├── index.php                 # 主页面入口
├── api/                      # API接口目录
│   ├── chart-data.php       # 图表数据接口
│   └── table-data.php       # 表格数据接口
├── includes/                 # 核心类文件
│   ├── EconomicAnalyzer.php # 经济数据分析器
│   ├── FREDDataFetcher.php  # FRED数据获取器
│   ├── LanguageManager.php  # 多语言管理器
│   └── RiskCalculator.php   # 风险计算器
├── assets/                   # 静态资源
│   ├── css/main.css         # 主样式文件
│   └── js/main.js           # 主JavaScript文件
├── data/                     # 数据目录
│   ├── indicators_fred.csv  # 历史经济数据
│   └── cache/               # 数据缓存目录
├── config/                   # 配置文件
│   └── .env.example         # 环境变量示例
├── composer.json            # Composer配置
└── README.md               # 项目说明文档
```

## 🔧 配置说明

### 环境变量配置 (.env)
```bash
# FRED API配置 (可选，有本地数据备份)
FRED_API_KEY=your_fred_api_key_here

# 应用配置
APP_DEBUG=true
DEFAULT_LANGUAGE=zh

# 缓存配置
CACHE_ENABLED=true
CACHE_TIMEOUT=3600
```

### FRED API密钥获取 (可选)
1. 访问 [FRED API官网](https://fred.stlouisfed.org/docs/api/api_key.html)
2. 注册账户并申请API密钥
3. 将密钥添加到`.env`文件中
4. **注意**: 即使没有API密钥，应用仍可正常运行(使用本地数据)

## 📊 核心功能

### 🎯 六大指标体系
1. **消费指标** (30%) - 个人消费支出、零售销售
2. **制造业指标** (25%) - PMI制造业就业、工业生产  
3. **就业指标** (20%) - 失业率(Sahm规则)、非农就业
4. **信用指标** (15%) - 高收益债券利差、投资级利差
5. **利率指标** (10%) - 收益率曲线、期限利差
6. **先行指标** (5%) - OECD复合领先指标

### 🔍 风险等级
- 🟢 **低风险** (>60%): 经济健康，可正常投资
- 🟡 **中等风险** (30-60%): 需要关注，谨慎决策  
- 🔴 **高风险** (<30%): 衰退可能，防御策略

### 📈 数据可视化
- **风险趋势图** - 显示历史风险变化轨迹
- **指标热力图** - 各指标当前状态一目了然
- **详细数据表** - 支持滚动浏览完整历史数据

## 🌐 多语言支持

### 中文版 (默认)
- **品牌**: 亮子财经
- **完整中文界面** - 所有功能和说明均为中文

### English Version
- **Brand**: Giant Cutie  
- **Complete English Interface** - All features in English
- **Switch Language** - Use the language selector in top-right

## 🔄 API接口

### 图表数据接口
```bash
GET /api/chart-data.php
返回格式: JSON
包含: 当前指标状态 + 历史风险趋势
```

### 表格数据接口  
```bash
GET /api/table-data.php?months=60
参数: months (12-120)  
返回格式: JSON
包含: 历史详细数据
```

## 🚀 部署指南

### Apache服务器
```apache
# .htaccess配置
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx服务器
```nginx
# nginx.conf配置
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 共享主机部署
1. 上传所有文件到Web根目录
2. 确保`data/cache/`目录可写 (chmod 755)
3. 配置`.env`文件
4. 访问域名即可使用

## 🔍 故障排除

### 常见问题

**Q: 页面显示空白或PHP错误？**
A: 检查PHP版本 >= 7.4，开启error_reporting查看具体错误

**Q: 图表不显示？**  
A: 检查浏览器JavaScript是否开启，网络是否能访问CDN资源

**Q: 数据获取失败？**
A: 检查FRED API密钥配置，或使用本地数据模式

**Q: 样式显示异常？**
A: 检查CSS文件路径，确保静态资源可正常访问

### 调试模式
```bash
# 开启调试模式 (.env文件)
APP_DEBUG=true

# 查看PHP错误日志
tail -f /var/log/php_errors.log
```

## 🔒 安全建议

### 生产环境配置
```bash
# 关闭调试模式
APP_DEBUG=false

# 保护敏感文件
deny from all (.env, composer.json等)

# 开启HTTPS
使用SSL证书，强制HTTPS访问

# 定期更新
及时更新PHP版本和依赖包
```

## 📈 性能优化

### 缓存策略
- **数据缓存**: FRED API响应缓存1小时
- **静态资源**: 设置浏览器缓存头
- **压缩传输**: 开启Gzip压缩

### 服务器优化
```bash
# PHP配置优化
memory_limit = 256M
max_execution_time = 120  
opcache.enable = 1

# Apache/Nginx缓存配置
开启静态文件缓存和压缩
```

## 🤝 开发贡献

### 本地开发
```bash
# 克隆仓库
git clone https://github.com/liangzi/economic-recession-detector-php.git
cd economic-recession-detector-php

# 安装依赖
composer install

# 启动开发服务器
composer run serve

# 运行测试
composer test
```

### 代码规范
```bash
# 检查代码风格
composer run cs-check

# 自动修复代码风格  
composer run cs-fix
```

## 📄 版本历史

### v1.0 (2024-09-24)
- ✅ 完整PHP版本实现
- ✅ 13个核心经济指标分析
- ✅ 实时FRED数据集成
- ✅ 多语言国际化支持
- ✅ 响应式黑金主题界面
- ✅ 交互式图表和数据表格

### 计划功能
- 📧 **邮件预警** - 风险等级变化通知
- 📱 **PWA支持** - 离线访问能力
- 🔍 **高级筛选** - 自定义时间范围和指标
- 📊 **更多图表** - 相关性分析、对比图表

## 📞 技术支持

- **官方网站**: https://liangzi.finance
- **技术文档**: https://docs.liangzi.finance/economic-detector
- **问题反馈**: https://github.com/liangzi/economic-recession-detector-php/issues
- **联系邮箱**: support@liangzi.finance

## 📜 开源协议

本项目采用 [MIT协议](LICENSE) 开源，允许商业使用和修改。

## 🙏 致谢

- **FRED API** - 美联储经济数据库
- **Chart.js** - 优秀的JavaScript图表库  
- **Bootstrap** - 响应式CSS框架
- **PHP社区** - 感谢所有贡献者

---

**免责声明**: 本系统仅供教育和研究目的，不构成投资建议。经济预测存在不确定性，请结合多方面信息进行判断。

**© 2024 亮子财经 - 让经济数据更易懂，让投资决策更理性**