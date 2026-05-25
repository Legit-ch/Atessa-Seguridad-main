/**
 * Cliente HTTP para consumir la API de Atessa
 * Uso: ApiClient.post('/api/quotes', formData)
 */

class ApiClient {
  static API_BASE = '/backend/public';

  /**
   * Realiza una petición GET
   */
  static async get(endpoint) {
    return this.request('GET', endpoint);
  }

  /**
   * Realiza una petición POST
   */
  static async post(endpoint, data) {
    return this.request('POST', endpoint, data);
  }

  /**
   * Petición genérica
   */
  static async request(method, endpoint, data = null) {
    try {
      const options = {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': this.getCsrfToken()
        }
      };

      if (data) {
        options.body = JSON.stringify(data);
      }

      const response = await fetch(`${this.API_BASE}${endpoint}`, options);
      const json = await response.json();

      if (!response.ok) {
        throw new Error(json.message || `HTTP ${response.status}`);
      }

      return json;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  /**
   * Obtener token CSRF del meta tag
   */
  static getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }
}

/**
 * Manejador de formularios con integración a API
 */
class FormHandler {
  constructor(formSelector, submitHandler) {
    this.form = document.querySelector(formSelector);
    this.submitHandler = submitHandler;

    if (this.form) {
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
  }

  /**
   * Manejar envío del formulario
   */
  async handleSubmit(event) {
    event.preventDefault();

    const formData = new FormData(this.form);
    const data = Object.fromEntries(formData);

    try {
      const response = await this.submitHandler(data);

      // Mostrar mensaje de éxito
      this.showAlert('Éxito: ' + response.message, 'success');

      // Resetear formulario
      this.form.reset();

      return response;
    } catch (error) {
      // Mostrar mensaje de error
      this.showAlert('Error: ' + error.message, 'error');
    }
  }

  /**
   * Mostrar alerta en el formulario
   */
  showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type}`;
    alertDiv.textContent = message;

    this.form.insertAdjacentElement('beforebegin', alertDiv);

    // Auto-remover después de 5 segundos
    setTimeout(() => alertDiv.remove(), 5000);
  }
}

/**
 * Inicializar formularios de cotización y contacto
 */
document.addEventListener('DOMContentLoaded', () => {
  // Formulario de cotización
  const quoteForm = new FormHandler('.php-email-form[id*="quote"]', async (data) => {
    return ApiClient.post('/api/quotes', data);
  });

  // Formulario de contacto
  const contactForm = new FormHandler('.php-email-form[id*="contact"]', async (data) => {
    return ApiClient.post('/api/contacts', data);
  });
});
