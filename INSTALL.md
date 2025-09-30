# 📦 亮子财经·经济衰退检测仪 PHP版本 - 安装部署指南

## 🎯 快速部署 (5分钟上线)

### 第1步: 下载项目
```bash
# 下载备份包
wget https://page.gensparksite.com/project_backups/tooluse_5XY8VKznSDaB8rbDyk9oTg.tar.gz

# 解压到Web目录
tar -xzf tooluse_5XY8VKznSDaB8rbDyk9oTg.tar.gz
cd home/user/webapp-php
```

### 第2步: 环境配置
```bash
# 复制环境配置文件
cp config/.env.example .env

# 可选: 编辑添加FRED API Key (有本地数据，可跳过)
nano .env
```

### 第3步: 权限设置
```bash
# 设置缓存目录权限
mkdir -p data/cache
chmod 755 data/cache

# 确保Web服务器可读取
chown -R www-data:www-data . # (Apache/Nginx)
# 或
chmod -R 755 . # (共享主机)
```

### 第4步: 启动服务

**方法1: PHP内置服务器 (开发/测试)**
```bash
php -S localhost:8000
# 访问: http://localhost:8000
```

**方法2: Apache服务器 (生产)**
```bash
# 复制文件到Web根目录
cp -r * /var/www/html/recession-detector/
# 访问: http://your-domain.com/recession-detector/
```

**方法3: Nginx服务器 (生产)**
```bash
# 复制文件到Web根目录
cp -r * /usr/share/nginx/html/recession-detector/
# 访问: http://your-domain.com/recession-detector/
```

**方法4: 共享主机 (最简单)**
```bash
# 上传所有文件到public_html目录
# 通过cPanel/FTP上传解压后的文件
# 访问: http://your-domain.com
```

## 🌍 各种环境部署示例

### 🐳 Docker部署
```dockerfile
FROM php:7.4-apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
```

```bash
# 构建并运行
docker build -t recession-detector .
docker run -p 8080:80 recession-detector
```

### ☁️ 云主机部署 (Ubuntu)
```bash
# 安装LAMP环境
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-curl php-json php-mbstring

# 上传项目文件
sudo cp -r webapp-php/* /var/www/html/recession-detector/
sudo chown -R www-data:www-data /var/www/html/recession-detector/

# 启用Apache重写模块
sudo a2enmod rewrite
sudo systemctl restart apache2

# 访问: http://your-server-ip/recession-detector/
```

### 💻 Windows本地部署 (XAMPP)
```bash
1. 下载安装 XAMPP (https://www.apachefriends.org/)
2. 启动 Apache 和 MySQL (可选)
3. 解压项目到: C:\xampp\htdocs\recession-detector\
4. 访问: http://localhost/recession-detector/
```

### 🍎 macOS本地部署 (MAMP)
```bash
1. 下载安装 MAMP (https://www.mamp.info/)
2. 启动 MAMP 服务器
3. 解压项目到: /Applications/MAMP/htdocs/recession-detector/
4. 访问: http://localhost:8888/recession-detector/
```

### 📱 手机访问优化
项目已完美适配移动端，支持：
- 响应式设计，完美适配手机屏幕
- 触摸友好的交互界面  
- 快速加载，流量友好
- PWA支持 (计划功能)

## 🔧 服务器配置优化

### Apache优化配置
```apache
# 在.htaccess或虚拟主机配置中添加:
<IfModule mod_rewrite.c>
    RewriteEngine On
    # 强制HTTPS (生产环境)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# 性能优化
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
</IfModule>
```

### Nginx优化配置
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/recession-detector;
    index index.php;

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # 静态文件缓存
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }

    # API路由
    location /api/ {
        try_files $uri $uri/ =404;
    }

    # 安全配置
    location ~ /\. {
        deny all;
    }
}
```

### PHP性能优化
```ini
# php.ini 推荐配置
memory_limit = 256M
max_execution_time = 120
upload_max_filesize = 50M
post_max_size = 50M

# 开启OPcache (生产环境)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

## 🔍 故障排除指南

### 常见问题及解决方案

**❌ 问题1: 页面显示空白**
```bash
# 原因: PHP错误或权限问题
# 解决: 
1. 检查PHP错误日志: tail -f /var/log/php_errors.log
2. 开启错误显示: ini_set('display_errors', 1);
3. 检查文件权限: ls -la
```

