import { Outlet, useNavigate } from 'react-router-dom';
import { FaUpload, FaEye } from 'react-icons/fa';
import { useUsuario } from '../contexto/UsuarioContexto';

function LayoutTelegram() {
  const { usuario } = useUsuario(); 
  const navigate = useNavigate();

  const manejarRedireccion = (ruta) => {
    if (!usuario) {
      navigate('/'); // Redirige al inicio de sesión si no hay usuario
    } else {
      navigate(ruta);
    }
  };

  return (
    <div style={{ minHeight: "100vh", display: "flex", flexDirection: "column" }}>
      
      {/* Barra superior */}
      <header style={{ backgroundColor: '#1b5e20' }} className="text-white p-3 d-flex justify-content-between align-items-center">
        <h4>Mi Presentador</h4>
      </header>

      {/* Contenido */}
      <div className="d-flex flex-grow-1">
        <aside className="text-white p-3" style={{ width: '200px', backgroundColor: '#2c2f33' }}>
          <h4>Menú</h4>
          <ul className="nav flex-column">
            <li className="nav-item">
              <button
                className="nav-link text-white btn btn-link text-start"
                onClick={() => manejarRedireccion('/SubirPresentacion')}
              >
                <FaUpload className="me-2" /> Subir Presentación
              </button>
            </li>
            <li className="nav-item">
              <button
                className="nav-link text-white btn btn-link text-start"
                onClick={() => manejarRedireccion('/DesplegarPresentacion')}
              >
                <FaEye className="me-2" /> Ver Presentaciones
              </button>
            </li>
          </ul>
        </aside>

        <main className="flex-grow-1 p-4">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

export default LayoutTelegram;
