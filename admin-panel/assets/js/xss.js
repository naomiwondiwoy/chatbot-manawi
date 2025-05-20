// Fungsi untuk menghindari XSS
    function escapeHtml(text) {
      if (!text) return '';
      return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('usernameDisplay').textContent = 'Admin';
      fetchChatLogs();

      // Realtime update setiap 10 detik
      setInterval(() => {
        fetchChatLogs();
      }, 10000);
    });