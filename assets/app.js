/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Funciones "Globales"

//Barras de Progreso
function actualizarBarras(progress) {
// --- Barra global ---
const progressBar = document.getElementById("progressBar");
if (progressBar) {
    const porcentaje = progress.global.percent;

    // Limpiar clases de color
    ['bg-danger','bg-warning','bg-info','bg-success'].forEach(c => progressBar.classList.remove(c));


    if (porcentaje === 0) progressBar.classList.add('bg-danger');
    else if (porcentaje <= 20) progressBar.classList.add('bg-danger');
    else if (porcentaje <= 60) progressBar.classList.add('bg-warning');
    else if (porcentaje < 100) progressBar.classList.add('bg-info');
    else progressBar.classList.add('bg-success');

    // Ancho mínimo para que se vea el color y el texto si es 0%
    progressBar.style.width = (porcentaje === 0 ? '3%' : porcentaje + "%");

    // Mostrar porcentaje centrado
    progressBar.textContent = porcentaje + "%";
    progressBar.style.textAlign = 'center';
    progressBar.style.color = '#000';
    progressBar.style.fontWeight = 'bold';
    progressBar.style.display = 'flex';
    progressBar.style.alignItems = 'center';
    progressBar.style.justifyContent = 'center';
}

// Barras por categoría
const container = document.querySelector('.progressBarVisual');
if (container) {
    container.innerHTML = ''; // limpiar contenido anterior

    progress.categories.forEach(cat => {
        const segment = document.createElement('div');
        segment.classList.add('segment', cat.color_class);
        segment.style.width = '0%';
        segment.style.position = 'relative';
        segment.style.display = 'flex';
        segment.style.alignItems = 'center';
        segment.style.justifyContent = 'center';

        // Barra interna (hechas)
        const inner = document.createElement('div');
        inner.style.width = cat.percent_interno + '%';
        inner.style.height = '100%';
        inner.style.background = 'rgba(0,0,0,0.1)';
        inner.style.position = 'absolute';
        inner.style.left = '0';
        inner.style.top = '0';

        // Nombre categoría
        const span = document.createElement('span');
        span.style.fontSize = '10px';
        span.style.lineHeight = '12px';
        span.style.zIndex = '1';
        span.textContent = cat.name;

        segment.appendChild(inner);
        segment.appendChild(span);
        container.appendChild(segment);

        // Animación al ancho real
        setTimeout(() => {
            segment.style.width = cat.percent_global + '%';
        }, 50);
    });
}
}

// Función auxiliar para llamar al endpoint
async function actualizarBarrasAjax() {
try {
const response = await fetch('/get-progress', {
  headers: {
    'Accept': 'application/json'
  }
});

if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

const progress = await response.json();
actualizarBarras(progress);

} catch (err) {
    console.error('Error al cargar las barras:', err);
  }
}

// Función para actualizar solo la barra de una tarea
function actualizarBarraTask(progress) {
    const progressBar = document.getElementById("progressBarTask");
    if (!progressBar) return;

    // Limpiar clases de color
    ['bg-danger','bg-warning','bg-info','bg-success'].forEach(c => progressBar.classList.remove(c));

    // Colores según horquillas
    if (progress === 0) progressBar.classList.add('bg-danger');
    else if (progress <= 20) progressBar.classList.add('bg-danger');
    else if (progress <= 60) progressBar.classList.add('bg-warning');
    else if (progress < 100) progressBar.classList.add('bg-info');
    else progressBar.classList.add('bg-success');

    progressBar.style.width = progress + "%";
    progressBar.textContent = progress + "%";
    progressBar.style.textAlign = 'center';
    progressBar.style.color = '#000';
    progressBar.style.fontWeight = 'bold';
    progressBar.style.display = 'flex';
    progressBar.style.alignItems = 'center';
    progressBar.style.justifyContent = 'center';
}

// Funciones de interactividad

