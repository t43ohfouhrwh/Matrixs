let tabs = [];
let activeTab = 0;

function createTab(url = '') {
    const tabId = tabs.length;
    tabs.push({ url: url, history: [], historyIndex: -1 });
    // Add tab button
    const tabBtn = document.createElement('div');
    tabBtn.className = 'tab';
    tabBtn.dataset.tab = tabId;
    tabBtn.textContent = `Tab ${tabId + 1}`;
    tabBtn.addEventListener('click', () => switchTab(tabId));
    document.getElementById('tabs-container').insertBefore(tabBtn, document.getElementById('new-tab'));
    // Switch to new tab
    switchTab(tabId);
}

function switchTab(tabId) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
    activeTab = tabId;
    // Update URL bar
    const tab = tabs[tabId];
    document.getElementById('url-input').value = tab.url || '';
    // Update navigation buttons
    updateNavButtons();
    // If tab has content, load it
    if (tab.url) {
        loadURL(tab.url);
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
    // Encrypt via proxy
    const formData = new FormData();
    formData.append('url', url);
    const resp = await fetch('proxy.php?action=encrypt', { method: 'POST', body: formData });
    const data = await resp.json();
    if (data.error) {
        alert('Invalid URL');
        return;
    }
    const token = data.token;
    // Update tab history
    const tab = tabs[activeTab];
    tab.history = tab.history.slice(0, tab.historyIndex + 1);
    tab.history.push(token);
    tab.historyIndex = tab.history.length - 1;
    tab.url = url;
    // Load iframe
    document.getElementById('main-frame').src = 'proxy.php?t=' + token;
    updateNavButtons();
    // Keep address bar showing original URL (disguise)
    document.getElementById('url-input').value = url;
    // Use pushState to keep URL same (optional)
    history.pushState({ tab: activeTab }, '', window.location.pathname);
}

document.getElementById('url-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const url = document.getElementById('url-input').value.trim();
    if (!url) return;
    loadURL(url);
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

// About:blank button – disguised as Google
