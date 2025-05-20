// Cek apakah user sudah login
const username = localStorage.getItem('username');
if (!username) {
    alert("Anda harus login terlebih dahulu.");
    window.location.href = "login.html";
} else {
    document.getElementById('usernameDisplay').textContent = username;
}


// Logout function
function logout() {
    localStorage.removeItem('username');
    window.location.href = "login.html";
}

// Load the bottom nav HTML
fetch('partials/nav.html')
    .then(response => response.text())
    .then(data => {
    document.getElementById('bottom-nav-placeholder').innerHTML = data;
    });



