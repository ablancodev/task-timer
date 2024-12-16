// js/task-timer.js
document.addEventListener('DOMContentLoaded', function() {
    let timers = {};
    let intervals = {};

    // Función para formatear el tiempo
    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    // Event listeners para los botones de inicio/parada
    document.querySelectorAll('.tarea-item').forEach(item => {
        const tareaId = item.dataset.tareaId;
        const startBtn = item.querySelector('.start-timer');
        const stopBtn = item.querySelector('.stop-timer');
        const manualBtn = item.querySelector('.manual-time');
        const tiempoDisplay = item.querySelector('.tiempo-display');

        startBtn.addEventListener('click', () => {
            startBtn.classList.add('hidden');
            stopBtn.classList.remove('hidden');

            // Iniciar cronómetro
            let seconds = 0;
            const currentTime = tiempoDisplay.textContent;
            if (currentTime) {
                const [hours, minutes, secs] = currentTime.split(':').map(Number);
                seconds = hours * 3600 + minutes * 60 + secs;
            }

            timers[tareaId] = seconds;
            intervals[tareaId] = setInterval(() => {
                timers[tareaId]++;
                tiempoDisplay.textContent = formatTime(timers[tareaId]);
            }, 1000);
        });

        stopBtn.addEventListener('click', () => {
            stopBtn.classList.add('hidden');
            startBtn.classList.remove('hidden');

            // Detener cronómetro
            clearInterval(intervals[tareaId]);
            
            // Guardar tiempo
            guardarTiempo(tareaId, formatTime(timers[tareaId]));
        });

        manualBtn.addEventListener('click', () => {
            const tiempo = prompt('Introduce el tiempo (HH:MM:SS):', '00:00:00');
            if (tiempo && /^\d{2}:\d{2}:\d{2}$/.test(tiempo)) {
                tiempoDisplay.textContent = tiempo;
                guardarTiempo(tareaId, tiempo);
            }
        });
    });

    // Función para guardar el tiempo
    function guardarTiempo(tareaId, tiempo) {
        fetch('../wp-json/task-timer/v1/guardar-tiempo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tarea_id: tareaId,
                tiempo: tiempo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error al guardar el tiempo:', data.mensaje);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Formulario para nueva tarea
    const nuevaTareaForm = document.getElementById('nueva-tarea-form');
    if (nuevaTareaForm) {
        nuevaTareaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const titulo = document.getElementById('titulo-tarea').value;
            
            // al terminar, que recargue la página de momento
            fetch('../wp-json/task-timer/v1/nueva-tarea', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    titulo: titulo,
                    proyecto_id: document.getElementById('proyecto-id').value
                })
            }).then(() => {
                location.reload();
            });          
        });
    }

    // Formulario para nuevo proyecto
    const nuevoProyectoForm = document.getElementById('nuevo-proyecto-form');
    if ( nuevoProyectoForm) {
        nuevoProyectoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const titulo = document.getElementById('titulo-proyecto').value;
            
            fetch('../wp-json/task-timer/v1/nuevo-proyecto', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    titulo: titulo
                })
            }).then(() => {
                location.reload();
            })
        });
    }

    // eliminar proyecto
    document.querySelectorAll('.eliminar-proyecto').forEach(btn => {
        btn.addEventListener('click', function() {
            // from data-proyecto-id
            const proyectoId = this.getAttribute('data-proyecto-id');
            if ( !confirm('¿Estás seguro de que quieres eliminar este proyecto?') ) {
                return;
            }
            fetch(`../wp-json/task-timer/v1/eliminar-proyecto/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    proyecto_id: proyectoId
                })
            }).then(() => {
                location.reload();
            });
        });
    });

    // eliminar tarea
    document.querySelectorAll('.eliminar-tarea').forEach(btn => {
        btn.addEventListener('click', function() {
            // from data-tarea-id
            const tareaId = this.getAttribute('data-tarea-id');
            if ( !confirm('¿Estás seguro de que quieres eliminar esta tarea?') ) {
                return;
            }
            fetch(`../wp-json/task-timer/v1/eliminar-tarea/`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tarea_id: tareaId
                })
            }).then(() => {
                location.reload();
            });
        });
    });
});