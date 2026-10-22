/**
 * Luzori Support Chat — Firebase RTDB + REST fallback polling
 */
(function (window, document) {
  'use strict';

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function initials(name) {
    var parts = String(name || '?').trim().split(/\s+/).slice(0, 2);
    return parts.map(function (p) { return p.charAt(0).toUpperCase(); }).join('') || '?';
  }

  function SupportChat(opts) {
    this.opts = opts || {};
    this.viewer = opts.viewer || {};
    this.mode = opts.mode || 'admin'; // admin | center
    this.routes = opts.routes || {};
    this.csrf = opts.csrf;
    this.firebaseConfig = opts.firebaseConfig || null;
    this.firebaseToken = opts.firebaseToken || null;
    this.activeCenterId = opts.activeCenterId || null;
    this.lastMessageId = 0;
    this.knownIds = {};
    this.pollTimer = null;
    this.fbUnsub = null;
    this.$list = document.getElementById('lzChatMessages');
    this.$composer = document.getElementById('lzChatComposer');
    this.$input = document.getElementById('lzChatInput');
    this.$title = document.getElementById('lzChatTitle');
    this.$subtitle = document.getElementById('lzChatSubtitle');
    this.$empty = document.getElementById('lzChatEmpty');
    this.$contacts = document.getElementById('lzChatContacts');
    this.$search = document.getElementById('lzChatSearch');
    this.$badge = document.getElementById('lzSupportChatBadge');
  }

  SupportChat.prototype.init = function () {
    var self = this;
    if (this.$composer) {
      this.$composer.addEventListener('submit', function (e) {
        e.preventDefault();
        self.send();
      });
    }
    if (this.$input) {
      this.$input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          self.send();
        }
      });
    }
    if (this.$search && this.$contacts) {
      this.$search.addEventListener('input', function () {
        var q = (self.$search.value || '').toLowerCase().trim();
        self.$contacts.querySelectorAll('.lz-chat-item').forEach(function (el) {
          var hay = (el.getAttribute('data-search') || '').toLowerCase();
          el.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
        });
      });
    }
    if (this.$contacts) {
      this.$contacts.addEventListener('click', function (e) {
        var btn = e.target.closest('.lz-chat-item');
        if (!btn) return;
        var id = parseInt(btn.getAttribute('data-center-id'), 10);
        if (!id) return;
        self.openConversation(id, btn.getAttribute('data-name'), btn.getAttribute('data-domain'));
      });
    }

    if (this.mode === 'center' && this.activeCenterId) {
      this.openConversation(this.activeCenterId, this.opts.centerName, this.opts.centerDomain);
    } else if (this.opts.initialMessages && this.opts.initialMessages.length) {
      this.renderMessages(this.opts.initialMessages, true);
    }

    this.initFirebase();
    this.startPolling();
    this.pollUnread();
  };

  SupportChat.prototype.openConversation = function (centerId, name, domain) {
    var self = this;
    this.activeCenterId = centerId;
    this.lastMessageId = 0;
    this.knownIds = {};
    if (this.$title) this.$title.textContent = name || ('#' + centerId);
    if (this.$subtitle) this.$subtitle.textContent = domain || '';
    if (this.$contacts) {
      this.$contacts.querySelectorAll('.lz-chat-item').forEach(function (el) {
        el.classList.toggle('active', parseInt(el.getAttribute('data-center-id'), 10) === centerId);
      });
    }
    if (this.$list) this.$list.innerHTML = '';
    if (this.$empty) this.$empty.classList.remove('d-none');

    this.bindFirebaseCenter(centerId);
    this.fetchMessages(true).then(function () {
      self.scrollBottom();
    });
  };

  SupportChat.prototype.fetchMessages = function (replace) {
    var self = this;
    if (!this.activeCenterId || !this.routes.messages) return Promise.resolve();

    var url = this.routes.messages;
    if (this.mode === 'admin') {
      url = url.replace('__CENTER__', this.activeCenterId);
    }
    if (!replace && this.lastMessageId > 0) {
      url += (url.indexOf('?') >= 0 ? '&' : '?') + 'after_id=' + this.lastMessageId;
    }

    return fetch(url, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(function (data) {
      if (!data || !data.success) return;
      var msgs = data.messages || [];
      if (replace) {
        self.knownIds = {};
        self.lastMessageId = 0;
        if (self.$list) self.$list.innerHTML = '';
      }
      self.renderMessages(msgs, false);
      if (typeof data.unread_total !== 'undefined') self.setBadge(data.unread_total);
      if (typeof data.unread !== 'undefined') self.setBadge(data.unread);
    }).catch(function () {});
  };

  SupportChat.prototype.renderMessages = function (msgs, replace) {
    var self = this;
    if (!this.$list) return;
    if (replace) {
      this.$list.innerHTML = '';
      this.knownIds = {};
      this.lastMessageId = 0;
    }
    var added = 0;
    (msgs || []).forEach(function (m) {
      if (!m || !m.id || self.knownIds[m.id]) return;
      self.knownIds[m.id] = true;
      self.lastMessageId = Math.max(self.lastMessageId, parseInt(m.id, 10) || 0);
      self.$list.appendChild(self.buildBubble(m));
      added++;
    });
    if (this.$empty) {
      this.$empty.classList.toggle('d-none', this.$list.children.length > 0);
    }
    if (added) this.scrollBottom();
  };

  SupportChat.prototype.buildBubble = function (m) {
    var mine = m.sender_type === this.viewer.type && String(m.sender_id) === String(this.viewer.id);
    // Admin viewing: all admin messages align right; center messages left
    if (this.mode === 'admin') {
      mine = m.sender_type === 'admin';
    } else {
      mine = m.sender_type === 'center_user';
    }

    var wrap = document.createElement('div');
    wrap.className = 'lz-msg ' + (mine ? 'mine' : 'theirs');
    wrap.setAttribute('data-id', m.id);

    var meta = document.createElement('div');
    meta.className = 'lz-msg-meta';
    meta.innerHTML =
      '<span class="lz-msg-name">' + esc(m.sender_name) + '</span>' +
      (m.sender_role ? '<span class="lz-msg-role">' + esc(m.sender_role) + '</span>' : '') +
      '<span>' + esc(m.created_at_human || '') + '</span>';

    var bubble = document.createElement('div');
    bubble.className = 'lz-msg-bubble';
    bubble.textContent = m.body || '';

    wrap.appendChild(meta);
    wrap.appendChild(bubble);
    return wrap;
  };

  SupportChat.prototype.send = function () {
    var self = this;
    if (!this.activeCenterId || !this.$input || !this.routes.send) return;
    var body = (this.$input.value || '').trim();
    if (!body) return;

    var url = this.routes.send;
    if (this.mode === 'admin') {
      url = url.replace('__CENTER__', this.activeCenterId);
    }

    var btn = this.$composer && this.$composer.querySelector('.btn-send');
    if (btn) btn.disabled = true;
    this.$input.value = '';

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': this.csrf
      },
      credentials: 'same-origin',
      body: JSON.stringify({ body: body })
    }).then(function (r) { return r.json(); }).then(function (data) {
      if (data && data.message) {
        self.renderMessages([data.message], false);
        self.toastNew(data.message, true);
      } else {
        self.fetchMessages(false);
      }
    }).catch(function () {
      if (self.$input) self.$input.value = body;
    }).finally(function () {
      if (btn) btn.disabled = false;
      if (self.$input) self.$input.focus();
    });
  };

  SupportChat.prototype.scrollBottom = function () {
    if (!this.$list) return;
    this.$list.scrollTop = this.$list.scrollHeight;
  };

  SupportChat.prototype.setBadge = function (n) {
    n = parseInt(n, 10) || 0;
    document.querySelectorAll('.lz-support-chat-badge').forEach(function (el) {
      el.textContent = n > 99 ? '99+' : String(n);
      el.classList.toggle('d-none', n <= 0);
    });
  };

  SupportChat.prototype.toastNew = function (message, isMine) {
    if (isMine) return;
    try {
      if (window.toastr) {
        toastr.info((message.sender_name || '') + ': ' + (message.body || '').slice(0, 80), undefined, { timeOut: 4000 });
      }
      if (window.Notification && Notification.permission === 'granted') {
        new Notification(message.sender_name || 'Support', { body: (message.body || '').slice(0, 120) });
      }
    } catch (e) {}
  };

  SupportChat.prototype.pollUnread = function () {
    var self = this;
    if (!this.routes.unread) return;
    var tick = function () {
      fetch(self.routes.unread, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      }).then(function (r) { return r.json(); }).then(function (data) {
        if (data && typeof data.unread !== 'undefined') self.setBadge(data.unread);
      }).catch(function () {});
    };
    tick();
    setInterval(tick, 20000);
  };

  SupportChat.prototype.startPolling = function () {
    var self = this;
    if (this.pollTimer) clearInterval(this.pollTimer);
    this.pollTimer = setInterval(function () {
      if (!self.activeCenterId) return;
      // Fallback / sync even when Firebase is active
      self.fetchMessages(false);
    }, 8000);
  };

  SupportChat.prototype.initFirebase = function () {
    var self = this;
    if (!this.firebaseConfig || !this.firebaseConfig.databaseURL) return;
    if (typeof firebase === 'undefined') return;

    try {
      if (!firebase.apps.length) {
        firebase.initializeApp(this.firebaseConfig);
      }
      if (this.firebaseToken) {
        firebase.auth().signInWithCustomToken(this.firebaseToken).catch(function () {});
      }
      if (window.Notification && Notification.permission === 'default') {
        Notification.requestPermission().catch(function () {});
      }
      if (this.activeCenterId) this.bindFirebaseCenter(this.activeCenterId);
    } catch (e) {
      console.warn('Firebase init skipped', e);
    }
  };

  SupportChat.prototype.bindFirebaseCenter = function (centerId) {
    var self = this;
    if (typeof firebase === 'undefined' || !this.firebaseConfig) return;
    try {
      if (this.fbUnsub) {
        this.fbUnsub();
        this.fbUnsub = null;
      }
      var ref = firebase.database().ref('support_chats/' + centerId + '/messages').limitToLast(50);
      var handler = function (snap) {
        var val = snap.val();
        if (!val) return;
        // When child_added fires for each; use value for batch on first load is heavy — use child_added
      };
      var childHandler = function (snap) {
        var m = snap.val();
        if (!m) return;
        if (!m.id) m.id = snap.key;
        var wasKnown = !!self.knownIds[m.id];
        self.renderMessages([m], false);
        if (!wasKnown) {
          var mine = self.mode === 'admin' ? m.sender_type === 'admin' : m.sender_type === 'center_user';
          self.toastNew(m, mine);
          // bump badge softly
          self.pollUnread();
        }
      };
      ref.on('child_added', childHandler);
      this.fbUnsub = function () { ref.off('child_added', childHandler); };
    } catch (e) {
      console.warn('Firebase bind failed', e);
    }
  };

  window.LuzoriSupportChat = SupportChat;
})(window, document);
