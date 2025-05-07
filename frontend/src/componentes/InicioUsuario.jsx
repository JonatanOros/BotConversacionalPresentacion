import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import servidor from '../config';

function InicioUsuario() {
  const [usuario, setUsuario] = useState('');
  const [clave, setContrasena] = useState('');
  const navigate = useNavigate();

  const handleSubmit = (e) => {
    e.preventDefault();

    fetch(`${servidor}/loginWeb`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'ngrok-skip-browser-warning': 'true',
      },
      credentials: 'include',
      body: JSON.stringify({ usuario, clave }),
    })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'ok') {
          alert(`Bienvenido ${usuario}`);
          navigate('/SubirPresentacion');
        } else {
          alert(`Error: ${data.mensaje}`);
        }
      })
      .catch(error => {
        console.error('Error de red:', error);
      });
  };

  return (
    <div className="d-flex justify-content-center align-items-center vh-100" style={{ backgroundColor: '#28a745' }}>
      <div className="card p-5 text-center" style={{ maxWidth: '400px', width: '100%', borderRadius: '15px' }}>
        <h4 className="mb-4">Iniciar sesión con usuario y contraseña</h4>
        <form onSubmit={handleSubmit}>
          <input
            type="text"
            className="form-control mb-3"
            placeholder="Usuario"
            value={usuario}
            onChange={(e) => setUsuario(e.target.value)}
            required
          />
          <input
            type="password"
            className="form-control mb-4"
            placeholder="Contraseña"
            value={clave}
            onChange={(e) => setContrasena(e.target.value)}
            required
          />
          <button type="submit" className="btn btn-success w-100">Entrar</button>
        </form>
      </div>
    </div>
  );
}

export default InicioUsuario;
