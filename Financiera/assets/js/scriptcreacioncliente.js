document.addEventListener('DOMContentLoaded', function() {
    // Inicializar canvas para firma
    const canvas = document.getElementById('firmaCanvas');
    const ctx = canvas.getContext('2d');
    let dibujando = false;
    let lastX = 0;
    let lastY = 0;

    function inicializarCanvas() {
        // Establecer el tamaño del canvas
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        
        // Configuración inicial del contexto
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }

    function iniciarDibujo(e) {
        e.preventDefault();
        dibujando = true;
        const rect = canvas.getBoundingClientRect();
        lastX = e.clientX - rect.left;
        lastY = e.clientY - rect.top;
    }

    function dibujar(e) {
        if (!dibujando) return;
        e.preventDefault();
        
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.stroke();
        
        lastX = x;
        lastY = y;
    }

    function detenerDibujo() {
        dibujando = false;
    }

    // Eventos del mouse
    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mousemove', dibujar);
    canvas.addEventListener('mouseup', detenerDibujo);
    canvas.addEventListener('mouseout', detenerDibujo);

    // Eventos touch
    canvas.addEventListener('touchstart', function(e) {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        iniciarDibujo(mouseEvent);
    });

    canvas.addEventListener('touchmove', function(e) {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        dibujar(mouseEvent);
    });

    canvas.addEventListener('touchend', detenerDibujo);

    // Función para limpiar el canvas
    window.limpiarFirma = function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    };

    // Manejo del formulario
    document.getElementById('registroClienteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Verificar si hay firma
        const firmaBase64 = canvas.toDataURL('image/png');
        console.log('¿Hay firma?:', firmaBase64.length > 100); // Debug
        
        const formData = new FormData(this);
        formData.append('firma', firmaBase64);

        // Debug - ver todos los datos que se envían
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        try {
            const response = await fetch('functions/API_Functions.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Respuesta del servidor:', data); // Debug
            
            if (data.success) {
                alert('Cliente registrado exitosamente');
                window.location.href = 'index.php';
            } else {
                alert('Error en el registro: ' + data.message);
            }
        } catch (error) {
            console.error('Error completo:', error);
            alert('Error al procesar la solicitud');
        }
    });

    // Inicializar el canvas
    inicializarCanvas();
    window.addEventListener('resize', inicializarCanvas);
});