// ========================================
// GLOBAL CONSTANTS & TIMER STATE
// ========================================
let timerInterval = null;
let isTimerRunning = false;
let FOCUS_TIME_SECONDS = 25 * 60; // Default: 25 minutes (in seconds)
let BREAK_TIME_SECONDS = 5 * 60;  // Default: 5 minutes (in seconds)
let timeRemaining = FOCUS_TIME_SECONDS;
let currentMode = 'focus'; 
// Ang circumference ng circle na may radius=85 (2 * pi * 85 ≈ 534.07)
const CIRCUMFERENCE = 534.07; 
const alarmSound = new Audio('../assets/sounds/alarm.wav'); 

// Global variable for the progress circle element, i-initialize sa initFocusMode
let progressCircle = null; 

// ========================================
// DOM CONTENT LOADED - INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Navigation click handler
    const navItems = document.querySelectorAll('.nav-item[data-section]');
    const sections = document.querySelectorAll('.content-section');

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove active class from all nav items
            navItems.forEach(nav => nav.classList.remove('active'));

            // Add active class to clicked item
            this.classList.add('active');

            // Hide all sections
            sections.forEach(section => section.classList.remove('active'));

            // Show selected section
            const sectionId = this.getAttribute('data-section') + '-section';
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });

    // Initialize core features
    initScheduleGenerator();
    initFocusMode(); 

    // ===============================================
    // 1. LOGOUT MODAL LOGIC (New)
    // ===============================================
    const logoutButton = document.getElementById('logoutButton');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogoutButton = document.getElementById('cancelLogout');

    // Show the modal when the sidebar logout button is clicked
    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (logoutModal) {
                logoutModal.classList.add('active');
            }
        });
    }

    // Hide the modal when the Cancel button is clicked
    if (cancelLogoutButton) {
        cancelLogoutButton.addEventListener('click', function() {
            if (logoutModal) {
                logoutModal.classList.remove('active');
            }
        });
    }

    // Hide the modal when clicking outside the modal content
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            // Check if the click occurred directly on the overlay, not the content
            if (e.target === logoutModal) {
                logoutModal.classList.remove('active');
            }
        });
    }

    // ===============================================
    // 2. CHART.JS INITIALIZATION (New)
    // ===============================================
    var ctx = document.getElementById('weeklyChart');
    if (ctx) {
        // Tiyakin na naka-load ang Chart.js bago gamitin
        if (typeof Chart !== 'undefined') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Study Time (Hours)',
                        data: [3, 4.5, 2, 5, 3.5, 7, 6], // Example data
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 4, // Added for modern look
                        hoverBackgroundColor: '#8b5cf6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)', // Dark mode grid line color
                            },
                            ticks: {
                                color: '#94a3b8' // Dark mode tick color
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#f1f5f9' // Dark mode legend color
                            }
                        }
                    }
                }
            });
        } else {
             console.warn('Chart.js not loaded. Cannot initialize weekly chart.');
        }
    }


    // ========================================
    // INITIAL LOADING STATE HANDLING (FIXED)
    // ========================================
    const pageLoader = document.querySelector('.page-loader');
    if (pageLoader) {
        setTimeout(() => {
            // Gumamit ng 'hidden' class para i-trigger ang pointer-events: none sa CSS
            pageLoader.classList.add('hidden'); 

        }, 500); // Show loader for at least 0.5s
    }

    // 🔔 Request Notification permission agad sa start
    if (Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
    
    updateDashboardStats(); // Start any continuous updates
});

// ========================================
// SCHEDULE GENERATOR / QUICK ADD TASK
// ========================================
function initScheduleGenerator() {
    const scheduleForm = document.getElementById('quickAddForm'); 
    if (!scheduleForm) return;

    scheduleForm.addEventListener('submit', function(e) {
        e.preventDefault();
        generateScheduleFromQuickAdd(); // Use dedicated function
    });
}

function generateScheduleFromQuickAdd() {
    const taskName = document.getElementById('taskName').value.trim();
    const taskSubject = document.getElementById('taskSubject').value;
    const taskPriority = document.getElementById('taskPriority').value;

    if (taskName.length === 0 || taskSubject.length === 0) {
        showNotification('Please fill out the task name and select a subject', 'error');
        return;
    }

    const newItem = {
        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
        subject: taskSubject,
        task: taskName,
        duration: '2 hours (Estimate)',
        priority: taskPriority
    };

    showNotification(`🤖 AI is integrating "${taskName}" into your schedule...`, 'info');

    setTimeout(() => {
        insertNewScheduleItem(newItem);
        document.getElementById('taskName').value = ''; // Clear input
        document.getElementById('taskSubject').selectedIndex = 0;
        document.getElementById('taskPriority').value = 'Medium';

        showNotification('✅ Task added and optimized!', 'success');
    }, 1500);
}

