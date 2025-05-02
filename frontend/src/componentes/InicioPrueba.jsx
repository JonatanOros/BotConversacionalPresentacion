// InicioPrueba.jsx
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

function InicioPrueba() {
  const [usuarioId, setUsuarioId] = useState(null);
  const navigate = useNavigate();

  const iniciarSesion = () => {
    const fakeUserId = "7438201161"; // Simulamos ID de usuario de Telegram
    setUsuarioId(fakeUserId);

    if (fakeUserId) {
      navigate('/SubirPresentacionPrueba');
    }
  };

  return (
    <div className="d-flex justify-content-center align-items-center vh-100" style={{ backgroundColor: '#28a745' }}>
      <div className="card p-5 text-center" style={{ maxWidth: '400px', width: '100%', borderRadius: '15px' }}>
        <h4 className="mb-4">Esta web te permite subir presentaciones y compartirlas por Telegram</h4>
        <button onClick={iniciarSesion} className="btn btn-success btn-lg">
          Iniciar Sesión
        </button>
      </div>
    </div>
  );
}

export default InicioPrueba;
