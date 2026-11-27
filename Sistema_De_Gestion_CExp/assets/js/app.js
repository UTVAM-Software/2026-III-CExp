class GestorModales {
    constructor() {
        const colsReceta = ['medicamento', 'dosis', 'frecuencia', 'duracion'];

        this.handlers = {
            modalPaciente: (d) => {
                const edicion = Boolean(d.id);
                this.valores({
                    pac_id: edicion ? d.id : '',
                    pac_nombre: edicion ? d.nombre : '',
                    pac_paterno: edicion ? d.paterno : '',
                    pac_materno: edicion ? d.materno : '',
                    pac_nacimiento: edicion ? d.nacimiento : '',
                    pac_contacto: edicion ? d.contacto : '',
                    pac_medico: edicion ? d.medico : '',
                    pac_alergia: edicion ? d.alergia : ''
                });
                this.textos({
                    modalPacienteTitulo: edicion ? `Editar Paciente #${d.id}` : 'Registrar Nuevo Paciente',
                    modalPacienteBtnGuardar: edicion ? 'Actualizar Paciente' : 'Guardar Paciente'
                });
            },

            modalAntecedentes: (d) => {
                if (!d.id) return;
                this.valores({ ant_id_paciente: d.id, ant_notas: d.antecedentes });
                this.textos({ ant_nombre_paciente: d.nombre });
            },

            modalEliminarPaciente: (d) => {
                if (!d.id) return;
                this.valores({ elim_id_paciente: d.id });
                this.textos({ elim_nombre_paciente: d.nombre });
            },

            modalReceta: (d, b) => {
                if (!b) return;
                this.textos({
                    modalRecetaTitulo: `Consulta #${d.id} • Receta #${d.receta}`,
                    modalRecetaMeta: `Paciente: ${d.paciente} | Fecha: ${d.fecha}`,
                    modalRecetaDiagnostico: `Diagnóstico: ${d.diagnostico}`
                });
                this.llenarTabla('modalRecetaDetalles', d.detalles, colsReceta);
            },

            modalHistorial: (d, b) => {
                if (!b) return;
                this.textos({
                    modalHistorialTitulo: `Consulta #${d.id} • Receta #${d.receta}`,
                    histTemp: d.temp,
                    histSpo2: d.spo2,
                    histFr: d.fr,
                    histPulso: d.pulso,
                    modalHistorialDiagnostico: `Diagnóstico: ${d.diagnostico}`
                });
                this.llenarTabla('modalHistorialDetalles', d.detalles, colsReceta);
            },

            modalUsuario: (d) => {
                this.valores({
                    id_usuario: d.id,
                    nombre_completo: d.nombre,
                    username: d.username,
                    email: d.email,
                    id_grupo: d.grupo,
                    estado_usuario: d.estado || 'activo',
                    contrasena: ''
                });
                const pass = document.getElementById('contrasena');
                if (pass) pass.required = !d.id;
            },

            modalRol: (d) => {
                this.valores({ id_grupo_modal: d.id, nombre_grupo: d.nombre });
                const activos = this.parseJson(d.permisos);
                document.querySelectorAll('.check-permiso-rol').forEach(chk => {
                    chk.checked = activos.includes(parseInt(chk.value, 10));
                });
            },

            modalEliminarUsuario: (d) => {
                this.valores({ idUsuarioEliminar: d.id });
                this.textos({ nombreUsuarioEliminar: d.nombre });
            }
        };
    }

    valores(mapa) {
        Object.entries(mapa).forEach(([id, val]) => {
            const el = document.getElementById(id);
            if (el) el.value = val ?? '';
        });
    }

    textos(mapa) {
        Object.entries(mapa).forEach(([id, txt]) => {
            const el = document.getElementById(id);
            if (el) el.textContent = txt ?? '';
        });
    }

    parseJson(raw) {
        try { return JSON.parse(raw || '[]'); } catch { return []; }
    }

    llenarTabla(tbodyId, raw, columnas) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = '';
        this.parseJson(raw).forEach(fila => {
            const tr = document.createElement('tr');
            columnas.forEach(col => {
                const td = document.createElement('td');
                td.textContent = fila[col] || '';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    iniciar() {
        document.addEventListener('show.bs.modal', ({ target: modal, relatedTarget: boton }) => {
            this.handlers[modal.id]?.(boton?.dataset || {}, boton);
        });
    }
}

class FiltroTabla {
    constructor({ tablaId, buscarId, selectIds = [], limpiarId = '', coincide }) {
        this.tabla = document.getElementById(tablaId);
        if (!this.tabla) return;

        this.buscar = document.getElementById(buscarId);
        this.selects = selectIds.map(id => document.getElementById(id)).filter(Boolean);
        this.coincide = coincide;

        ['input', 'search'].forEach(ev => this.buscar?.addEventListener(ev, () => this.filtrar()));
        this.selects.forEach(s => s.addEventListener('change', () => this.filtrar()));
        if (limpiarId) {
            document.getElementById(limpiarId)?.addEventListener('click', () => this.limpiar());
        }
    }

    filtrar() {
        const texto = (this.buscar?.value || '').toLowerCase().trim();
        const vals = this.selects.map(s => (s.value || '').toLowerCase().trim());
        this.tabla.querySelectorAll('tbody tr').forEach(fila => {
            fila.style.display = this.coincide(fila, texto, vals) ? '' : 'none';
        });
    }

    limpiar() {
        if (this.buscar) this.buscar.value = '';
        this.selects.forEach(s => { s.value = ''; });
        this.filtrar();
    }
}

class AppConsultorio {
    iniciar() {
        new GestorModales().iniciar();
        this.iniciarAtajoPacienteConsulta();
        this.iniciarSlotsAgenda();
        this.iniciarFiltrosTablas();
    }

    iniciarAtajoPacienteConsulta() {
        document.getElementById('id_paciente')?.addEventListener('change', function () {
            if (this.value !== '__nuevo__') return;
            this.value = '';
            const modalEl = document.getElementById('modalNuevoPacienteConsulta');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    }

    iniciarSlotsAgenda() {
        const slots = document.querySelectorAll('.btn-slot-libre');
        if (!slots.length) return;
        const inputFecha = document.getElementById('fecha_agenda');
        const selectPac = document.getElementById('id_paciente_agenda');

        slots.forEach(btn => {
            btn.addEventListener('click', () => {
                slots.forEach(b => b.classList.remove('border-primary', 'shadow-sm'));
                btn.classList.add('border-primary', 'shadow-sm');
                if (inputFecha && btn.dataset.fechaHora) inputFecha.value = btn.dataset.fechaHora;
                selectPac?.focus();
            });
        });
    }

    iniciarFiltrosTablas() {
        new FiltroTabla({
            tablaId: 'tablaUsuarios',
            buscarId: 'filtroBuscarUsuario',
            selectIds: ['filtroRolUsuario', 'filtroEstadoUsuario'],
            coincide: (fila, txt, [rol, est]) =>
                (!txt || fila.textContent.toLowerCase().includes(txt)) &&
                (!rol || (fila.dataset.rol || '').toLowerCase().trim() === rol) &&
                (!est || (fila.dataset.estado || '').toLowerCase().trim() === est)
        });

        new FiltroTabla({
            tablaId: 'tablaPacientes',
            buscarId: 'filtroBuscarPaciente',
            selectIds: ['filtroMedicoPaciente', 'filtroAlergiaPaciente'],
            limpiarId: 'btnLimpiarPacientes',
            coincide: (fila, txt, [med, aler]) =>
                (!txt || (fila.dataset.busqueda || '').includes(txt)) &&
                (!med || (fila.dataset.medico || '') === med) &&
                (!aler || (fila.dataset.alergia || '') === aler)
        });
    }
}

document.addEventListener('DOMContentLoaded', () => new AppConsultorio().iniciar());
