<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>沈超 | PHP 开发工程师</title>
    <meta name="description" content="沈超 - PHP 后端开发工程师个人主页">
    <meta name="theme-color" content="#00f0ff">

    <!-- 引入 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- 引入 Tailwind CSS (用于 AI 组件) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- 引入自定义 CSS -->
    <link rel="stylesheet" type="text/css" href="{$hostUrl}/static/css/home-tech.css">
    
    <!-- 引入 jQuery -->
    <script src="{$hostUrl}/static/js/jquery.js"></script>
</head>
<body>
<!-- 动态背景 -->
<div id="particles-bg"></div>
<div class="grid-overlay"></div>

<!-- ==================== 初始加载界面（左右布局）==================== -->
<div id="initial-screen">
    <div class="initial-container">
        <!-- 左侧：AI 智能助手 -->
        <div class="initial-left-panel">
            <?php if(session('?userList')): ?>
                <?php
                $historyUrl = url('/home/index/getChatHistory');
                $domain = request()->domain();
                $domain = explode(':',$domain);
                $urlHost = $domain[0].":".$domain[1];
                ?>
                <input type="hidden" id="currentUserId" data-ws="{$urlHost}" data-url="{$historyUrl}" value="{$token}">
                
                <!-- AI 对话组件容器（唯一实例） -->
                <div id="chatContainer" class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <!-- 聊天头部 -->
                    <div id="chatHeader" class="bg-gradient-to-r from-indigo-500 to-purple-600 p-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <h2 class="text-lg font-semibold">实时对话</h2>
                                <button id="enterMainPageBtn" class="ml-2 px-3 py-1.5 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-sm font-medium transition-all duration-300 flex items-center space-x-1 border border-white border-opacity-30 hover:border-opacity-50">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>进入主页</span>
                                </button>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="flex w-3 h-3">
                                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <span class="text-sm">在线</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 消息区域 -->
                    <div id="chatMessages" class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50">
                        <div class="flex justify-start message-enter">
                            <div class="max-w-xs lg:max-w-md bg-white rounded-2xl rounded-tl-none p-4 shadow-sm border">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-robot text-indigo-500 mr-2"></i>
                                    <span class="font-medium text-gray-700">AI助手</span>
                                </div>
                                <p class="text-gray-600">您好！我是沈超的应聘助手，随时为您解答招聘问题。请问您有什么想问沈超的吗？</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 输入区域 -->
                    <div class="bg-white rounded-2xl shadow-xl p-4">
                        <div class="flex items-end space-x-3">
                            <div class="flex-1">
                                <label for="messageInput" class="block text-sm font-medium text-gray-700 mb-1">
                                    输入您的问题
                                </label>
                                <div class="relative">
                                    <textarea
                                        id="messageInput"
                                        rows="3"
                                        placeholder="请输入您的问题... (支持Enter发送)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none transition-all"
                                    ></textarea>
                                    <div class="absolute bottom-2 right-2 flex items-center space-x-2">
                                        <span id="charCount" class="text-xs text-gray-500">0/500</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col space-y-2 pb-1">
                                <button
                                    id="sendButton"
                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white p-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                    disabled
                                >
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                                <button
                                    id="clearButton"
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 p-3 rounded-xl transition-colors"
                                    title="清空对话"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tailwind 配置 -->
                <script>
                    tailwind.config = {
                        theme: {
                            extend: {
                                colors: {
                                    primary: '#6366f1',
                                    secondary: '#8b5cf6',
                                    accent: '#ec4899',
                                    dark: '#1e293b',
                                    light: '#f8fafc'
                                }
                            }
                        }
                    }
                </script>
                
                <!-- AI 对话脚本 -->
                <script src="{$hostUrl}/static/js/aiScript.js"></script>
            <?php else: ?>
                <div class="login-prompt">
                    <i class="fas fa-robot fa-3x" style="color: var(--primary-color); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--text-primary); margin-bottom: 15px;">AI 智能助手</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">登录后即可体验 AI 助手功能</p>
                    <a href="{:url('index/ai')}" class="btn-primary">立即登录</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 右侧：进入按钮 -->
        <div class="initial-right-panel">
            <button class="enter-button">
                <span class="glitch-text">进入主页</span>
            </button>
        </div>
    </div>
</div>

