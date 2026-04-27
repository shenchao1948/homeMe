
$(document).ready(function() {
    console.log('🚀 科技感个人主页初始化');

    // 初始化粒子背景
    initParticles();
    
    // 绑定进入主页按钮事件
    bindEnterButton();
    
    // 绑定导航菜单
    bindNavigation();
    
    // 绑定平滑滚动
    bindSmoothScroll();
    
    // 初始化轮播
    initCarousels();
    
    // 绑定滚动效果
    bindScrollEffects();
});

// 初始化粒子背景效果
function initParticles() {
    const canvas = document.createElement('canvas');
    canvas.id = 'particles-canvas';
    canvas.style.position = 'fixed';
    canvas.style.top = '0';
    canvas.style.left = '0';
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    canvas.style.zIndex = '-1';
    canvas.style.pointerEvents = 'none';
    document.body.insertBefore(canvas, document.body.firstChild);

    const ctx = canvas.getContext('2d');
    let particles = [];
    const particleCount = window.innerWidth < 768 ? 30 : 60;

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    resizeCanvas();
    $(window).on('resize', resizeCanvas);

    class Particle {
        constructor() {
            this.reset();
        }

        reset() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 1;
            this.speedX = (Math.random() - 0.5) * 0.5;
            this.speedY = (Math.random() - 0.5) * 0.5;
            this.opacity = Math.random() * 0.5 + 0.2;
        }

        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
            if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(0, 240, 255, ${this.opacity})`;
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function connectParticles() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 150) {
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(0, 240, 255, ${0.1 * (1 - distance / 150)})`;
                    ctx.lineWidth = 1;
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });

        connectParticles();
        requestAnimationFrame(animate);
    }

    animate();
    console.log('✨ 粒子背景已初始化');
}

// 绑定进入按钮事件
function bindEnterButton() {
    // 使用事件委托，确保动态生成的元素也能响应
    $(document).on('click touchend', '.enter-button, #enterMainPageBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // 对于touchend事件，防止与click事件重复触发
        if (e.type === 'touchend') {
            // 标记已处理，防止click再次触发
            $(this).data('touched', true);
            setTimeout(() => {
                $(this).removeData('touched');
            }, 500);
        }
        
        // 如果已经通过touchend处理过，则忽略click事件
        if ($(this).data('touched') && e.type === 'click') {
            return;
        }
        
        console.log('🎯 检测到进入主页按钮点击 (' + e.type + ')');
        enterMainPage();
    });
    
    console.log('✅ 进入主页按钮事件已绑定（支持触摸）');
}

// 进入主页面函数
function enterMainPage() {
    console.log('✨ 用户点击进入主页面');

    // 防止重复点击
    if ($('#initial-screen').hasClass('hidden')) {
        console.log('⚠️ 已经进入主页面，忽略重复点击');
        return;
    }

    // 隐藏初始屏幕
    $('#initial-screen').addClass('hidden');

    // 将AI助手移动到主内容区域
    moveAIAssistantToMainContent();

    // 显示主内容
    setTimeout(() => {
        $('#main-content').addClass('visible');
        // 【关键】启用页面滚动
        $('body').css('overflow', 'auto');
    }, 400);

    // 触发一次滚动事件以更新导航状态
    setTimeout(() => {
        $(window).trigger('scroll');
    }, 1200);
}

// 将AI助手移动到主内容区域
function moveAIAssistantToMainContent() {
    const aiContainer = $('#chatContainer');
    const targetContainer = $('#aiAssistantTarget');
    
    if (aiContainer.length > 0 && targetContainer.length > 0) {
        console.log('🔄 准备移动AI助手到主内容区域');
        
        // 【关键】检查并保存 WebSocket 状态
        let needReconnect = false;
        if (typeof state !== 'undefined' && state.ws) {
            console.log('⚠️ 检测到活跃的 WebSocket 连接，先关闭它');
            needReconnect = true;
            
            // 暂时禁用自动重连
            if (state.reconnectTimer) {
                clearTimeout(state.reconnectTimer);
                state.reconnectTimer = null;
            }
            
            // 停止心跳
            if (state.heartbeatTimer) {
                clearInterval(state.heartbeatTimer);
                state.heartbeatTimer = null;
            }
            
            // 【关键】设置手动关闭标志
            state.isManuallyClosed = true;
            
            // 关闭 WebSocket（不触发重连）
            state.ws.onclose = null; // 移除关闭事件处理器
            state.ws.onerror = null; // 移除错误事件处理器
            state.ws.close();
            state.ws = null;
            state.isConnected = false;
            state.isAuthenticated = false;
        }
        
        // 添加过渡动画
        aiContainer.css({
            'transition': 'all 0.6s ease',
            'opacity': '0',
            'transform': 'scale(0.9)'
        });
        
        setTimeout(() => {
            // 移动DOM元素
            aiContainer.detach().appendTo(targetContainer);
            
            // 移除固定样式，让它适应新容器
            aiContainer.css({
                'opacity': '1',
                'transform': 'scale(1)',
                'max-width': '100%'
            });
            
            console.log('✅ AI助手已移动到主内容区域');
            
            // 【关键】如果需要，重新初始化 WebSocket
            if (needReconnect) {
                console.log('🔄 重新初始化 WebSocket 连接...');
                setTimeout(() => {
                    // 重置状态
                    state.reconnectAttempts = 0;
                    state.isManuallyClosed = false; // 重置标志
                    
                    // 重新初始化 WebSocket
                    if (typeof initWebSocket === 'function') {
                        initWebSocket();
                    }
                }, 500);
            }
        }, 300);
    }
}