function insertNewScheduleItem(item) {
    const scheduleList = document.querySelector('.schedule-list');
    if (!scheduleList) return;

    const priorityClass = item.priority === 'Urgent' ? 'priority-urgent' : item.priority === 'High' ? 'priority-high' : '';
    const priorityBadgeClass = item.priority === 'Urgent' ? 'urgent' : item.priority === 'High' ? 'high' : 'normal';
    const priorityIcon = item.priority === 'Urgent' ? '🔴' : '🟡'; // Simplified icons

    const html = `
        <div class="schedule-item ${priorityClass} new-task-animated">
            <div class="schedule-indicator ${priorityBadgeClass}"></div>
            <div class="schedule-time-block">
                <span class="time-main">${item.time}</span>
                <span class="time-duration">${item.duration}</span>
            </div>
            <div class="schedule-content">
                <div class="schedule-header-row">
                    <h4>${item.task} (${item.subject})</h4>
                    <span class="priority-badge ${priorityBadgeClass}">
                        <span class="badge-icon">${priorityIcon}</span>
                        ${item.priority.toUpperCase()}
                    </span>
                </div>
                <p class="schedule-desc">AI recommended slot based on focus time.</p>
                <div class="schedule-meta">
                    <span class="meta-tag">🧠 New Task</span>
                </div>
            </div>
            <button class="btn-start-task">Start</button>
        </div>
    `;

    scheduleList.insertAdjacentHTML('afterbegin', html);
}

// ========================================
// FOCUS MODE / POMODORO TIMER
// ========================================
function getSelectedTime(mode) {
    const selector = mode === 'focus' ? 'input[name="study-time"]:checked' : 'input[name="break-time"]:checked';
    const selectedRadio = document.querySelector(selector);

    if (!selectedRadio) return mode === 'focus' ? 25 * 60 : 5 * 60; // Fallback to default (in seconds)

    if (selectedRadio.value === 'custom') {
        const customInputId = mode === 'focus' ? 'customStudyTime' : 'customBreakTime';
        // Naka-minutes ang value sa custom input
        const customValue = parseInt(document.getElementById(customInputId)?.value ?? 0, 10); 
        return isNaN(customValue) || customValue < 1 ? (mode === 'focus' ? 25 : 5) * 60 : customValue * 60;
    }

    // Assumes data-time is in MINUTES
    const standardTimeMinutes = selectedRadio.getAttribute('data-time') ?? '0'; 
    return parseInt(standardTimeMinutes, 10) * 60; // Convert minutes to seconds
}

function updateFocusConstants() {
    FOCUS_TIME_SECONDS = getSelectedTime('focus');
    BREAK_TIME_SECONDS = getSelectedTime('break');
}

function initFocusMode() {
    const startButton = document.getElementById('startFocus');
    const resetButton = document.getElementById('resetFocus'); 
    const timeOptions = document.querySelectorAll('.timer-settings input[type="radio"]');

    if (!startButton) return;
    
    // 🚨 Initialization ng global progressCircle variable DITO
    progressCircle = document.querySelector('.timer-progress');
    if (progressCircle) {
        progressCircle.style.strokeDasharray = CIRCUMFERENCE;
    }

    // 1. Setup Time Option Listeners (Handles standard and custom toggle)
    timeOptions.forEach(radio => {
        radio.addEventListener('change', function() {
            updateFocusConstants();
            
            if (!isTimerRunning) {
                resetFocusTimer(this.name === 'study-time' ? 'focus' : 'break'); 
            }
            
            // Handle Custom Input Visibility (CRITICAL LOGIC)
            const isCustom = this.value === 'custom';
            const inputId = this.name === 'study-time' ? 'customStudyTime' : 'customBreakTime';
            const customInput = document.getElementById(inputId);
            
            if (customInput) {
                customInput.style.display = isCustom ? 'block' : 'none'; 
            }
            
            // Ensure the other custom input is hidden when selecting a radio option for the current group
            if (!isCustom) {
                const otherInputId = this.name === 'study-time' ? 'customBreakTime' : 'customStudyTime';
                const otherCustomInput = document.getElementById(otherInputId);
                if(otherCustomInput) {
                     // Hide only if the other group's custom radio is NOT selected
                     if(document.getElementById(otherInputId)?.style.display === 'block' && this.name !== (otherInputId.includes('Study') ? 'study-time' : 'break-time')) {
                         // Pass. Let the other radio group handle its own visibility.
                     } else if(this.name.includes('study-time')) {
                         // When selecting a study time, ensure break time custom is hidden if a standard study time is picked
                         document.getElementById('customBreakTime')?.style.display = 'none';
                     } else {
                         // When selecting a break time, ensure study time custom is hidden if a standard break time is picked
                         document.getElementById('customStudyTime')?.style.display = 'none';
                     }
                }
            }
        });
    });

    // 2. Setup Custom Input Listeners 
    document.getElementById('customStudyTime')?.addEventListener('input', () => {
        if (document.getElementById('customStudyToggle')?.checked && !isTimerRunning) { 
            updateFocusConstants();
            resetFocusTimer('focus');
        }
    });
    document.getElementById('customBreakTime')?.addEventListener('input', () => {
        if (document.getElementById('customBreakToggle')?.checked && !isTimerRunning) {
            updateFocusConstants();
            resetFocusTimer('break');
        }
    });


    // 3. Setup Controls
    startButton.addEventListener('click', toggleFocusMode);

    if (resetButton) {
        resetButton.addEventListener('click', () => resetFocusTimer('focus'));
    }

    // 4. Initial Update (Important for display)
    updateFocusConstants(); 
    updateTimerDisplay();
    updateTimerProgress();
    updateFocusModeTitle();
    
    // Initial Custom Input Visibility Fix (From user's new block, cleaned)
    document.getElementById('customStudyTime')?.style.display = document.getElementById('customStudyToggle')?.checked ? 'block' : 'none';
    document.getElementById('customBreakTime')?.style.display = document.getElementById('customBreakToggle')?.checked ? 'block' : 'none';
}

