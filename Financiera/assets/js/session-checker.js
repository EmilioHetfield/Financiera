function checkSession() {
    fetch('functions/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                window.location.href = 'pages-login.html?timeout=1';
            }
        });
}

// Verificar cada 5 minutos
setInterval(checkSession, 5 * 60 * 1000);