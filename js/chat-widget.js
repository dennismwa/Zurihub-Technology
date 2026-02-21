/* Zurihub - Chat Support Widget logic */

(function () {
  var STORAGE_KEY = 'zuri_chat';
  var POLL_INTERVAL = 4000;
  var pollTimer = null;

  function getState() {
    try {
      var s = localStorage.getItem(STORAGE_KEY);
      return s ? JSON.parse(s) : {};
    } catch (e) {
      return {};
    }
  }

  function setState(o) {
    var prev = getState();
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...prev, ...o }));
    } catch (e) {}
  }

  function showScreen(id) {
    var panel = document.getElementById('zuri-chat-panel');
    if (!panel) return;
    ['zuri-chat-welcome', 'zuri-chat-ticket-form', 'zuri-chat-thread'].forEach(function (sid) {
      var el = document.getElementById(sid);
      if (el) el.hidden = sid !== id;
    });
    if (id === 'zuri-chat-thread') {
      fetchMessages();
      startPolling();
    } else {
      stopPolling();
    }
  }

  function startChat() {
    var name = (document.getElementById('zuri-chat-name') || {}).value.trim();
    var email = (document.getElementById('zuri-chat-email') || {}).value.trim();
    if (!name || !email) return;
    var btn = document.getElementById('zuri-chat-start');
    if (btn) btn.disabled = true;
    fetch('/api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'start', name: name, email: email })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          setState({ conversation_id: data.conversation_id, visitor_token: data.visitor_token, name: name, email: email });
          showScreen('zuri-chat-thread');
          var inp = document.getElementById('zuri-chat-input');
          if (inp) inp.focus();
        }
      })
      .catch(function () {})
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function fetchMessages() {
    var state = getState();
    if (!state.conversation_id || !state.visitor_token) return;
    fetch('/api/chat.php?conversation_id=' + encodeURIComponent(state.conversation_id) + '&visitor_token=' + encodeURIComponent(state.visitor_token))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.messages) renderMessages(data.messages);
      })
      .catch(function () {});
  }

  function renderMessages(messages) {
    var container = document.getElementById('zuri-chat-messages');
    if (!container) return;
    container.innerHTML = messages.map(function (m) {
      var sender = m.sender_type === 'visitor' ? 'visitor' : 'staff';
      var time = m.created_at ? new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
      return '<div class="zuri-chat-msg ' + sender + '">' +
        escapeHtml(m.message) +
        '<div class="zuri-chat-msg-time">' + escapeHtml(time) + '</div></div>';
    }).join('');
    container.scrollTop = container.scrollHeight;
  }

  function escapeHtml(s) {
    if (!s) return '';
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
  }

  function sendMessage() {
    var inp = document.getElementById('zuri-chat-input');
    var msg = (inp && inp.value.trim()) || '';
    if (!msg) return;
    var state = getState();
    if (!state.conversation_id || !state.visitor_token) return;
    var btn = document.getElementById('zuri-chat-send');
    if (btn) btn.disabled = true;
    fetch('/api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'message',
        conversation_id: state.conversation_id,
        visitor_token: state.visitor_token,
        message: msg
      })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success && data.message) {
          var container = document.getElementById('zuri-chat-messages');
          if (container) {
            var div = document.createElement('div');
            div.className = 'zuri-chat-msg visitor';
            div.innerHTML = escapeHtml(data.message.message) +
              '<div class="zuri-chat-msg-time">' + (data.message.created_at ? new Date(data.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '') + '</div>';
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
          }
          if (inp) inp.value = '';
        }
      })
      .catch(function () {})
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function startPolling() {
    stopPolling();
    pollTimer = setInterval(fetchMessages, POLL_INTERVAL);
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function submitTicket() {
    var subject = (document.getElementById('zuri-ticket-subject') || {}).value.trim();
    var message = (document.getElementById('zuri-ticket-message') || {}).value.trim();
    var name = (document.getElementById('zuri-ticket-name') || {}).value.trim();
    var email = (document.getElementById('zuri-ticket-email') || {}).value.trim();
    if (!subject || !message || !name || !email) return;
    var btn = document.getElementById('zuri-ticket-submit');
    if (btn) btn.disabled = true;
    var payload = { subject: subject, message: message, name: name, email: email };
    var state = getState();
    if (state.conversation_id) payload.conversation_id = state.conversation_id;
    fetch('/api/create-ticket.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          alert('Ticket created: ' + (data.ticket_ref || '') + '. We will get back to you soon.');
          document.getElementById('zuri-ticket-subject').value = '';
          document.getElementById('zuri-ticket-message').value = '';
          document.getElementById('zuri-ticket-name').value = '';
          document.getElementById('zuri-ticket-email').value = '';
          showScreen('zuri-chat-welcome');
        } else {
          alert(data.error || 'Could not create ticket.');
        }
      })
      .catch(function () { alert('Something went wrong.'); })
      .finally(function () {
        if (btn) btn.disabled = false;
      });
  }

  function adjustWhatsAppPosition() {
    var whatsappBtn = document.querySelector('.whatsapp-float');
    var chatWidget = document.getElementById('zuri-chat-widget');
    var backToTop = document.getElementById('backToTop');
    
    if (whatsappBtn && chatWidget) {
      var isMobile = window.innerWidth <= 480;
      var isVerySmall = window.innerWidth <= 360;
      
      if (isVerySmall) {
        whatsappBtn.style.bottom = '72px';
        if (backToTop) backToTop.style.bottom = '130px';
      } else if (isMobile) {
        whatsappBtn.style.bottom = '76px';
        if (backToTop) backToTop.style.bottom = '140px';
      } else {
        whatsappBtn.style.bottom = '88px';
        if (backToTop) backToTop.style.bottom = '152px';
      }
    }
  }

  function init() {
    var root = document.getElementById('zuri-chat-widget');
    if (!root) return;
    
    // Adjust WhatsApp button position
    adjustWhatsAppPosition();
    window.addEventListener('resize', adjustWhatsAppPosition);

    var toggle = document.getElementById('zuri-chat-toggle');
    var panel = document.getElementById('zuri-chat-panel');
    var closeBtn = document.getElementById('zuri-chat-close');
    var state = getState();

    if (toggle) {
      toggle.addEventListener('click', function () {
        var open = !panel.hidden;
        panel.hidden = open;
        if (!open) {
          if (state.conversation_id && state.visitor_token) {
            showScreen('zuri-chat-thread');
          } else {
            showScreen('zuri-chat-welcome');
          }
        } else {
          stopPolling();
        }
      });
    }
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        panel.hidden = true;
        stopPolling();
      });
    }

    var startBtn = document.getElementById('zuri-chat-start');
    if (startBtn) startBtn.addEventListener('click', startChat);

    var showTicket = document.getElementById('zuri-chat-show-ticket');
    if (showTicket) {
      showTicket.addEventListener('click', function () {
        showScreen('zuri-chat-ticket-form');
        var name = document.getElementById('zuri-ticket-name');
        var email = document.getElementById('zuri-ticket-email');
        if (state.name && name) name.value = state.name;
        if (state.email && email) email.value = state.email;
      });
    }

    var ticketBack = document.getElementById('zuri-ticket-back');
    if (ticketBack) ticketBack.addEventListener('click', function () { showScreen('zuri-chat-welcome'); });

    var ticketSubmit = document.getElementById('zuri-ticket-submit');
    if (ticketSubmit) ticketSubmit.addEventListener('click', submitTicket);

    var sendBtn = document.getElementById('zuri-chat-send');
    var chatInput = document.getElementById('zuri-chat-input');
    if (sendBtn) sendBtn.addEventListener('click', sendMessage);
    if (chatInput) {
      chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendMessage();
        }
      });
    }

    var createTicketFromChat = document.getElementById('zuri-chat-create-ticket-from-chat');
    if (createTicketFromChat) {
      createTicketFromChat.addEventListener('click', function () {
        showScreen('zuri-chat-ticket-form');
        document.getElementById('zuri-ticket-name').value = state.name || '';
        document.getElementById('zuri-ticket-email').value = state.email || '';
      });
    }

    if (state.conversation_id && state.visitor_token) {
      var unreadDot = document.getElementById('zuri-chat-unread-dot');
      if (unreadDot) unreadDot.classList.add('is-visible');
    }
  }

  window.initZuriChatWidget = init;
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