function toggleFocusMode() {
    const startButton = document.getElementById('startFocus');

    // Disable settings only when the timer is RUNNING (opposite of !isTimerRunning)
    document.querySelectorAll('.timer-settings input').forEach(input => input.disabled = isTimerRunning);

    if (!isTimerRunning) {
        updateFocusConstants(); 
        const totalTime = currentMode === 'focus' ? FOCUS_TIME_SECONDS : BREAK_TIME_SECONDS;
        
        if (timeRemaining <= 0 || timeRemaining === totalTime) {
            timeRemaining = totalTime; 
        }

        startFocusTimer();
        startButton.innerHTML = '<span class="btn-icon">⏸️</span> Pause Session'; 
        startButton.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
        showNotification(`Starting ${currentMode} session!`, 'info');
    } else {
        stopFocusTimer();
        startButton.innerHTML = '<span class="btn-icon">▶️</span> Resume Session'; 
        startButton.style.background = 'linear-gradient(135deg, #f59e0b, #eab308)'; 
        showNotification(`${currentMode} session paused.`, 'warning');
    }
}

function startFocusTimer() {
    isTimerRunning = true;
    
    timerInterval = setInterval(() => {
        if (timeRemaining <= 0) {
            notifySessionComplete(); 
            return;
        }
        
        timeRemaining--;
        updateTimerDisplay();
        updateTimerProgress();
    }, 1000);
}

function stopFocusTimer() {
    isTimerRunning = false;
    clearInterval(timerInterval);
    document.querySelectorAll('.timer-settings input').forEach(input => input.disabled = false);
}

function resetFocusTimer(mode = 'focus') {
    stopFocusTimer();
    currentMode = mode;
    updateFocusConstants(); 
    timeRemaining = mode === 'focus' ? FOCUS_TIME_SECONDS : BREAK_TIME_SECONDS;
    updateTimerDisplay();
    updateTimerProgress();
    updateFocusModeTitle();

    const startButton = document.getElementById('startFocus');
    startButton.innerHTML = '<span class="btn-icon">▶️</span> Start Focus Session';
    startButton.style.background = 'linear-gradient(135deg, #6366f1, #8b5cf6)';
    
    document.querySelectorAll('.timer-settings input').forEach(input => input.disabled = false);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    
    document.querySelector('#timerMinutes').textContent = String(minutes).padStart(2, '0');
    document.querySelector('#timerSeconds').textContent = String(seconds).padStart(2, '0');
    
    document.title = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')} - ${currentMode === 'focus' ? 'Focus' : 'Break'}`;
}

function updateTimerProgress() {
    // Gumamit ng global variable na progressCircle
    if (!progressCircle) {
        progressCircle = document.querySelector('.timer-progress');
        if (!progressCircle) return;
        progressCircle.style.strokeDasharray = CIRCUMFERENCE; // Set dasharray once
    }

    const totalTime = currentMode === 'focus' ? FOCUS_TIME_SECONDS : BREAK_TIME_SECONDS;
    // I-calculate kung gaano karami ang natitirang dash length
    const progress = (timeRemaining / totalTime) * CIRCUMFERENCE; 

    progressCircle.style.strokeDashoffset = CIRCUMFERENCE - progress; 
}

