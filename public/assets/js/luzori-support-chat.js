/**
 * Luzori Support Chat — Firebase RTDB + attachments + typing + seen + sound
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

  function playNotifySound() {
    try {
      var Ctx = window.AudioContext || window.webkitAudioContext;
      if (!Ctx) return;
      if (!window.__lzAudioCtx) window.__lzAudioCtx = new Ctx();
      var ctx = window.__lzAudioCtx;
      if (ctx.state === 'suspended') ctx.resume();
      var o = ctx.createOscillator();
      var g = ctx.createGain();
      o.type = 'sine';
      o.frequency.setValueAtTime(880, ctx.currentTime);
      o.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.18);
      g.gain.setValueAtTime(0.0001, ctx.currentTime);
      g.gain.exponentialRampToValueAtTime(0.22, ctx.currentTime + 0.02);
      g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
      o.connect(g);
      g.connect(ctx.destination);
      o.start();
      o.stop(ctx.currentTime + 0.36);
    } catch (e) {}
  }

  function SupportChat(opts) {
    this.opts = opts || {};
    this.viewer = opts.viewer || {};
    this.mode = opts.mode || 'admin';
    this.routes = opts.routes || {};
    this.csrf = opts.csrf;
    this.firebaseConfig = opts.firebaseConfig || null;
    this.firebaseToken = opts.firebaseToken || null;
    this.activeCenterId = opts.activeCenterId || null;
    this.limits = opts.limits || { image_mb: 10, video_mb: 500 };
    this.i18n = opts.i18n || {};
    this.lastMessageId = 0;
    this.knownIds = {};
    this.pollTimer = null;
    this.fbUnsub = null;
    this.fbTypingUnsub = null;
    this.fbSeenUnsub = null;
    this.typingTimer = null;
    this.pendingFile = null;
    this.$list = document.getElementById('lzChatMessages');
    this.$composer = document.getElementById('lzChatComposer');
    this.$input = document.getElementById('lzChatInput');
    this.$title = document.getElementById('lzChatTitle');
    this.$subtitle = document.getElementById('lzChatSubtitle');
    this.$empty = document.getElementById('lzChatEmpty');
    this.$contacts = document.getElementById('lzChatContacts');
    this.$search = document.getElementById('lzChatSearch');
    this.$typing = document.getElementById('lzChatTyping');
    this.$file = document.getElementById('lzChatFile');
    this.$filePreview = document.getElementById('lzChatFilePreview');
    this.$shell = document.getElementById('lzChatShell') || document.querySelector('.lz-chat-shell');
  }

  SupportChat.prototype.init = function () {
    var self = this;
    document.body.classList.add('lz-chat-active');

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
      this.$input.addEventListener('input', function () {
        self.emitTyping(true);
      });
    }
    if (this.$file) {
      this.$file.addEventListener('change', function () {
        self.onFilePicked(self.$file.files && self.$file.files[0]);
      });
    }
    var clearBtn = document.getElementById('lzChatFileClear');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        self.clearFile();
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
        if (e.target.closest('.lz-center-toggle')) return;
        var id = parseInt(btn.getAttribute('data-center-id'), 10);
        if (!id) return;
        self.openConversation(id, btn.getAttribute('data-name'), btn.getAttribute('data-domain'));
      });
      this.$contacts.addEventListener('change', function (e) {
        var tog = e.target.closest('.lz-center-toggle');
        if (!tog) return;
        self.toggleCenter(tog);
      });
    }

    this.setupMobileComposer();
    this.initFirebase();

    if (this.mode === 'center' && this.activeCenterId) {
      this.openConversation(this.activeCenterId, this.opts.centerName, this.opts.centerDomain);
      if (this.opts.initialMessages) this.renderMessages(this.opts.initialMessages, true);
    }

    this.startPolling();
    this.pollUnread();

    // Unlock audio on first user gesture
    document.addEventListener('click', function unlock() {
      playNotifySound();
      document.removeEventListener('click', unlock);
    }, { once: true });
  };

  SupportChat.prototype.setupMobileComposer = function () {
    var self = this;
    if (!window.visualViewport) return;
    var apply = function () {
      if (!self.$shell) return;
      var vv = window.visualViewport;
      var offset = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
      self.$shell.style.setProperty('--lz-kb-offset', offset + 'px');
      document.documentElement.style.setProperty('--lz-vv-height', vv.height + 'px');
      if (self.$list) self.scrollBottom();
    };
    window.visualViewport.addEventListener('resize', apply);
    window.visualViewport.addEventListener('scroll', apply);
    apply();
  };

  SupportChat.prototype.onFilePicked = function (file) {
    if (!file) return;
    var isImage = /^image\//.test(file.type);
    var isVideo = /^video\//.test(file.type);
    if (!isImage && !isVideo) {
      alert(this.i18n.unsupported || 'Unsupported file');
      this.clearFile();
      return;
    }
    var maxMb = isVideo ? (this.limits.video_mb || 500) : (this.limits.image_mb || 10);
    if (file.size > maxMb * 1024 * 1024) {
      alert((this.i18n.tooLarge || 'File too large') + ' (max ' + maxMb + 'MB)');
      this.clearFile();
      return;
    }
    this.pendingFile = file;
    if (this.$filePreview) {
      this.$filePreview.classList.remove('d-none');
      this.$filePreview.querySelector('.lz-file-name').textContent =
        (isVideo ? '🎬 ' : '🖼️ ') + file.name + ' (' + Math.round(file.size / 1024 / 1024 * 10) / 10 + ' MB)';
    }
  };

  SupportChat.prototype.clearFile = function () {
    this.pendingFile = null;
    if (this.$file) this.$file.value = '';
    if (this.$filePreview) this.$filePreview.classList.add('d-none');
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
    if (this.$input) this.$input.disabled = false;
    var sendBtn = this.$composer && this.$composer.querySelector('.btn-send');
    if (sendBtn) sendBtn.disabled = false;

    this.bindFirebaseCenter(centerId);
    this.fetchMessages(true).then(function () { self.scrollBottom(); });
  };

  SupportChat.prototype.toggleCenter = function (el) {
    var id = el.getAttribute('data-center-id');
    var field = el.getAttribute('data-field');
    if (!id || !this.routes.toggle) return;
    var url = this.routes.toggle.replace('__CENTER__', id);
    var payload = {};
    payload[field] = el.checked ? 1 : 0;
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': this.csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    }).then(function (r) { return r.json(); }).then(function (data) {
      if (!data || !data.success) el.checked = !el.checked;
    }).catch(function () { el.checked = !el.checked; });
  };

  SupportChat.prototype.fetchMessages = function (replace) {
    var self = this;
    if (!this.activeCenterId || !this.routes.messages) return Promise.resolve();

    var url = this.routes.messages;
    if (this.mode === 'admin') url = url.replace('__CENTER__', this.activeCenterId);
    if (!replace && this.lastMessageId > 0) {
      url += (url.indexOf('?') >= 0 ? '&' : '?') + 'after_id=' + this.lastMessageId;
    }

    return fetch(url, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); }).then(function (data) {
      if (!data || !data.success) return;
      self.renderMessages(data.messages || [], !!replace);
      if (typeof data.unread_total !== 'undefined') self.setBadge(data.unread_total);
      if (typeof data.unread !== 'undefined') self.setBadge(data.unread);
      // Refresh seen states from full replace
      if (replace) {
        (data.messages || []).forEach(function (m) {
          if (m.is_seen) self.markBubbleSeen(m.id);
        });
      }
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
      if (!m || !m.id || self.knownIds[m.id]) {
        if (m && m.id && m.is_seen) self.markBubbleSeen(m.id);
        return;
      }
      self.knownIds[m.id] = true;
      self.lastMessageId = Math.max(self.lastMessageId, parseInt(m.id, 10) || 0);
      self.$list.appendChild(self.buildBubble(m));
      added++;
    });
    if (this.$empty) this.$empty.classList.toggle('d-none', this.$list.children.length > 0);
    if (added) this.scrollBottom();
  };

  SupportChat.prototype.isMine = function (m) {
    if (this.mode === 'admin') return m.sender_type === 'admin';
    return m.sender_type === 'center_user';
  };

  SupportChat.prototype.buildBubble = function (m) {
    var mine = this.isMine(m);
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

    if (m.attachment_type === 'image' && m.attachment_url) {
      bubble.innerHTML += '<a href="' + esc(m.attachment_url) + '" target="_blank" rel="noopener" class="lz-msg-media">' +
        '<img src="' + esc(m.attachment_url) + '" alt="' + esc(m.attachment_name || '') + '"></a>';
    } else if (m.attachment_type === 'video' && m.attachment_url) {
      bubble.innerHTML += '<video class="lz-msg-video" controls preload="metadata" src="' + esc(m.attachment_url) + '"></video>';
    }
    if (m.body) {
      var t = document.createElement('div');
      t.className = 'lz-msg-text';
      t.textContent = m.body;
      bubble.appendChild(t);
    }

    if (mine) {
      var ticks = document.createElement('span');
      ticks.className = 'lz-msg-seen' + (m.is_seen || m.seen_at ? ' is-seen' : '');
      ticks.innerHTML = m.is_seen || m.seen_at
        ? '<i class="ti ti-checks" title="Seen"></i>'
        : '<i class="ti ti-check" title="Sent"></i>';
      bubble.appendChild(ticks);
    }

    wrap.appendChild(meta);
    wrap.appendChild(bubble);
    return wrap;
  };

  SupportChat.prototype.markBubbleSeen = function (id) {
    var el = this.$list && this.$list.querySelector('.lz-msg[data-id="' + id + '"] .lz-msg-seen');
    if (!el) return;
    el.classList.add('is-seen');
    el.innerHTML = '<i class="ti ti-checks" title="Seen"></i>';
  };

  SupportChat.prototype.send = function () {
    var self = this;
    if (!this.activeCenterId || !this.routes.send) return;
    var body = (this.$input && this.$input.value || '').trim();
    if (!body && !this.pendingFile) return;

    var url = this.routes.send;
    if (this.mode === 'admin') url = url.replace('__CENTER__', this.activeCenterId);

    var fd = new FormData();
    fd.append('body', body);
    if (this.pendingFile) fd.append('attachment', this.pendingFile);

    var btn = this.$composer && this.$composer.querySelector('.btn-send');
    if (btn) btn.disabled = true;
    if (this.$input) this.$input.value = '';
    this.clearFile();
    this.emitTyping(false);

    fetch(url, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': this.csrf
      },
      credentials: 'same-origin',
      body: fd
    }).then(function (r) { return r.json(); }).then(function (data) {
      if (data && data.message) {
        self.renderMessages([data.message], false);
      } else if (data && data.message === false || data && data.success === false) {
        if (window.toastr) toastr.error(data.message || 'Error');
        else alert(data.message || 'Error');
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

  SupportChat.prototype.emitTyping = function (on) {
    var self = this;
    if (!this.activeCenterId || !this.routes.typing) return;
    clearTimeout(this.typingTimer);
    var url = this.routes.typing;
    if (this.mode === 'admin') url = url.replace('__CENTER__', this.activeCenterId);

    var post = function (typing) {
      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': self.csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ typing: typing ? 1 : 0 })
      }).catch(function () {});
    };

    if (on) {
      post(true);
      this.typingTimer = setTimeout(function () { post(false); }, 2500);
    } else {
      post(false);
    }
  };

  SupportChat.prototype.setTypingLabel = function (name) {
    if (!this.$typing) return;
    if (name) {
      this.$typing.textContent = (this.i18n.typing || ':name is typing…').replace(':name', name);
      this.$typing.classList.remove('d-none');
    } else {
      this.$typing.classList.add('d-none');
      this.$typing.textContent = '';
    }
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
    playNotifySound();
    try {
      if (window.toastr) {
        toastr.info((message.sender_name || '') + ': ' + (message.body || message.attachment_type || '').toString().slice(0, 80), undefined, { timeOut: 4000 });
      }
      if (window.Notification && Notification.permission === 'granted') {
        new Notification(message.sender_name || 'Support', { body: (message.body || message.attachment_type || '').toString().slice(0, 120) });
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
      self.fetchMessages(false);
    }, 8000);
  };

  SupportChat.prototype.initFirebase = function () {
    if (!this.firebaseConfig || !this.firebaseConfig.databaseURL) return;
    if (typeof firebase === 'undefined') return;
    try {
      if (!firebase.apps.length) firebase.initializeApp(this.firebaseConfig);
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
      if (this.fbUnsub) { this.fbUnsub(); this.fbUnsub = null; }
      if (this.fbTypingUnsub) { this.fbTypingUnsub(); this.fbTypingUnsub = null; }
      if (this.fbSeenUnsub) { this.fbSeenUnsub(); this.fbSeenUnsub = null; }

      var msgRef = firebase.database().ref('support_chats/' + centerId + '/messages').limitToLast(80);
      var childHandler = function (snap) {
        var m = snap.val();
        if (!m) return;
        if (!m.id) m.id = snap.key;
        var wasKnown = !!self.knownIds[m.id];
        self.renderMessages([m], false);
        if (!wasKnown) {
          self.toastNew(m, self.isMine(m));
          self.pollUnread();
        }
      };
      msgRef.on('child_added', childHandler);
      this.fbUnsub = function () { msgRef.off('child_added', childHandler); };

      var typingRef = firebase.database().ref('support_chats/' + centerId + '/typing');
      var typingHandler = function (snap) {
        var val = snap.val() || {};
        var names = [];
        Object.keys(val).forEach(function (k) {
          var t = val[k];
          if (!t) return;
          if (String(t.sender_type) === String(self.viewer.type) && String(t.sender_id) === String(self.viewer.id)) return;
          // stale typing (>6s)
          if (t.at && (Date.now() - Date.parse(t.at) > 6000)) return;
          if (t.sender_name) names.push(t.sender_name);
        });
        self.setTypingLabel(names.length ? names.join(', ') : null);
      };
      typingRef.on('value', typingHandler);
      this.fbTypingUnsub = function () { typingRef.off('value', typingHandler); };

      var seenRef = firebase.database().ref('support_chats/' + centerId + '/seen').limitToLast(20);
      var seenHandler = function (snap) {
        var val = snap.val();
        if (!val || !val.message_ids) return;
        (val.message_ids || []).forEach(function (id) { self.markBubbleSeen(id); });
      };
      seenRef.on('child_added', seenHandler);
      this.fbSeenUnsub = function () { seenRef.off('child_added', seenHandler); };
    } catch (e) {
      console.warn('Firebase bind failed', e);
    }
  };

  window.LuzoriSupportChat = SupportChat;
  window.lzPlaySupportSound = playNotifySound;
})(window, document);
