/**
 * Script de Interactividad para el Módulo de Pacientes
 * Proyecto: Consultorio Médico CExp+
 * Funcionalidad: Filtros en vivo y gestión de modales (Crear, Editar, Antecedentes, Eliminar)
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. FILTROS EN VIVO DE LA TABLA DE PACIENTES
    const inputBuscar = document.getElementById('filtroBuscarPaciente');
    const selectMedico = document.getElementById('filtroMedicoPaciente');
    const selectAlergia = document.getElementById('filtroAlergiaPaciente');
    const filasPacientes = document.querySelectorAll('#tablaPacientes tbody tr');

    function filtrarPacientes() {
        const texto = (inputBuscar?.value || '').toLowerCase().trim();
        const medico = selectMedico?.value || '';
        const alergia = selectAlergia?.value || '';

        filasPacientes.forEach(fila => {
            const contenidoFila = fila.textContent.toLowerCase();
            const medicoFila = fila.getAttribute('data-medico') || '';
            const alergiaFila = fila.getAttribute('data-alergia') || '';

            const coincideTexto = !texto || contenidoFila.includes(texto);
            const coincideMedico = !medico || medicoFila === medico;
            const coincideAlergia = !alergia || alergiaFila === alergia;

            fila.style.display = (coincideTexto && coincideMedico && coincideAlergia) ? '' : 'none';
        });
    }

    inputBuscar?.addEventListener('input', filtrarPacientes);
    selectMedico?.addEventListener('change', filtrarPacientes);
    selectAlergia?.addEventListener('change', filtrarPacientes);

    // 2. MODAL CREAR / EDITAR PACIENTE
    const modalPaciente = document.getElementById('modalPaciente');
    modalPaciente?.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('id_paciente').value = boton?.getAttribute('data-id') || '';
        document.getElementById('nombre_paciente').value = boton?.getAttribute('data-nombre') || '';
        document.getElementById('apellido_paterno').value = boton?.getAttribute('data-paterno') || '';
        document.getElementById('apellido_materno').value = boton?.getAttribute('data-materno') || '';
        document.getElementById('fecha_nacimiento').value = boton?.getAttribute('data-nacimiento') || '';
        document.getElementById('contacto').value = boton?.getAttribute('data-contacto') || '';
        document.getElementById('id_medico').value = boton?.getAttribute('data-medico') || '';
        document.getElementById('alergia').value = boton?.getAttribute('data-alergia') || '';
    });

    // 3. MODAL ANTECEDENTES MÉDICOS
    const modalAntecedentes = document.getElementById('modalAntecedentes');
    modalAntecedentes?.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        document.getElementById('antecedentes_id_paciente').value = boton?.getAttribute('data-id') || '';
        document.getElementById('antecedentes_nombre_paciente').textContent = boton?.getAttribute('data-nombre') || '';
        document.getElementById('notas_antecedentes').value = boton?.getAttribute('data-notas') || '';
    });

    // 4. MODAL CONFIRMAR ELIMINACIÓN DE PACIENTE
    const modalEliminar = document.getElementById('modalEliminarPaciente');
    modalEliminar?.addEventListener('show.bs.modal', function (event) {
        const boton = event.relatedTarget;
        const idPaciente = boton?.getAttribute('data-id') || '';
        const nombrePaciente = boton?.getAttribute('data-nombre') || '';

        document.getElementById('nombrePacienteEliminar').textContent = nombrePaciente;
        document.getElementById('btnConfirmarEliminarPaciente').href = `../controllers/pacienteController.php?accion=eliminar&id=${idPaciente}`;
    });
});
