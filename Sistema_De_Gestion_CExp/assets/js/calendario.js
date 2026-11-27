document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const titleEl = document.getElementById('calendarTitle');
    const puedeEditarCitas = !!document.querySelector('[data-bs-target="#modalCita"]');
    let todosLosEventos = [];
    try { todosLosEventos = JSON.parse(calendarEl.dataset.eventos || '[]'); } catch { todosLosEventos = []; }

    const badgeClases = {
        concluida:  'bg-success-subtle text-success-emphasis',
        completada: 'bg-success-subtle text-success-emphasis',
        confirmada: 'bg-primary-subtle text-primary-emphasis',
        programada: 'bg-warning-subtle text-warning-emphasis',
        cancelada:  'bg-danger-subtle text-danger-emphasis'
    };

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'es',
        firstDay: 1,
        hiddenDays: [0, 6],
        slotMinTime: '08:00:00',
        slotMaxTime: '18:00:00',
        slotDuration: '01:00:00',
        slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        allDaySlot: false,
        headerToolbar: false,
        height: 'calc(100vh - 230px)',
        expandRows: true,
        dayHeaderFormat: { weekday: 'long' },
        events: todosLosEventos,

        datesSet: (info) => {
            if (titleEl) titleEl.textContent = info.view.title;
        },

        eventContent: (arg) => {
            const props = arg.event.extendedProps || {};
            const estado = props.estado || 'programada';
            const fechaIso = arg.event.startStr.substring(0, 16);
            const esVistaDia = arg.view.type === 'timeGridDay';
            const claseBadge = badgeClases[estado] || 'bg-secondary-subtle text-secondary-emphasis';

            const contenedor = document.createElement('article');
            contenedor.className = 'card bg-primary-subtle border-0 rounded-3 p-2 w-100 h-100 d-flex flex-column justify-content-between overflow-hidden';

            const accionesEditar = puedeEditarCitas ? `
                <menu class="d-flex gap-1 m-0 p-0 flex-shrink-0">
                    <button type="button" class="btn btn-sm p-0 border-0 text-primary" title="Editar cita"
                        data-bs-toggle="modal" data-bs-target="#modalCita"
                        data-id="${arg.event.id}" data-paciente="${props.id_paciente}"
                        data-fecha="${fechaIso}" data-estado="${estado}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm p-0 border-0 text-danger" title="Eliminar cita"
                        data-bs-toggle="modal" data-bs-target="#modalEliminarCita"
                        data-id="${arg.event.id}" data-paciente="${arg.event.title}">
                        <i class="bi bi-trash"></i>
                    </button>
                </menu>` : '';

            const botonesRapidos = (esVistaDia && puedeEditarCitas && estado !== 'concluida') ? `
                <button type="button" class="badge bg-white text-dark border border-secondary-subtle fw-normal"
                    data-estado-rapido="confirmada" data-id="${arg.event.id}" data-paciente="${props.id_paciente}" data-fecha="${fechaIso}">
                    <i class="bi bi-check-circle me-1 text-success"></i>Confirmar
                </button>
                <button type="button" class="badge bg-white text-danger border border-danger-subtle fw-normal"
                    data-estado-rapido="cancelada" data-id="${arg.event.id}" data-paciente="${props.id_paciente}" data-fecha="${fechaIso}">
                    <i class="bi bi-x-circle me-1"></i>Cancelar cita
                </button>` : '';

            contenedor.innerHTML = `
                <header class="d-flex justify-content-between align-items-start gap-1">
                    <div class="text-truncate">
                        <time class="small fw-bold text-primary-emphasis">${arg.timeText}</time>
                        <span class="small fw-semibold text-dark">${esVistaDia ? '— ' + arg.event.title : ''}</span>
                    </div>
                    ${accionesEditar}
                </header>
                <p class="small ${esVistaDia ? 'text-secondary' : 'text-dark fw-medium text-truncate'} mb-1">${esVistaDia ? (props.medico || '') : arg.event.title}</p>
                <div class="d-flex flex-wrap align-items-center gap-1 mt-auto">
                    <span class="badge ${claseBadge}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>
                    ${botonesRapidos}
                </div>
            `;
            return { domNodes: [contenedor] };
        }
    });

    calendar.render();

    // Acciones rápidas de estado (Confirmar / Cancelar cita) en Vista Día vía POST + CSRF
    calendarEl.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-estado-rapido]');
        if (!btn) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../controllers/citaController.php';
        Object.entries({
            csrf: document.querySelector('#modalCita input[name="csrf"]')?.value || '',
            id_cita: btn.dataset.id,
            id_paciente: btn.dataset.paciente,
            fecha: btn.dataset.fecha,
            estado: btn.dataset.estadoRapido
        }).forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    });

    // Navegación Anterior / Siguiente / Hoy
    Object.entries({ btnPrevWeek: 'prev', btnNextWeek: 'next', btnToday: 'today' })
        .forEach(([id, metodo]) => document.getElementById(id)?.addEventListener('click', () => calendar[metodo]()));

    // Cambio de vista: Día | Semana | Mes
    const botonesVista = document.querySelectorAll('.btn-vista-cal');
    botonesVista.forEach(btn => {
        btn.addEventListener('click', function () {
            botonesVista.forEach(b => {
                b.classList.replace('btn-primary', 'btn-outline-primary');
                b.classList.remove('active');
            });
            this.classList.replace('btn-outline-primary', 'btn-primary');
            this.classList.add('active');
            calendar.changeView(this.dataset.view);
        });
    });

    // Alternar entre Modo Lista y Modo Calendario
    const btnModoLista = document.getElementById('btnModoLista');
    const btnModoCalendario = document.getElementById('btnModoCalendario');
    const vistaListaEl = document.getElementById('vistaListaCitas');
    const navControles = document.getElementById('navControlesCalendario');

    const cambiarModo = (esCalendario) => {
        if (!btnModoLista || !btnModoCalendario || !vistaListaEl) return;
        btnModoCalendario.classList.toggle('btn-primary', esCalendario);
        btnModoCalendario.classList.toggle('btn-outline-primary', !esCalendario);
        btnModoCalendario.classList.toggle('active', esCalendario);
        btnModoLista.classList.toggle('btn-primary', !esCalendario);
        btnModoLista.classList.toggle('btn-outline-primary', esCalendario);
        btnModoLista.classList.toggle('active', !esCalendario);
        calendarEl.classList.toggle('d-none', !esCalendario);
        navControles?.classList.toggle('d-none', !esCalendario);
        vistaListaEl.classList.toggle('d-none', esCalendario);
        if (esCalendario) calendar.updateSize();
    };

    btnModoLista?.addEventListener('click', () => cambiarModo(false));
    btnModoCalendario?.addEventListener('click', () => cambiarModo(true));

    // Filtros simultáneos para Calendario y Tabla en Modo Lista
    const filtroMedico = document.getElementById('filtroMedico');
    const filtroEstado = document.getElementById('filtroEstado');
    const filtroFecha = document.getElementById('filtroFecha');
    const filtroPaciente = document.getElementById('filtroPaciente');

    function aplicarFiltros() {
        const medicoVal = filtroMedico?.value || '';
        const estadoVal = filtroEstado?.value || '';
        const fechaVal = filtroFecha?.value || '';
        const textoVal = (filtroPaciente?.value || '').toLowerCase().trim();
        const textoLimpio = textoVal.replace(/^#/, '');

        const coincide = (idCita, idPaciente, medico, estado, fecha, paciente) => {
            if (medicoVal && String(medico) !== medicoVal) return false;
            if (estadoVal && estado !== estadoVal) return false;
            if (fechaVal && fecha !== fechaVal) return false;
            if (textoVal) {
                const porNombre = (paciente || '').toLowerCase().includes(textoVal);
                const porId = textoLimpio !== '' && (String(idCita).includes(textoLimpio) || String(idPaciente).includes(textoLimpio));
                if (!porNombre && !porId) return false;
            }
            return true;
        };

        const filtrados = todosLosEventos.filter(ev => {
            const p = ev.extendedProps || {};
            return coincide(ev.id, p.id_paciente, p.id_medico, p.estado, ev.start.substring(0, 10), ev.title);
        });

        calendar.removeAllEvents();
        calendar.addEventSource(filtrados);

        if (filtrados.length > 0 && (medicoVal || estadoVal || fechaVal || textoVal)) {
            calendar.gotoDate(filtrados[0].start);
        } else if (!medicoVal && !estadoVal && !fechaVal && !textoVal) {
            calendar.today();
        }

        document.querySelectorAll('#tablaListaCitasBody tr[data-fecha]').forEach(fila => {
            const d = fila.dataset;
            fila.style.display = coincide(d.id, d.idPaciente, d.medico, d.estado, d.fecha, d.paciente) ? '' : 'none';
        });
    }

    [filtroMedico, filtroEstado, filtroFecha].forEach(el => el?.addEventListener('change', aplicarFiltros));
    ['input', 'search'].forEach(evt => filtroPaciente?.addEventListener(evt, aplicarFiltros));

    // Modal Crear / Editar Cita
    document.getElementById('modalCita')?.addEventListener('show.bs.modal', ({ relatedTarget: b }) => {
        const d = b?.dataset.id ? b.dataset : { id: '', paciente: '', fecha: '', estado: 'programada' };
        [['id_cita', 'id'], ['id_paciente', 'paciente'], ['fecha', 'fecha'], ['estado', 'estado']].forEach(([elId, key]) => {
            const el = document.getElementById(elId);
            if (el) el.value = d[key] || '';
        });
    });

    // Modal Confirmar Eliminación de Cita
    document.getElementById('modalEliminarCita')?.addEventListener('show.bs.modal', ({ relatedTarget: b }) => {
        if (!b?.dataset.id) return;
        const nombreEl = document.getElementById('nombrePacienteEliminar');
        const idEl = document.getElementById('idCitaEliminar');
        if (nombreEl) nombreEl.textContent = b.dataset.paciente || '';
        if (idEl) idEl.value = b.dataset.id;
    });
});
