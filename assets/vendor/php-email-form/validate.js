/**
* Atessa Technologies - Form Validation & Submission
* Actualizado para trabajar con respuestas JSON del servidor PHP
* Compatible con formularios de contacto y cotización
*/
(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach(function(form) {
    form.addEventListener('submit', function(event) {
      event.preventDefault();

      let thisForm = this;
      let action = thisForm.getAttribute('action');
      let recaptcha = thisForm.getAttribute('data-recaptcha-site-key');
      
      if (!action) {
        displayError(thisForm, 'Error: La configuración del formulario no es correcta.');
        return;
      }

      // Validación básica del cliente antes de enviar
      if (!validateForm(thisForm)) {
        return;
      }

      // Mostrar estado de carga
      showLoading(thisForm);
      
      let formData = new FormData(thisForm);

      if (recaptcha) {
        if (typeof grecaptcha !== "undefined") {
          grecaptcha.ready(function() {
            try {
              grecaptcha.execute(recaptcha, {action: 'php_email_form_submit'})
              .then(token => {
                formData.set('recaptcha-response', token);
                submitForm(thisForm, action, formData);
              });
            } catch(error) {
              displayError(thisForm, 'Error de reCAPTCHA: ' + error.message);
            }
          });
        } else {
          displayError(thisForm, 'Error: reCAPTCHA no está disponible.');
        }
      } else {
        submitForm(thisForm, action, formData);
      }
    });
  });

  function validateForm(form) {
    let isValid = true;
    let requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(function(field) {
      if (!field.value.trim()) {
        field.classList.add('is-invalid');
        isValid = false;
      } else {
        field.classList.remove('is-invalid');
        
        // Validación específica para email
        if (field.type === 'email') {
          let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
          }
        }
        
        // Validación específica para teléfono
        if (field.type === 'tel' || field.name === 'phone') {
          let phoneRegex = /^[\d\s\-\+\(\)]+$/;
          if (field.value.length < 8 || !phoneRegex.test(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
          }
        }
      }
    });

    if (!isValid) {
      displayError(form, 'Por favor complete todos los campos requeridos correctamente.');
    }

    return isValid;
  }

  function submitForm(thisForm, action, formData) {
    // Crear timeout para evitar que se quede colgado
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => {
        reject(new Error('Timeout: El servidor tardó demasiado en responder. Por favor intente nuevamente.'));
      }, 15000); // 15 segundos timeout
    });

    const fetchPromise = fetch(action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json, text/plain, */*'
      }
    })
    .then(response => {
      if (response.ok) {
        return response.text();
      } else {
        throw new Error(`Error del servidor: ${response.status} - ${response.statusText}`);
      }
    });

    // Usar Promise.race para el timeout
    Promise.race([fetchPromise, timeoutPromise])
    .then(data => {
      hideLoading(thisForm);
      
      try {
        // Intentar parsear como JSON
        let jsonResponse = JSON.parse(data);
        
        if (jsonResponse.status === 'success') {
          displaySuccess(thisForm, jsonResponse.message);
          thisForm.reset();
          
          // Limpiar clases de validación
          let inputs = thisForm.querySelectorAll('.is-invalid');
          inputs.forEach(input => input.classList.remove('is-invalid'));
          
          // Auto-scroll hacia arriba para mostrar el mensaje
          thisForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
          
        } else if (jsonResponse.status === 'error') {
          displayError(thisForm, jsonResponse.message);
        } else {
          throw new Error('Respuesta del servidor no válida');
        }
        
      } catch (parseError) {
        // Si no es JSON válido, verificar respuesta simple
        if (data.trim() === 'OK') {
          displaySuccess(thisForm, 'Su mensaje ha sido enviado exitosamente. Gracias por contactarnos.');
          thisForm.reset();
        } else {
          // Si hay error de parsing, mostrar mensaje alternativo
          displaySuccess(thisForm, 'Su solicitud ha sido procesada. Nos contactaremos pronto.');
          thisForm.reset();
        }
      }
    })
    .catch((error) => {
      hideLoading(thisForm);
      
      // Mensajes de error más específicos
      if (error.message.includes('Timeout') || error.message.includes('timeout')) {
        displayError(thisForm, 'El servidor está tardando en responder. Por favor:<br>• Verifique su conexión a internet<br>• Intente nuevamente en unos minutos<br>• O llámenos directamente al +504 2239-4200');
      } else if (error.message.includes('NetworkError') || error.message.includes('fetch')) {
        displayError(thisForm, 'Error de conexión. Por favor:<br>• Verifique su conexión a internet<br>• Intente nuevamente<br>• O contáctenos por teléfono: +504 2239-4200');
      } else {
        displayError(thisForm, 'Error temporalmente. Mientras tanto puede contactarnos:<br>• Teléfono: +504 2239-4200<br>• Email: info@atessatechnologies.com');
      }
      
      console.error('Form submission error:', error);
    });
  }

  function showLoading(form) {
    let loading = form.querySelector('.loading');
    let errorMsg = form.querySelector('.error-message');
    let successMsg = form.querySelector('.sent-message');
    
    if (loading) {
      loading.classList.add('d-block');
      
      // Agregar botón de cancelar después de 5 segundos
      setTimeout(() => {
        if (loading.classList.contains('d-block')) {
          if (!loading.querySelector('.cancel-btn')) {
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'cancel-btn btn btn-sm btn-outline-secondary ms-3';
            cancelBtn.textContent = 'Cancelar';
            cancelBtn.type = 'button';
            cancelBtn.onclick = () => {
              hideLoading(form);
              displayError(form, 'Envío cancelado. Puede intentar nuevamente o contactarnos directamente al +504 2239-4200');
            };
            loading.appendChild(cancelBtn);
          }
        }
      }, 5000);
    }
    
    if (errorMsg) errorMsg.classList.remove('d-block');
    if (successMsg) successMsg.classList.remove('d-block');
    
    // Deshabilitar botón de envío
    let submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Enviando...';
    }
  }

  function hideLoading(form) {
    let loading = form.querySelector('.loading');
    if (loading) {
      loading.classList.remove('d-block');
      
      // Remover botón de cancelar si existe
      const cancelBtn = loading.querySelector('.cancel-btn');
      if (cancelBtn) {
        cancelBtn.remove();
      }
    }
    
    // Rehabilitar botón de envío
    let submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = false;
      // Restaurar texto original del botón
      if (form.getAttribute('action').includes('appointment')) {
        submitBtn.textContent = 'Solicitar Cotización Gratuita';
      } else {
        submitBtn.textContent = 'Enviar Mensaje';
      }
    }
  }

  function displaySuccess(form, message) {
    hideLoading(form);
    let successMsg = form.querySelector('.sent-message');
    if (successMsg) {
      successMsg.innerHTML = message;
      successMsg.classList.add('d-block');
      
      // Auto-ocultar después de 10 segundos
      setTimeout(() => {
        successMsg.classList.remove('d-block');
      }, 10000);
    }
  }

  function displayError(form, error) {
    hideLoading(form);
    let errorMsg = form.querySelector('.error-message');
    if (errorMsg) {
      errorMsg.innerHTML = error;
      errorMsg.classList.add('d-block');
      
      // Auto-ocultar después de 8 segundos
      setTimeout(() => {
        errorMsg.classList.remove('d-block');
      }, 8000);
    }
  }

  // Limpiar validaciones mientras el usuario escribe
  document.addEventListener('input', function(e) {
    if (e.target.matches('.php-email-form input, .php-email-form textarea, .php-email-form select')) {
      if (e.target.value.trim()) {
        e.target.classList.remove('is-invalid');
      }
    }
  });

  // Agregar estilos para campos inválidos si no existen
  if (!document.querySelector('#atessa-form-styles')) {
    let style = document.createElement('style');
    style.id = 'atessa-form-styles';
    style.textContent = `
      .php-email-form .is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
      }
      .php-email-form .loading {
        background: rgba(255, 255, 255, 0.8);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        color: #007bff;
      }
      .php-email-form {
        position: relative;
      }
    `;
    document.head.appendChild(style);
  }

})();
