<?php
session_start();
include 'config.php';
include 'functions/dashboard_queries.php';
require_once 'functions/auth.php';
verificarAcceso(['master', 'vendedor']);

?>

<?php include '../Financiera/views/head.php'; ?>

<?php include '../Financiera/views/header.php'; ?>

<?php include '../Financiera/views/nav_menu.php'; ?>
<style>
    .signature-canvas {
        border: 1px solid #000;
        width: 100%;
        height: 200px;
    }
</style>
<!-- ======= Main ======= -->
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Pagare Sin Aval</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                <li class="breadcrumb-item active">Pagare sin Aval</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-8">
                <div class="container mt-5">
                    <h1 class="text-center mb-4">Formulario de Pagaré Normal</h1>
                    <form action="insert_pagare.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="nombre_cliente" class="form-label">Nombre del Cliente:</label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                        </div>
                        <div class="form-group">
                            <label for="monto" class="form-label">Monto:</label>
                            <input type="number" class="form-control" id="monto" name="monto" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>

                        <h3 class="mt-4 mb-2">Firma del Cliente:</h3>
                        <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                        <input type="hidden" id="firma_cliente" name="firma_cliente">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" onclick="clearCanvas()" class="btn btn-secondary">Limpiar</button>
                            <button type="submit" class="btn btn-primary">Guardar Pagaré</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
    function setupCanvas(canvasId) {
        const canvas = document.getElementById(canvasId);
        const ctx = canvas.getContext('2d');
        let drawing = false;

        // Set canvas size to match its container
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;

        // Touch events for mobile devices
        canvas.addEventListener('touchstart', (event) => {
            event.preventDefault();
            const rect = canvas.getBoundingClientRect();
            const x = event.touches[0].clientX - rect.left;
            const y = event.touches[0].clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
            drawing = true;
        });

        canvas.addEventListener('touchmove', (event) => {
            event.preventDefault();
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = event.touches[0].clientX - rect.left;
            const y = event.touches[0].clientY - rect.top;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';
            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        });

        canvas.addEventListener('touchend', () => drawing = false);

        // Mouse events for desktop devices
        canvas.addEventListener('mousedown', (event) => {
            const rect = canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
            drawing = true;
        });

        canvas.addEventListener('mouseup', () => drawing = false);

        canvas.addEventListener('mousemove', (event) => {
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';
            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        });
    }

    function clearCanvas() {
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('firma_cliente').value = '';
    }

    document.querySelector('form').addEventListener('submit', () => {
        const dataURL = document.getElementById('signatureCanvas').toDataURL('image/webp');
        document.getElementById('firma_cliente').value = dataURL;
    });

    setupCanvas('signatureCanvas');
</script>
</body>
<?php include '../Financiera/views/footer.php'; ?>

</html>