<?php $page_title = 'Messages - RELAPSE'; include 'header.php'; ?>

<div class="main-content">
  <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:700;margin-bottom:20px;" class="animate-fadeInUp">Messages</div>

  <!-- Send message -->
  <div class="card animate-fadeInUp delay-1" style="margin-bottom:20px;">
    <div class="card-title" style="margin-bottom:16px;">Contact Support</div>
    <div class="form-group">
      <label class="form-label">Subject</label>
      <input type="text" id="msgSubject" class="form-control" placeholder="What's this about?">
    </div>
    <div class="form-group">
      <label class="form-label">Message</label>
      <textarea id="msgBody" class="form-control" rows="4" placeholder="Describe your concern..."></textarea>
    </div>
    <button class="btn btn-primary btn-full" onclick="sendMsg()">Send Message</button>
  </div>

  <!-- Message history -->
  <div style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:12px;">Message History</div>
  <div id="msgList">
    <div class="loader"><div class="spinner"></div></div>
  </div>
</div>

<?php include 'footer.php'; ?>
<script>
async function loadMessages() {
  const res = await apiCall(`${API}/user.php?action=messages`);
  const list = document.getElementById('msgList');
  const msgs = res.messages || [];
  if (!msgs.length) {
    list.innerHTML = '<div class="empty-state"><div class="empty-icon">💬</div><div class="empty-title">No messages yet</div><div class="empty-msg">Send us a message and we\'ll get back to you soon!</div></div>';
    return;
  }
  list.innerHTML = msgs.map(m => `
    <div class="card" style="margin-bottom:12px;">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;">
        <div style="font-weight:700;">${m.subject||'(No Subject)'}</div>
        <span class="badge badge-${m.status}">${m.status}</span>
      </div>
      <div style="color:var(--text-muted);font-size:0.85rem;margin-bottom:8px;">${m.message}</div>
      ${m.reply ? `<div style="background:var(--gray-100);border-radius:var(--radius-sm);padding:10px;margin-top:8px;border-left:3px solid var(--accent);">
        <div style="font-size:0.75rem;font-weight:700;color:var(--accent);margin-bottom:4px;">RELAPSE Support</div>
        <div style="font-size:0.85rem;">${m.reply}</div>
      </div>` : ''}
      <div style="font-size:0.75rem;color:var(--text-muted);margin-top:8px;">${timeAgo(m.created_at)}</div>
    </div>`).join('');
}

async function sendMsg() {
  const subject = document.getElementById('msgSubject').value.trim();
  const message = document.getElementById('msgBody').value.trim();
  if (!message) { showToast('Please enter a message', 'error'); return; }
  const res = await apiCall(`${API}/user.php`, 'POST', { action: 'send_message', subject, message });
  if (res.success) {
    showToast('Message sent!', 'success');
    document.getElementById('msgSubject').value = '';
    document.getElementById('msgBody').value = '';
    loadMessages();
  } else showToast('Failed to send message', 'error');
}

loadMessages();
</script>
</body>
</html>