// 绑定导航事件
function bindNavigation() {
    // 移动端菜单切换
    $('#navToggle').on('click', function() {
        $(this).toggleClass('active');
        $('#navMenu').toggleClass('active');
    });

    // 导航链接点击
    $('.nav-link').on('click', function(e) {
        let target = $(this).attr('href');
        if(target.indexOf("#")>-1){
            e.preventDefault();
            target = $(target);
        }else{
            return true;
        }
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800, 'swing');

            // 关闭移动端菜单
            if ($(window).width() < 768) {
                $('#navToggle').removeClass('active');
                $('#navMenu').removeClass('active');
            }

            // 更新激活状态
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        }
    });

    // 点击其他地方关闭菜单
    $(document).on('click', function(e) {
        if (!$(e.target).closest('header').length && $(window).width() < 768) {
            $('#navToggle').removeClass('active');
            $('#navMenu').removeClass('active');
        }
    });
}

// 绑定平滑滚动
function bindSmoothScroll() {
    $('a[href*="#"]').on('click', function(e) {
        e.preventDefault();

        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800, 'swing');

            // 更新激活状态
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        }
    });
}

// 初始化轮播
function initCarousels() {
    // 只初始化项目轮播 - 桌面端3个，平板端2个，移动端1个
    initCarousel('.projects-track', '.projects-indicators', 3, 2, 1);
}

/**
 * 通用轮播初始化函数
 * @param {string} trackSelector - 轨道选择器
 * @param {string} indicatorsSelector - 指示器选择器
 * @param {number} desktopVisible - 桌面端可见数量
 * @param {number} tabletVisible - 平板端可见数量
 * @param {number} mobileVisible - 移动端可见数量
 */
function initCarousel(trackSelector, indicatorsSelector, desktopVisible, tabletVisible, mobileVisible) {
    const $track = $(trackSelector);
    const $container = $track.parent();
    const $wrapper = $container.parent();
    const $indicators = $(indicatorsSelector);
    const $items = $track.children();
    const totalItems = $items.length;
    
    let currentIndex = 0;
    let itemsPerView = getItemsPerView();
    let maxIndex = Math.max(0, totalItems - itemsPerView);
    let autoPlayTimer = null;
    
    // 创建指示器
    function createIndicators() {
        $indicators.empty();
        const indicatorCount = maxIndex + 1;
        for (let i = 0; i < indicatorCount; i++) {
            const $indicator = $('<div class="carousel-indicator"></div>');
            if (i === 0) $indicator.addClass('active');
            $indicator.on('click', () => goToSlide(i));
            $indicators.append($indicator);
        }
    }
    
    // 获取当前视口可见数量
    function getItemsPerView() {
        const width = $(window).width();
        if (width <= 768) return mobileVisible;
        if (width <= 1024) return tabletVisible;
        return desktopVisible;
    }
    
    // 【关键修复】更新轮播位置 - 使用精确的偏移量计算
    function updateCarousel() {
        const containerWidth = $container.width();
        const itemWidth = $items.first().outerWidth();
        const gap = parseFloat($track.css('gap')) || 30;
        
        // 计算每个项目的实际占用宽度（包含gap）
        const itemFullWidth = itemWidth + gap;
        
        // 计算偏移量
        const offset = -(currentIndex * itemFullWidth);
        
        $track.css('transform', `translateX(${offset}px)`);
        
        // 更新指示器
        $indicators.find('.carousel-indicator').removeClass('active');
        $indicators.find('.carousel-indicator').eq(currentIndex).addClass('active');
        
        // 更新按钮状态
        $wrapper.find('.prev-btn').prop('disabled', currentIndex === 0);
        $wrapper.find('.next-btn').prop('disabled', currentIndex >= maxIndex);
    }
    
    // 跳转到指定幻灯片
    function goToSlide(index) {
        currentIndex = Math.max(0, Math.min(index, maxIndex));
        updateCarousel();
        resetAutoPlay();
    }
    
    // 下一张
    function nextSlide() {
        if (currentIndex < maxIndex) {
            currentIndex++;
        } else {
            currentIndex = 0; // 循环回到第一张
        }
        updateCarousel();
    }
    
    // 上一张
    function prevSlide() {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = maxIndex; // 循环到最后一张
        }
        updateCarousel();
    }
    
    // 自动播放
    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(nextSlide, 4000); // 4秒切换一次
    }
    
    function stopAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }
    
    function resetAutoPlay() {
        stopAutoPlay();
        startAutoPlay();
    }
    
    // 绑定按钮事件
    $wrapper.find('.prev-btn').on('click', () => {
        prevSlide();
        resetAutoPlay();
    });
    
    $wrapper.find('.next-btn').on('click', () => {
        nextSlide();
        resetAutoPlay();
    });
    
    // 触摸滑动支持
    let touchStartX = 0;
    let touchEndX = 0;
    
    $container.on('touchstart', (e) => {
        touchStartX = e.originalEvent.touches[0].clientX;
        stopAutoPlay();
    });
    
    $container.on('touchend', (e) => {
        touchEndX = e.originalEvent.changedTouches[0].clientX;
        handleSwipe();
        startAutoPlay();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
    }
    
    // 鼠标悬停暂停自动播放
    $wrapper.on('mouseenter', stopAutoPlay);
    $wrapper.on('mouseleave', startAutoPlay);
    
    // 【关键修复】窗口大小改变时重新计算
    $(window).on('resize', debounce(() => {
        const oldMaxIndex = maxIndex;
        itemsPerView = getItemsPerView();
        maxIndex = Math.max(0, totalItems - itemsPerView);
        
        // 如果当前索引超出范围，调整到最大值
        if (currentIndex > maxIndex) {
            currentIndex = maxIndex;
        }
        
        // 重新创建指示器
        createIndicators();
        
        // 强制重新计算布局并更新
        setTimeout(() => {
            updateCarousel();
        }, 150);
    }, 250));
    
    // 【关键修复】初始化时等待 DOM 完全渲染
    setTimeout(() => {
        createIndicators();
        updateCarousel();
        startAutoPlay();
    }, 150);
}