async function toggleStatus(btn) {

  const { eventType } = btn.dataset;
  const eventId = Number(btn.dataset.eventId);

  const result = await fetchToggleStatus(eventType, eventId);

  if (!result.success) {
      ;
      return;
  }

  console.log(result.data)
  updateStatusButton(btn, result.data);
  actualizarBarrasAjax();
  // 🔥 calcular progreso directamente desde el resultado del toggle
    if (btn.dataset.context === "event") {
        const subtasks = result.data.subtasks || [];
        const done = subtasks.filter(st => st.status).length;
        const progress = subtasks.length > 0 ? (done / subtasks.length) * 100 : (result.data.status ? 100 : 0);

        actualizarBarraTask(progress);
    }

}

async function fetchToggleStatus(eventType, eventId) {

  const response = await fetch(`/set-event-status/${eventType}/${eventId}`, {
      method: 'POST',
      headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
      }
  });

  return await response.json();

}

function updateStatusButton(btn, data) {

  const icon = btn.querySelector('i');
  const context = btn.dataset.context;
  const taskId = btn.dataset.eventId

  if (context == "frontpage") {

    if (data.status == 1 ) {

        btn.innerHTML = '<i class="bi bi-x-circle"></i>';
        btn.className = 'btn btn-warning btn-sm';

    } else {

        btn.innerHTML = '<i class="bi bi-check-circle"></i>';
        btn.className = 'btn btn-success btn-sm';

    }

  }

  else if (context == "calendar") {

    if (data.status == 1) {

        btn.innerText = 'Sin Finalizar';
        btn.className = 'btn btn-warning';

    } else {

        btn.innerText = 'Finalizar';
        btn.className = 'btn btn-success';

    }

  }

  else if (context === "event") {
      console.log("EVENT CONTEXT ejecutado", btn.dataset.eventId);
      const taskId = btn.dataset.eventId;
      const eventType = btn.dataset.eventType;  // <--- obligatorio

      // actualizar solo los botones relacionados
      const relatedButtons = document.querySelectorAll(
          `[data-event-id="${taskId}"][data-event-type="${eventType}"]`
      );

      relatedButtons.forEach(b => {
          if (data.status == 1) {
              b.innerHTML = '<i class="bi bi-x-circle"></i>';
              b.className = 'btn btn-warning btn-sm';
          } else {
              b.innerHTML = '<i class="bi bi-check-circle"></i>';
              b.className = 'btn btn-success btn-sm';
          }
      });

      // determinar id de la task principal
      let mainTaskId = (eventType === "task") ? taskId : btn.dataset.mainTaskId;
      if (!mainTaskId) mainTaskId = taskId;

      // 🔥 micro-delay para asegurar que Doctrine refleja flush
      setTimeout(() => {
          fetch(`/task-progress/${mainTaskId}`, {
              method: 'GET',
              headers: {
                  'Accept': 'application/json',
                  'X-Requested-With': 'XMLHttpRequest'
              },
              cache: 'no-store'
          })
          .then(r => {
              if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
              return r.json();
          })
          .then(progressData => {
              console.log("FETCH progress recibido:", progressData.task.progress);
              const progress = progressData.task.progress || 0;
              actualizarBarraTask(progress);

              // actualizar botón de la task principal
              const taskBtn = document.querySelector(`[data-event-id="${mainTaskId}"][data-event-type="task"]`);
              if (taskBtn) {
                  if (progress === 100) {
                      taskBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
                      taskBtn.className = 'btn btn-warning btn-sm';
                  } else {
                      taskBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
                      taskBtn.className = 'btn btn-success btn-sm';
                  }
              }

              // actualizar subtasks
              progressData.subtasks.forEach(st => {
                  const subtaskBtn = document.querySelector(`[data-event-id="${st.id}"][data-event-type="subtask"]`);
                  if (subtaskBtn) {
                      if (st.status) {
                          subtaskBtn.innerHTML = '<i class="bi bi-x-circle"></i>';
                          subtaskBtn.className = 'btn btn-warning btn-sm';
                      } else {
                          subtaskBtn.innerHTML = '<i class="bi bi-check-circle"></i>';
                          subtaskBtn.className = 'btn btn-success btn-sm';
                      }
                  }
              });

          })
          .catch(err => console.error("Error al actualizar barra:", err));
      }, 20); // 20ms delay para que Doctrine refleje el flush
  }

}

