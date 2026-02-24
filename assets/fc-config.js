document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  window.calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridWeek',
    headerToolbar: {
      left: 'title',
      right: 'today,dayGridMonth,dayGridWeek,dayGridDay,prev,next' // user can switch between the two
    },
    buttonText: {
      today:    'hoy',
      month:    'mes',
      week:     'semana',
      day:      'dia',
      list:     'lista'
    },
    themeSystem: 'bootstrap5',
    selectable: true,
    locale: 'es',
    firstDay: 1,
    height: 650,
    events: '/get-events',
    eventClick: function(info) {
      const verEvento = new bootstrap.Modal(document.getElementById("verEvento"));
      const cambiarEstado = document.getElementById("estado");
      const category = `<i class=\"${info.event.extendedProps.category_icon}\" style="color: ${info.event.extendedProps.category_color};\"></i> ${info.event.extendedProps.category}`;
      document.getElementById("tarea_nombre").innerText = info.event.title;
      document.getElementById("tarea_desc").innerText = info.event.extendedProps.description;
      document.getElementById("tarea_cat").innerHTML = category;
      document.getElementById("tarea_inicio").innerText = info.event.start.toLocaleString();
      document.getElementById("tarea_fin").innerText = info.event.end !== null ? info.event.end.toLocaleString() : info.event.start.toLocaleString();

      $(cambiarEstado).attr('data-event-id', info.event.id);

      if(info.event.extendedProps.status == true)
       {
        $(cambiarEstado).html('Finalizada')
                        .removeClass().addClass("btn btn-warning");
       }

       else {
        $(cambiarEstado).html('Finalizar')
                        .removeClass().addClass("btn btn-success");
       }

      verEvento.show();
    },
    select: function(info){

      //console.log(info);
      const crearEvento = new bootstrap.Modal(document.getElementById("crearEvento"));
      var inicio = new Date(info.startStr);
      inicio.setMinutes(inicio.getMinutes() - inicio.getTimezoneOffset());
      document.getElementById('task_start').value = inicio.toISOString().slice(0,16);
      document.getElementById('task_endtime').value = inicio.toISOString().slice(0,16);
      var prueba = inicio.toISOString().slice(0,16);
      //console.log(prueba);
      //document.getElementById('cad_end').value = final.toISOString().slice(0,16);
      //document.getElementById('task_start').value = inicio;



      crearEvento.show();
    }
  });

  window.calendar.render();

  });