<!-- ==================== 主内容区域 ==================== -->
<div id="main-content">
    <!-- 导航栏 -->
    <header id="mainHeader">
        <div class="nav-container">
            <a href="#home" class="nav-logo">找工作中</a>
            
            <button class="nav-toggle" id="navToggle" aria-label="切换导航菜单">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="#home" class="nav-link active">首页</a></li>
                <li><a href="#skills" class="nav-link">专业技能</a></li>
                <li><a href="#projects" class="nav-link">精选项目</a></li>
                <li><a href="#ai-assistant-section" class="nav-link">AI 助手</a></li>
                <li><a href="http://www.shenchao.me/swoper/site/loginOld" class="nav-link">管理系统</a></li>
                <li><a href="http://www.shenchao.me/swoper/site/longData" class="nav-link">数据展示</a></li>
            </ul>
        </div>
    </header>

    <!-- Hero 区域 -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>你好，我是沈超</h1>
            <p>PHP 后端开发工程师</p>
            <p class="hero-description">
                <small class="text-muted">
                专注于高性能 Web 应用开发，擅长构建可扩展的后端系统。<br>
                多年 PHP 开发经验，熟悉 Yii、ThinkPHP 等主流框架。
                </small>
            </p>
            <a href="#projects" class="btn-primary">查看我的作品</a>
        </div>
    </section>

    <!-- 专业技能 -->
    <section id="skills">
        <div class="section-title">
            <h2>专业技能</h2>
        </div>
        
        <div class="skills-grid">
            <!-- 技能 1 -->
            <div class="skill-card">
                <i class="fab fa-php skill-icon"></i>
                <h3>PHP 开发</h3>
                <p>熟练使用 Yii、ThinkPHP 框架进行高效开发，深入理解 OOP 和设计模式</p>
            </div>
            
            <!-- 技能 2 -->
            <div class="skill-card">
                <i class="fas fa-database skill-icon"></i>
                <h3>MySQL</h3>
                <p>数据库设计、查询优化、性能调优，能够处理百万级数据记录</p>
            </div>
            
            <!-- 技能 3 -->
            <div class="skill-card">
                <i class="fab fa-html5 skill-icon"></i>
                <h3>前端技术</h3>
                <p>HTML5 + CSS3 + jQuery，开发响应式网站和交互式用户界面</p>
            </div>
            
            <!-- 技能 4 -->
            <div class="skill-card">
                <i class="fas fa-laptop-code skill-icon"></i>
                <h3>独立开发</h3>
                <p>从需求分析到上线部署全流程掌控，具备完整的项目交付能力</p>
            </div>
            
            <!-- 技能 5 -->
            <div class="skill-card">
                <i class="fas fa-robot skill-icon"></i>
                <h3>AI 工具</h3>
                <p>熟练使用通义灵码等 AI 编程助手，大幅提升开发效率和质量</p>
            </div>
        </div>
    </section>

    <!-- 精选项目 -->
    <section id="projects">
        <div class="section-title">
            <h2>精选项目</h2>
        </div>
        
        <div class="carousel-wrapper">
            <button class="carousel-btn prev-btn" type="button" aria-label="上一个项目">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="carousel-container">
                <div class="carousel-track projects-track">
                    <!-- 项目 1 -->
                    <a href="http://www.shenchao.me/swoper/site/loginOld" target="_blank" class="project-card-link">
                        <div class="project-card-item">
                            <div class="project-image">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="project-info">
                                <h3>企业管理系统</h3>
                                <div class="tech-tags">
                                    <span class="tech-tag">Yii1</span>
                                    <span class="tech-tag">MySQL</span>
                                    <span class="tech-tag">jQuery</span>
                                </div>
                                <p>基于 Yii1 框架开发的企业管理系统，实现了模块化开发，提升了代码复用率，优化了数据库查询性能。</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- 项目 2 -->
                    <a href="javascript:void(0);" class="project-card-link" data-project="企业微信开发">
                        <div class="project-card-item">
                            <div class="project-image">
                                <i class="fab fa-weixin"></i>
                            </div>
                            <div class="project-info">
                                <h3>企业微信开发</h3>
                                <div class="tech-tags">
                                    <span class="tech-tag">Yii1</span>
                                    <span class="tech-tag">企业微信API</span>
                                    <span class="tech-tag">jQuery</span>
                                </div>
                                <p>集成企业微信 API 的企业内部应用，实现消息推送、审批流程等功能。</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- 项目 3 -->
                    <a href="http://www.shenchao.me/swoper/site/longData" target="_blank" class="project-card-link">
                        <div class="project-card-item">
                            <div class="project-image">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="project-info">
                                <h3>数据可视化大屏</h3>
                                <div class="tech-tags">
                                    <span class="tech-tag">原生PHP</span>
                                    <span class="tech-tag">ECharts</span>
                                    <span class="tech-tag">jQuery</span>
                                </div>
                                <p>原生 PHP 结合 jQuery 开发的数据报表系统，支持动态生成图表，每日处理百万级数据记录。</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- 项目 4 -->
                    <a href="javascript:void(0);" class="project-card-link" data-project="API接口服务平台">
                        <div class="project-card-item">
                            <div class="project-image">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="project-info">
                                <h3>API 接口服务平台</h3>
                                <div class="tech-tags">
                                    <span class="tech-tag">Yii1</span>
                                    <span class="tech-tag">Redis</span>
                                    <span class="tech-tag">Swoole</span>
                                </div>
                                <p>基于 Swoole 扩展的高性能 API 网关，为移动端提供毫秒级响应服务。</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <button class="carousel-btn next-btn" type="button" aria-label="下一个项目">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- 轮播指示器 -->
        <div class="carousel-indicators projects-indicators"></div>
    </section>

    <!-- AI 智能助手组件（占位容器，AI助手会移动到这里） -->
    <section id="ai-assistant-section">
        <div class="section-title">
            <h2>AI 智能助手</h2>
        </div>
        
        <div class="ai-container-wrapper" id="aiAssistantTarget">
            <!-- AI助手组件会通过JS移动到这里 -->
            <?php if(!session('?userList')): ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">登录后即可使用 AI 助手</p>
                    <a href="{:url('index/ai')}" class="btn-primary">立即登录</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 页脚 -->
    <footer>
        <p>&copy; 2026 沈超. All Rights Reserved.</p>
    </footer>
</div>

<!-- 引入主页面交互脚本 -->
<script src="{$hostUrl}/static/js/main-tech.js"></script>
</body>
</html>