function modalDelete(btn) {

const { eventType } = btn.dataset;
const eventId = Number(btn.dataset.eventId);

boton_eliminar = document.getElementById("boton_eliminar");
boton_eliminar.setAttribute('data-event-id', eventId);


}

async function finalDelete(btn) {
    const eventId = Number(btn.dataset.eventId);

    const response = await fetch(`/delete-event/${eventId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    const result = await response.json();

    setTimeout(() => {
        bootstrap.Modal.getInstance(document.getElementById('modalEliminar'))?.hide();
    }, 200);


    if (result.success) {
        const col = document.getElementById('event-' + eventId)?.closest('.col-12, .col-sm-6, .col-md-3');
        if (col) col.remove();
    }


    return result;
}

// Delegación de eventos "click"
document.addEventListener('click', async function(e) {

  const btn = e.target.closest('[data-action]');
  if (!btn) return;

  switch (btn.dataset.action) {

      case 'change-status':

          await toggleStatus(btn);

          break;

      case 'modal-delete':
          modalDelete(btn);
          break;

      case 'final-delete':
          finalDelete(btn);
          break;

  }

});


// Inicialización de las barras en páginas
document.addEventListener('DOMContentLoaded', () => {

    // --- Solo si hay barra de progreso ---
    const progressBar = document.getElementById("progressBar");
    const progressContainer = document.querySelector(".progressBarVisual");

    if (progressBar || progressContainer) {
        // Llamamos a la función AJAX que actualiza las barras
        actualizarBarrasAjax();

        // Animación de segmentos
        if (progressContainer) {
            progressContainer.querySelectorAll('.segment').forEach(segment => {
                setTimeout(() => {
                    segment.style.transition = 'width 0.5s ease';
                    // El width ya lo calculamos dentro de actualizarBarras()
                }, 50);
            });
        }
    }

});

// Inicialización de la barra de progreso cuando se visualiza una tarea
document.addEventListener('DOMContentLoaded', () => {
    console.log("init task bar");

    const progressBar = document.getElementById("progressBarTask");
    const mainTaskBtn = document.querySelector('[data-event-type="task"]');

    console.log("progressBarTask:", progressBar);
    console.log("task button:", mainTaskBtn);

    if (!progressBar || !mainTaskBtn) return;

    const mainTaskId = mainTaskBtn.dataset.eventId;

    console.log("Fetching progress for task:", mainTaskId);

    fetch(`/task-progress/${mainTaskId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        console.log("fetch response:", r);
        return r.json();
    })
    .then(progressData => {
        console.log("progressData recibido:", progressData);

        const progress = progressData.task.progress || 0;

        console.log("progress calculado:", progress);

        actualizarBarraTask(progress);
    })
    .catch(err => console.error("Error al cargar barra al inicio:", err));
});