function updateFocusModeTitle() {
    const titleElement = document.getElementById('focusModeTitle'); 
    const descriptionElement = document.querySelector('.focus-mode-description');
    
    const totalSeconds = currentMode === 'focus' ? FOCUS_TIME_SECONDS : BREAK_TIME_SECONDS;
    const duration = Math.floor(totalSeconds / 60);

    if (titleElement) {
        titleElement.textContent = currentMode === 'focus' 
            ? `🎯 Focus Mode (${duration} min)` 
            : `☕ Break Time (${duration} min)`;
    }

    if (descriptionElement) {
        descriptionElement.textContent = currentMode === 'focus' 
            ? 'Time to concentrate on your tasks.' 
            : 'Take a quick breather, relax your eyes and body!';
    }
}

function playAlarmAndNotify(mode) {
    alarmSound.currentTime = 0; 
    alarmSound.play().catch(e => console.error("Error playing alarm sound:", e)); 

    if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate([500, 250, 500, 250, 500]);
    }

    if (Notification.permission === 'granted') {
           const title = mode === 'focus' ? '🎉 SESSION COMPLETE!' : '✅ BREAK IS OVER!';
           const body = mode === 'focus' ? 'Great job! Time for a well-deserved break.' : 'Time to get back to focus mode!';
           new Notification(title, { body: body, icon: '../assets/images/focus-icon.png' }); 
    }
}


function notifySessionComplete() {
    stopFocusTimer(); 
    playAlarmAndNotify(currentMode); 

    const autoBreakToggle = document.getElementById('autoBreakToggle');
    const autoStartNext = autoBreakToggle ? autoBreakToggle.checked : true;

    let nextMode;
    let successMessage;

    if (currentMode === 'focus') {
        successMessage = '🎉 Focus session complete! Time for a break.';
        addCompletedSession(FOCUS_TIME_SECONDS); 
        nextMode = 'break';
    } else if (currentMode === 'break') {
        successMessage = '✅ Break complete! Time to get back to focus mode.';
        nextMode = 'focus';
    }
    
    showNotification(successMessage, 'success');
    
    currentMode = nextMode;
    updateFocusConstants();
    timeRemaining = nextMode === 'focus' ? FOCUS_TIME_SECONDS : BREAK_TIME_SECONDS;
    
    updateTimerDisplay();
    updateTimerProgress();
    updateFocusModeTitle();
    
    const startButton = document.getElementById('startFocus');
    
    if (autoStartNext) {
        setTimeout(() => {
            startButton.innerHTML = '<span class="btn-icon">⏸️</span> Pause Session';
            startButton.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            startFocusTimer();
        }, 1000); 
    } else {
        startButton.innerHTML = `<span class="btn-icon">▶️</span> Start ${nextMode === 'focus' ? 'Focus' : 'Break'} Session`;
        startButton.style.background = 'linear-gradient(135deg, #6366f1, #8b5cf6)';
        document.querySelectorAll('.timer-settings input').forEach(input => input.disabled = false);
    }
}

function addCompletedSession(durationSeconds) {
    const sessionsContainer = document.querySelector('.focus-sessions');
    if (!sessionsContainer) return;

    const now = new Date();
    const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    const durationMins = durationSeconds / 60; 
    
    const sessionHTML = `
        <div class="session-item completed">
            <span class="session-icon">✅</span>
            <div class="session-details">
                <h4>Study Session</h4>
                <p>${durationMins} minutes • Completed at ${timeStr}</p>
            </div>
        </div>
    `;
    
    sessionsContainer.insertAdjacentHTML('afterbegin', sessionHTML);
}

// ========================================
// HELPER FUNCTIONS 
// ========================================

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    let bgColor;
    if (type === 'success') bgColor = '#10b981';
    else if (type === 'error') bgColor = '#ef4444';
    else if (type === 'warning') bgColor = '#f59e0b';
    else bgColor = '#6366f1';
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${bgColor};
        color: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
    
    .loading {
        text-align: center;
        padding: 2rem;
        color: var(--text-gray);
        animation: pulse 1.5s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .new-task-animated {
        opacity: 0;
        transform: translateY(-10px);
        animation: fadeInSlideDown 0.5s ease forwards;
    }
    @keyframes fadeInSlideDown {
        to { opacity: 1; transform: translateY(0); }
    }
    
`;
document.head.appendChild(style);

// ========================================
// STATS UPDATES & CHART INTERACTIONS
// ========================================
function updateDashboardStats() {
    let totalStudyTime = 0; // Simulated tracker

    setInterval(() => {
        // Update stats here if needed
    }, 60000); 
}

// NOTE: Itong block na ito ay para lang sa simulated chart interaction. 
// Kung ginagamit mo ang Chart.js canvas sa taas, pwedeng alisin ito.
const chartBars = document.querySelectorAll('.chart-bar');
chartBars.forEach(bar => {
    bar.addEventListener('click', function() {
        chartBars.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const day = this.querySelector('span').textContent;
        showNotification(`Viewing stats for ${day}`, 'info');
    });
});