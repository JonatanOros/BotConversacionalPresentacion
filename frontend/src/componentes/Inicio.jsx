import { useEffect } from 'react';

function Inicio() {
  useEffect(() => {
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://telegram.org/js/telegram-widget.js?22';
    script.setAttribute('data-telegram-login', 'PresentadorBot');
    script.setAttribute('data-size', 'large');
    script.setAttribute('data-userpic', 'false');
    script.setAttribute('data-onauth', 'onTelegramAuth');
    script.setAttribute('data-request-access', 'write');
    document.getElementById('telegram-login-container').appendChild(script);

    // Agrega la función callback global
    window.onTelegramAuth = function(user) {
      

      // Aquí puedes enviar los datos al backend para validación
      // fetch('/verificarUsuario', { method: 'POST', body: JSON.stringify(user), ... })
      fetch('http://localhost:8080/verificarLoginTelegram', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(user),
      })
        .then(response => response.json())
        .then(data => {
          console.log('Respuesta del backend:', data);
          if (data.status === 'ok') {
            // Guardar al usuario en tu estado global o localStorage, etc.
            alert('Bienvenido ' + data.usuario.first_name);
          } else {
            alert('Error de autenticación: ' + data.mensaje);
          }
        })
        .catch(error => {
          console.error('Error de red:', error);
        });

    };
  }, []);

  return (
    <div className="container mt-5 text-center">
      <h2>Inicia sesión con Telegram</h2>
      <div id="telegram-login-container"></div>
    </div>
  );
}

export default Inicio;