// Formulario de crear tarea y subtareas, menudo puto chorizo XD
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('crearEvento');
    if (!modal) return; // solo ejecutar si el modal existe

    modal.addEventListener('shown.bs.modal', () => {
        const form = modal.querySelector('form');
        if (!form || form.dataset.listenerAdded) return;
        form.dataset.listenerAdded = "true";

        // --- Inicializar subtasks existentes ---
        modal.querySelectorAll('ul.subtasks li').forEach(li => addTagFormDeleteLink(li));

        // --- Botones "Añadir subtask" ---
        modal.querySelectorAll('.add_item_link').forEach(btn => {
            if (btn.dataset.listenerAdded) return;
            btn.dataset.listenerAdded = "true";
            btn.addEventListener('click', addFormToCollection);
        });

        // --- Recurrencia: mostrar/ocultar campos ---
        const recurrenteCheckbox = document.getElementById('task_recurrente');
        const recurrenceFields = document.getElementById('recurrence_fields'); // Agrupa type, interval, count
        if (recurrenteCheckbox && recurrenceFields) {
            recurrenceFields.style.display = recurrenteCheckbox.checked ? 'block' : 'none';
            recurrenteCheckbox.addEventListener('change', () => {
                recurrenceFields.style.display = recurrenteCheckbox.checked ? 'block' : 'none';
            });
        }

        // --- Submit del form ---
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // --- Recolectar subtasks ---
            const subtasks = [];
            modal.querySelectorAll('.subtasks li').forEach(li => {
                const name = li.querySelector('[name*="[name]"]')?.value;
                const description = li.querySelector('[name*="[description]"]')?.value;
                const start = li.querySelector('[name*="[start]"]')?.value;
                const endtime = li.querySelector('[name*="[endtime]"]')?.value;

                if (name) {
                    subtasks.push({ name, description, start, endTime: endtime, status: false });
                }
            });

            // --- Recolectar datos principales ---
            const data = {
                title: form.querySelector('[name="task[name]"]').value,
                description: form.querySelector('[name="task[description]"]').value,
                category: form.querySelector('[name="task[category]"]').value,
                start: form.querySelector('[name="task[start]"]').value,
                endTime: form.querySelector('[name="task[endtime]"]').value,
                subtasks: subtasks,
                recurrence: recurrenteCheckbox?.checked ? {
                    type: form.querySelector('[name="task[recurrence_type]"]').value,
                    interval: parseInt(form.querySelector('[name="task[recurrence_interval]"]').value),
                    count: parseInt(form.querySelector('[name="task[recurrence_count]"]').value)
                } : null
            };

            // --- Fetch al endpoint ---
            try {
                const resp = await fetch('/task-create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                }).then(r => r.json());

                if (resp.status === "ok") {
                    bootstrap.Modal.getInstance(modal).hide();

                    // Refrescar calendario si existe
                    if (window.calendar?.refetchEvents) {
                        window.calendar.refetchEvents();
                    }

                    document.dispatchEvent(new CustomEvent('taskCreated'));
                } else {
                    alert(resp.message);
                }
            } catch (err) {
                console.error("Error en fetch:", err);
            }
        });

        // ==============================
        // FUNCIONES AUXILIARES
        // ==============================

        // --- Botón eliminar subtask ---
        function addTagFormDeleteLink(item) {
            if (item.dataset.deleteButtonAdded) return;
            item.dataset.deleteButtonAdded = "true";

            const removeFormButton = document.createElement('button');
            removeFormButton.className = "remove btn btn-danger";
            removeFormButton.innerText = 'X';
            item.prepend(removeFormButton);

            removeFormButton.addEventListener('click', e => {
                e.preventDefault();
                item.remove();
            });
        }

        // --- Añadir subtask al collectionHolder ---
        function addFormToCollection(e) {
            const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
            const item = document.createElement('li');

            item.innerHTML = collectionHolder.dataset.prototype.replace(/__name__/g, collectionHolder.dataset.index);
            collectionHolder.appendChild(item);

            addTagFormDeleteLink(item);

            // Clonado de fechas de la task principal
            const indice = collectionHolder.dataset.index;
            const original_inicio = document.getElementById("task_start");
            const clon_inicio = document.getElementById(`task_subtasks_${indice}_start`);
            if (clon_inicio && original_inicio) clon_inicio.value = original_inicio.value;

            const original_fin = document.getElementById("task_endtime");
            const clon_fin = document.getElementById(`task_subtasks_${indice}_endtime`);
            if (clon_fin && original_fin) clon_fin.value = original_fin.value;

            collectionHolder.dataset.index++;
        }
    });
});
