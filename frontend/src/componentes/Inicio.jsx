import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import servidor from '../config'; // importamos la URL del backend

function Inicio() {
  const navigate = useNavigate();

  useEffect(() => {
    // Agrega el widget de Telegram
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://telegram.org/js/telegram-widget.js?22';
    script.setAttribute('data-telegram-login', 'PresentadorBot'); // Cambia esto por tu @Bot real
    script.setAttribute('data-size', 'large');
    script.setAttribute('data-userpic', 'false');
    script.setAttribute('data-onauth', 'onTelegramAuth');
    script.setAttribute('data-request-access', 'write');

    const container = document.getElementById('telegram-login-container');
    if (container) {
      container.innerHTML = ''; // Evitar widgets duplicados
      container.appendChild(script);
    }

    // Callback global que se ejecuta al autenticar
    window.onTelegramAuth = function(user) {
      fetch(`${servidor}/verificarLoginTelegram`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'ngrok-skip-browser-warning': 'true',
        },
        credentials: 'include',
        body: JSON.stringify(user),
      })
        .then(response => response.json())
        .then(data => {
          console.log('Respuesta del backend:', data);
          if (data.status === 'ok') {
            alert('Bienvenido ' + data.usuario.first_name);
            navigate('/SubirPresentacion');
          } else {
            alert('Error de autenticación: ' + data.mensaje);
          }
        })
        .catch(error => {
          console.error('Error de red:', error);
        });
    };
  }, [navigate]);

  return (
    <div className="d-flex justify-content-center align-items-center vh-100" style={{ backgroundColor: '#28a745' }}>
      <div className="card p-5 text-center" style={{ maxWidth: '400px', width: '100%', borderRadius: '15px' }}>
        <h4 className="mb-4">Esta web te permite subir presentaciones y compartirlas por Telegram</h4>
        <div id="telegram-login-container"></div>
      </div>
    </div>
  );
}

export default Inicio;