// 防抖函数
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// 绑定滚动效果
function bindScrollEffects() {
    $(window).on('scroll', function() {
        const scrollPos = $(window).scrollTop() + 100;

        // Header 滚动效果
        if ($(window).scrollTop() > 50) {
            $('header').addClass('scrolled');
        } else {
            $('header').removeClass('scrolled');
        }

        // 根据滚动位置高亮对应导航
        $('section[id]').each(function() {
            const sectionTop = $(this).offset().top;
            const sectionHeight = $(this).outerHeight();
            const sectionId = $(this).attr('id');

            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                $('.nav-link').removeClass('active');
                $(`.nav-link[href="#${sectionId}"]`).addClass('active');
            }
        });

        // 元素进入视口动画
        $('.skill-card, .project-card-item').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).css('opacity', '1').css('transform', 'translateY(0)');
            }
        });
    });
}

// 【新增】显示项目详情（用于没有链接的项目）
window.showProjectDetail = function(projectName) {
    console.log('查看项目详情:', projectName);
    
    // 创建模态框
    const modal = $(`
        <div class="project-modal-overlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        ">
            <div class="project-modal-content" style="
                background: var(--surface);
                border: 2px solid var(--border-glow);
                border-radius: 20px;
                padding: 40px;
                max-width: 600px;
                width: 90%;
                box-shadow: var(--shadow-glow);
                transform: scale(0.9);
                transition: transform 0.3s ease;
            ">
                <h2 style="
                    color: var(--primary-color);
                    margin-bottom: 20px;
                    font-size: 2rem;
                ">${projectName}</h2>
                <p style="
                    color: var(--text-secondary);
                    line-height: 1.8;
                    margin-bottom: 30px;
                ">该项目详情页面正在建设中，敬请期待！</p>
                <button class="modal-close-btn" style="
                    background: var(--gradient-1);
                    color: white;
                    border: none;
                    padding: 12px 30px;
                    border-radius: 50px;
                    cursor: pointer;
                    font-weight: 700;
                    transition: var(--transition-smooth);
                ">关闭</button>
            </div>
        </div>
    `);
    
    $('body').append(modal);
    
    // 显示动画
    setTimeout(() => {
        modal.css('opacity', '1');
        modal.find('.project-modal-content').css('transform', 'scale(1)');
    }, 10);
    
    // 关闭事件
    modal.on('click', function(e) {
        if ($(e.target).hasClass('project-modal-overlay') || $(e.target).hasClass('modal-close-btn')) {
            modal.css('opacity', '0');
            modal.find('.project-modal-content').css('transform', 'scale(0.9)');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    });
};

// 绑定项目卡片点击事件
function bindProjectCardClicks() {
    $('.project-card-link[data-project]').on('click', function(e) {
        e.preventDefault();
        const projectName = $(this).data('project');
        showProjectDetail(projectName);
    });
}

// 在主文档就绪时绑定项目点击事件
bindProjectCardClicks();