**❌ 问题2: 图表不显示**  
```bash
# 原因: JavaScript错误或CDN访问问题
# 解决:
1. 检查浏览器控制台错误
2. 确认网络可访问CDN资源
3. 检查Chart.js库加载状态
```

**❌ 问题3: API接口返回错误**
```bash
# 原因: PHP类文件加载问题
# 解决:
1. 检查include路径: require_once '../includes/xxx.php';
2. 验证文件存在: ls -la includes/
3. 测试单独访问: curl http://localhost/api/chart-data.php
```

**❌ 问题4: 数据获取失败**
```bash
# 原因: FRED API配置或网络问题  
# 解决:
1. 检查.env配置文件
2. 测试API连接: curl "https://api.stlouisfed.org/fred/series?series_id=UNRATE&api_key=YOUR_KEY&file_type=json"
3. 使用本地数据模式 (无需API)
```

**❌ 问题5: 样式显示异常**
```bash
# 原因: CSS文件路径或缓存问题
# 解决:
1. 检查CSS文件路径: http://localhost/assets/css/main.css
2. 清除浏览器缓存: Ctrl+F5
3. 检查CDN资源加载: Bootstrap, FontAwesome
```

### 调试模式
```php
# 在.env文件中启用调试
APP_DEBUG=true

# 在index.php开头添加:
if ($_ENV['APP_DEBUG'] === 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
```

### 性能监控
```bash
# 监控服务器资源
top -p $(pgrep -d',' php)

# 监控Apache/Nginx访问日志
tail -f /var/log/apache2/access.log
tail -f /var/log/nginx/access.log

# 监控PHP错误日志  
tail -f /var/log/php_errors.log
```

## 🚀 生产环境部署清单

### 安全配置 ✅
- [ ] 设置.env文件权限 (chmod 600)
- [ ] 禁止.git目录访问
- [ ] 开启HTTPS强制跳转
- [ ] 设置安全HTTP头
- [ ] 关闭PHP错误显示

### 性能优化 ✅  
- [ ] 开启Gzip压缩
- [ ] 配置静态资源缓存
- [ ] 启用PHP OPcache
- [ ] 设置CDN (可选)
- [ ] 数据库优化 (如使用)

### 监控配置 ✅
- [ ] 设置错误日志
- [ ] 配置访问日志  
- [ ] 服务器资源监控
- [ ] 应用性能监控 (APM)
- [ ] 备份策略

### 域名SSL ✅
- [ ] 购买/配置SSL证书
- [ ] 设置HTTPS重定向
- [ ] 配置HSTS头
- [ ] 测试SSL评级 (A+)

## 🎛️ 高级功能配置

### FRED API集成
```bash
# 1. 注册FRED账户: https://fred.stlouisfed.org/
# 2. 申请API Key: https://fred.stlouisfed.org/docs/api/api_key.html
# 3. 配置.env文件:
FRED_API_KEY=your_api_key_here

# 4. 测试API连接:
curl "https://api.stlouisfed.org/fred/series/observations?series_id=UNRATE&api_key=YOUR_KEY&file_type=json&limit=1"
```

### 缓存策略配置
```php
# 在.env中配置缓存
CACHE_ENABLED=true
CACHE_TIMEOUT=3600  # 1小时

# 手动清除缓存
rm -rf data/cache/*.json
```

### 多语言扩展
```php
# 添加新语言支持 (如日语)
# 在includes/LanguageManager.php中添加:
'ja' => [
    'page_title' => '経済後退検出器',
    'brand_name' => '亮子金融',
    // ... 更多翻译
]
```

### 数据库集成 (可选)
```sql
-- 创建MySQL数据表存储历史数据
CREATE TABLE economic_indicators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    unrate DECIMAL(5,2),
    pmi DECIMAL(5,2),
    -- ... 其他指标
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date)
);
```

## 📞 技术支持

### 获取帮助
- 📧 **邮件支持**: support@liangzi.finance  
- 📚 **文档中心**: https://docs.liangzi.finance
- 🐛 **问题报告**: GitHub Issues
- 💬 **社区讨论**: GitHub Discussions

### 服务支持
- ⚡ **快速响应**: 工作日24小时内回复
- 🔧 **远程协助**: 提供部署安装支持
- 📋 **定制开发**: 支持功能定制和二次开发
- 🎓 **培训服务**: 提供使用培训和技术培训

---

**© 2024 亮子财经 - 专业的经济数据分析工具**

**让经济数据更易懂，让投资决策更理性** 🚀