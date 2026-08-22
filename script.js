let tabs = [{
    url: '',
    history: [],
    historyIndex: -1
}];
let activeTab = 0;

function createTab(url = '') {
    const tabId = tabs.length;
    tabs.push({
        url: url,
        history: [],
        historyIndex: -1
    });
    
    const tabBtn = document.createElement('div');
    tabBtn.className = 'tab';
    tabBtn.dataset.tab = tabId;
    tabBtn.textContent = 'Tab ' + (tabId + 1);
    tabBtn.addEventListener('click', () => switchTab(tabId));
    
    const newTabBtn = document.getElementById('new-tab');
    document.getElementById('tabs-container').insertBefore(tabBtn, newTabBtn);
    
    switchTab(tabId);
}

function switchTab(tabId) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
    activeTab = tabId;
    
    const tab = tabs[tabId];
    document.getElementById('url-input').value = tab.url || '';
    updateNavButtons();
    
    if (tab.history.length > 0) {
        const token = tab.history[tab.historyIndex];
        document.getElementById('main-frame').src = 'proxy.php?t=' + token;
    } else {
        document.getElementById('main-frame').src = 'about:blank';
    }
}

function updateNavButtons() {
    const tab = tabs[activeTab];
    document.getElementById('back').disabled = tab.historyIndex <= 0;
    document.getElementById('forward').disabled = tab.historyIndex >= tab.history.length - 1;
}

async function loadURL(url) {
    const formData = new FormData();
    formData.append('url', url);
    
    try {
        const resp = await fetch('encrypt.php', { method: 'POST', body: formData });
        const data = await resp.json();
        
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        const token = data.token;
        const tab = tabs[activeTab];
        
        tab.history = tab.history.slice(0, tab.historyIndex + 1);
        tab.history.push(token);
        tab.historyIndex = tab.history.length - 1;
        tab.url = url;
        
        document.getElementById('main-frame').src = 'proxy.php?t=' + token;
        document.getElementById('url-input').value = url;
        updateNavButtons();
        
    } catch (err) {
        alert('Failed to load page');
    }
}

// Event listeners
document.getElementById('go-btn').addEventListener('click', () => {
    const url = document.getElementById('url-input').value.trim();
    if (url) loadURL(url);
});

document.getElementById('url-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        const url = e.target.value.trim();
        if (url) loadURL(url);
    }
});

document.getElementById('new-tab').addEventListener('click', () => createTab());

document.getElementById('back').addEventListener('click', () => {
    const tab = tabs[activeTab];
    if (tab.historyIndex > 0) {
        tab.historyIndex--;
        const token = tab.history[tab.historyIndex];
        document.getElementById('main-frame').src = 'proxy.php?t=' + token;
        updateNavButtons();
    }
});

document.getElementById('forward').addEventListener('click', () => {
    const tab = tabs[activeTab];
    if (tab.historyIndex < tab.history.length - 1) {
        tab.historyIndex++;
        const token = tab.history[tab.historyIndex];
        document.getElementById('main-frame').src = 'proxy.php?t=' + token;
        updateNavButtons();
    }
});

document.getElementById('refresh').addEventListener('click', () => {
    const tab = tabs[activeTab];
    if (tab.history.length > 0) {
        const token = tab.history[tab.historyIndex];
        document.getElementById('main-frame').src = 'proxy.php?t=' + token;
    }
});

// About:blank button - opens current page in new window disguised as Google Classroom
document.getElementById('about-blank-btn').addEventListener('click', () => {
    const tab = tabs[activeTab];
    if (tab.history.length === 0) {
        alert('Load a page first');
        return;
    }
    
    const token = tab.history[tab.historyIndex];
    const newWin = window.open('about:blank', '_blank');
    newWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Google Classroom</title>
            <style>
                body { margin: 0; padding: 0; overflow: hidden; }
                iframe { width: 100vw; height: 100vh; border: none; }
            </style>
        </head>
        <body>
            <iframe src="${window.location.origin}/proxy.php?t=${token}"></iframe>
        </body>
        </html>
    `);
    newWin.document.close();
});
