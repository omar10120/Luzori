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
      var src = window.__lzSupportSoundUrl || '/sumsung_galaxy_s4.mp3';
      if (!window.__lzSupportAudio) {
        window.__lzSupportAudio = new Audio(src);
        window.__lzSupportAudio.preload = 'auto';
        window.__lzSupportAudio.volume = 0.9;
      }
      var audio = window.__lzSupportAudio;
      audio.muted = false;
      audio.volume = 0.9;
      audio.pause();
      audio.currentTime = 0;
      var p = audio.play();
      if (p && typeof p.catch === 'function') p.catch(function () {});
    } catch (e) {}
  }

  /** Unlock autoplay without an audible beep */
  function unlockNotifySound() {
    try {
      var src = window.__lzSupportSoundUrl || '/sumsung_galaxy_s4.mp3';
      if (!window.__lzSupportAudio) {
        window.__lzSupportAudio = new Audio(src);
        window.__lzSupportAudio.preload = 'auto';
      }
      var audio = window.__lzSupportAudio;
      audio.muted = true;
      audio.volume = 0;
      var p = audio.play();
      if (p && typeof p.then === 'function') {
        p.then(function () {
          audio.pause();
          audio.currentTime = 0;
          audio.muted = false;
          audio.volume = 0.9;
        }).catch(function () {
          audio.muted = false;
          audio.volume = 0.9;
        });
      } else {
        audio.pause();
        audio.muted = false;
        audio.volume = 0.9;
      }
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
    this.$subtitle = document.getElementById('lzChatSubtitle');
    this.$file = document.getElementById('lzChatFile');
    this.$filePreview = document.getElementById('lzChatFilePreview');
    this.$shell = document.getElementById('lzChatShell') || document.querySelector('.lz-chat-shell');
    this.$back = document.getElementById('lzChatBack');
  };

  SupportChat.prototype.isMobileChat = function () {
    return window.matchMedia && window.matchMedia('(max-width: 991.98px)').matches;
  };

  SupportChat.prototype.showThread = function (fromHistory) {
    var alreadyOpen = this.$shell && this.$shell.classList.contains('lz-chat-thread-open');
    if (this.$shell) this.$shell.classList.add('lz-chat-thread-open');
    document.body.classList.add('lz-chat-thread-open');
    if (!fromHistory && !alreadyOpen && this.mode === 'admin' && this.isMobileChat()) {
      try { history.pushState({ lzChat: 1 }, ''); } catch (e) {}
    }
  };

  SupportChat.prototype.hideThread = function (fromHistory) {
    if (this.$shell) this.$shell.classList.remove('lz-chat-thread-open');
    document.body.classList.remove('lz-chat-thread-open');
    // Clear history flag without navigating away from the page
    if (!fromHistory && this.mode === 'admin' && history.state && history.state.lzChat) {
      try { history.replaceState(null, ''); } catch (e) {}
    }
  };

  SupportChat.prototype.init = function () {
    var self = this;
    document.documentElement.classList.add('lz-chat-lock');
    document.body.classList.add('lz-chat-active');
    // Freeze body without shifting layout (avoids white gap under navbar)
    this._lockScrollY = window.scrollY || 0;
    document.body.style.top = '0px';
    window.scrollTo(0, 0);
    this.lockOuterScroll();
    if (this.mode === 'center') this.showThread(true);

    var onBack = function (e) {
      e.preventDefault();
      e.stopPropagation();
      self.hideThread();
    };
    if (this.$back) this.$back.addEventListener('click', onBack);
    // Delegation fallback (icon / delayed DOM)
    if (this.$shell) {
      this.$shell.addEventListener('click', function (e) {
        var btn = e.target.closest('#lzChatBack, .lz-chat-back');
        if (!btn) return;
        onBack(e);
      });
    }
    window.addEventListener('popstate', function () {
      if (self.mode !== 'admin') return;
      if (self.$shell && self.$shell.classList.contains('lz-chat-thread-open')) {
        self.hideThread(true);
      }
    });

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
        var id = parseInt(btn.getAttribute('data-center-id'), 10);
        if (!id) return;
        self.openConversation(id, btn.getAttribute('data-name'), btn.getAttribute('data-domain'));
      });
    }

    this.setupMobileComposer();
    this.initFirebase();

    if (this.mode === 'center' && this.activeCenterId) {
      this.openConversation(this.activeCenterId, this.opts.centerName, this.opts.centerDomain);
      if (this.opts.initialMessages) this.renderMessages(this.opts.initialMessages, true);
      this.scrollBottom();
      setTimeout(function () { self.scrollBottom(); }, 350);
    }

    this.startPolling();
    this.pollUnread();

    // Unlock audio on first user gesture (silent — no beep)
    document.addEventListener('click', function unlock() {
      unlockNotifySound();
      document.removeEventListener('click', unlock);
    }, { once: true });
  };

  SupportChat.prototype.lockOuterScroll = function () {
    var self = this;
    if (this._outerScrollLocked) return;
    this._outerScrollLocked = true;
    this._onTouchMove = function (e) {
      var t = e.target;
      if (!t || !t.closest) {
        e.preventDefault();
        return;
      }
      // Allow layout sidebar + chat scroll areas
      if (t.closest('.layout-menu, #layout-menu, .menu-inner, .ps')) return;
      var scroller = t.closest('.lz-chat-messages, .lz-chat-list, textarea, .lz-chat-composer, .lz-notif-list');
      if (!scroller) {
        e.preventDefault();
        return;
      }
      if (scroller.classList && (scroller.classList.contains('lz-chat-messages')
          || scroller.classList.contains('lz-chat-list'))) {
        var top = scroller.scrollTop;
        var max = scroller.scrollHeight - scroller.clientHeight;
        var touch = e.touches && e.touches[0];
        if (!touch) return;
        var dy = touch.clientY - (self._lastTouchY || touch.clientY);
        self._lastTouchY = touch.clientY;
        if ((top <= 0 && dy > 0) || (top >= max - 1 && dy < 0)) {
          e.preventDefault();
        }
      }
    };
    this._onTouchStart = function (e) {
      var touch = e.touches && e.touches[0];
      self._lastTouchY = touch ? touch.clientY : 0;
    };
    document.addEventListener('touchstart', this._onTouchStart, { passive: true });
    document.addEventListener('touchmove', this._onTouchMove, { passive: false });
  };

  SupportChat.prototype.setupMobileComposer = function () {
    var self = this;
    var page = document.querySelector('.lz-chat-page');
    var navEl = document.getElementById('layout-navbar')
      || document.querySelector('.layout-navbar');
    var getNavH = function () {
      if (!navEl) return 62;
      var h = Math.round(navEl.getBoundingClientRect().height);
      // Guard against inflated measurements
      if (!h || h < 40 || h > 90) return 62;
      return h;
    };
    var apply = function () {
      var navH = getNavH();
      var kb = 0;
      if (window.visualViewport) {
        var vv = window.visualViewport;
        kb = Math.max(0, Math.round(window.innerHeight - vv.height - vv.offsetTop));
      }
      document.documentElement.style.setProperty('--lz-nav-h', navH + 'px');
      document.documentElement.style.setProperty('--lz-kb-offset', kb + 'px');
      if (page) {
        page.style.setProperty('--lz-nav-h', navH + 'px');
        // Explicit geometry — no white gap, composer stays on screen
        page.style.top = navH + 'px';
        page.style.left = '0px';
        page.style.right = '0px';
        page.style.bottom = kb + 'px';
        page.style.height = (window.innerHeight - navH - kb) + 'px';
        page.style.maxHeight = (window.innerHeight - navH - kb) + 'px';
      }
      if (self.$shell) {
        self.$shell.style.height = '100%';
        self.$shell.style.minHeight = '0';
      }
      self.scrollBottom();
    };
    if (window.visualViewport) {
      window.visualViewport.addEventListener('resize', apply);
      window.visualViewport.addEventListener('scroll', apply);
    }
    window.addEventListener('resize', apply);
    apply();
    // Re-measure after layout paint / fonts
    setTimeout(apply, 50);
    setTimeout(apply, 300);
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
    window.__lzSupportActiveCenterId = centerId;
    this.lastMessageId = 0;
    this.knownIds = {};
    this.fbNotifyReady = false;
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

    if (this.mode === 'admin') this.showThread();

    this.bindFirebaseCenter(centerId);
    this.fetchMessages(true).then(function () { self.scrollBottom(); });
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
    var textEl = this.$typing.querySelector('.lz-typing-text');
    var label = this.i18n.typingNow || this.i18n.typing || 'Typing…';
    // Prefer short green status under the name (not ":name is typing")
    if (name && label.indexOf(':name') !== -1) {
      label = label.replace(':name', name);
    } else if (name && this.i18n.typingNow) {
      label = this.i18n.typingNow;
    } else if (name) {
      label = name + ' — ' + (this.i18n.typingNow || 'Typing…');
    }

    if (name) {
      if (textEl) textEl.textContent = this.i18n.typingNow || 'Typing…';
      else this.$typing.textContent = label;
      this.$typing.classList.remove('d-none');
      if (this.$subtitle) this.$subtitle.classList.add('d-none');
    } else {
      this.$typing.classList.add('d-none');
      if (textEl) textEl.textContent = '';
      else this.$typing.textContent = '';
      if (this.$subtitle) this.$subtitle.classList.remove('d-none');
    }

    // Admin contact list: show under active conversation name
    if (this.$contacts && this.activeCenterId) {
      this.$contacts.querySelectorAll('.lz-chat-item').forEach(function (el) {
        var on = name && parseInt(el.getAttribute('data-center-id'), 10) === parseInt(this.activeCenterId, 10);
        el.classList.toggle('is-typing', !!on);
      }.bind(this));
    }
  };

  SupportChat.prototype.scrollBottom = function () {
    var list = this.$list;
    if (!list) return;
    var go = function () {
      list.scrollTop = list.scrollHeight;
    };
    go();
    requestAnimationFrame(function () {
      go();
      // Images/videos may expand height after load
      list.querySelectorAll('img, video').forEach(function (media) {
        if (media.complete) return;
        media.addEventListener('load', go, { once: true });
        media.addEventListener('loadeddata', go, { once: true });
      });
    });
    setTimeout(go, 120);
    setTimeout(go, 400);
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
    // Already viewing this thread with the tab focused — no toast/sound
    if (!document.hidden && this.activeCenterId && message.center_id
        && parseInt(message.center_id, 10) === parseInt(this.activeCenterId, 10)) {
      return;
    }
    playNotifySound();
    var title = message.sender_name || 'Support';
    var body = (message.body || message.attachment_type || '').toString().slice(0, 120);
    try {
      var host = document.getElementById('lzLocalToasts');
      if (host) {
        var el = document.createElement('div');
        el.className = 'lz-local-toast';
        el.innerHTML =
          '<span class="lz-lt-icon"><i class="ti ti-messages"></i></span>' +
          '<div style="min-width:0;flex:1"><p class="lz-lt-title"></p><p class="lz-lt-body"></p></div>' +
          '<button type="button" class="lz-lt-close">&times;</button>';
        el.querySelector('.lz-lt-title').textContent = title;
        el.querySelector('.lz-lt-body').textContent = body;
        el.querySelector('.lz-lt-close').addEventListener('click', function (e) {
          e.stopPropagation();
          el.remove();
        });
        host.prepend(el);
        setTimeout(function () { if (el.parentNode) el.remove(); }, 7000);
      }
      if (window.toastr) {
        toastr.info(body, title, { timeOut: 5000 });
      }
      // Browser notification only when tab is in background
      if (document.hidden && window.Notification && Notification.permission === 'granted') {
        new Notification(title, { body: body });
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
      // child_added replays history on subscribe — suppress alerts until hydrate finishes
      self.fbNotifyReady = false;
      var childHandler = function (snap) {
        var m = snap.val();
        if (!m) return;
        if (!m.id) m.id = snap.key;
        if (!m.center_id) m.center_id = centerId;
        var wasKnown = !!self.knownIds[m.id];
        self.renderMessages([m], false);
        if (!wasKnown && self.fbNotifyReady) {
          self.toastNew(m, self.isMine(m));
          self.pollUnread();
        }
      };
      msgRef.on('child_added', childHandler);
      msgRef.once('value', function () {
        self.fbNotifyReady = true;
      });
      this.fbUnsub = function () {
        msgRef.off('child_added', childHandler);
        self.fbNotifyReady = false;
      };

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
