/**
 * Archivo: public/js/script.js
 * 1) Verifica campos vacíos y que las contraseñas coincidan (antes de enviar al servidor).
 * 2) Botón para cambiar entre tema oscuro y claro.
 * Se carga en el <head> para aplicar el tema antes de dibujar la página.
 */
'use strict';

/* ===================== TEMA OSCURO / CLARO ===================== */
const CLAVE_TEMA = 'tema-login';

/** Lee el tema guardado en el navegador; por defecto, oscuro. */
function leerTema() {
    try {
        const guardado = localStorage.getItem(CLAVE_TEMA);
        return guardado === 'claro' || guardado === 'oscuro' ? guardado : 'oscuro';
    } catch (error) {
        return 'oscuro';
    }
}

/** Aplica el tema: pone data-tema="oscuro" o "claro" en <html> y el CSS cambia los colores. */
function aplicarTema(tema) {
    document.documentElement.dataset.tema = tema;
}

aplicarTema(leerTema()); // Se aplica enseguida, antes de que se vea la página

/** Conecta el botón de la esquina para alternar el tema. */
function iniciarBotonTema() {
    const boton = document.getElementById('btn-tema');
    if (!boton) return;

    function pintar() {
        const oscuro = document.documentElement.dataset.tema === 'oscuro';
        boton.textContent = oscuro ? '☀️ Modo claro' : '🌙 Modo oscuro';
        boton.title = oscuro ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro';
        boton.setAttribute('aria-label', boton.title);
    }
    pintar();

    boton.addEventListener('click', () => {
        const nuevo = document.documentElement.dataset.tema === 'oscuro' ? 'claro' : 'oscuro';
        aplicarTema(nuevo);
        try { localStorage.setItem(CLAVE_TEMA, nuevo); } catch (error) { /* no se pudo guardar */ }
        pintar();
    });
}

/* ===================== VALIDACIÓN DE FORMULARIOS ===================== */

/** Muestra un mensaje de error debajo de un campo y lo marca en rojo. */
function mostrarError(formulario, nombreCampo, texto) {
    const aviso = formulario.querySelector('[data-error-for="' + nombreCampo + '"]');
    const campo = formulario.elements[nombreCampo];
    if (aviso) aviso.textContent = texto;
    if (campo) campo.classList.add('invalido');
}

/** Borra los errores de un intento anterior. */
function limpiarErrores(formulario) {
    formulario.querySelectorAll('.error').forEach(a => { a.textContent = ''; });
    formulario.querySelectorAll('input').forEach(c => c.classList.remove('invalido'));
}

/** Revisa el formulario. Devuelve true si todo está bien. */
function validarFormulario(formulario) {
    let valido = true;

    // 1) Campos vacíos (trim: unos espacios en blanco cuentan como vacío)
    formulario.querySelectorAll('input').forEach(campo => {
        if (campo.value.trim() === '') {
            mostrarError(formulario, campo.name, 'Este campo es obligatorio.');
            valido = false;
        }
    });

    // 2) Formato del correo
    const correo = formulario.elements['correo'];
    if (correo && correo.value.trim() !== '' && !correo.checkValidity()) {
        mostrarError(formulario, 'correo', 'Ingresá un correo válido.');
        valido = false;
    }

    // 3) Solo en el registro: largo mínimo y que las dos contraseñas coincidan
    const confirmar = formulario.elements['confirmar_password'];
    if (confirmar) {
        const clave = formulario.elements['password'].value;
        if (clave !== '' && clave.length < 8) {
            mostrarError(formulario, 'password', 'Mínimo 8 caracteres.');
            valido = false;
        }
        if (confirmar.value !== '' && clave !== confirmar.value) {
            mostrarError(formulario, 'confirmar_password', 'Las contraseñas no coinciden.');
            valido = false;
        }
    }
    return valido;
}

/** Conecta la validación al envío del formulario (el que tiene el atributo data-validar). */
function iniciarValidacion() {
    const formulario = document.querySelector('form[data-validar]');
    if (!formulario) return;

    formulario.addEventListener('submit', evento => {
        limpiarErrores(formulario);
        // Si algo está mal, se frena el envío; si todo está bien, el formulario se envía por POST al PHP.
        if (!validarFormulario(formulario)) {
            evento.preventDefault();
        }
    });
}

// Cuando la página está lista, se activan las dos funciones.
document.addEventListener('DOMContentLoaded', () => {
    iniciarBotonTema();
    iniciarValidacion();
});
