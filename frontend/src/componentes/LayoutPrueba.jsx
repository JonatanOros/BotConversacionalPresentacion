// LayoutPrueba.jsx
import { Outlet, useNavigate } from 'react-router-dom';
import { FaUserCircle, FaUpload, FaEye, FaSignOutAlt } from 'react-icons/fa';
import { useState } from 'react';

function LayoutPrueba() {
  const [mostrarMenu, setMostrarMenu] = useState(false);
  const navigate = useNavigate();

  const handleCerrarSesion = () => {
    // Simulamos cierre de sesión
    navigate('/');
  };

  return (
    <div style={{ minHeight: "100vh", display: "flex", flexDirection: "column" }}>
      
      {/* Barra superior */}
      <header style={{ backgroundColor: '#1b5e20' }} className="text-white p-3 d-flex justify-content-between align-items-center">
        <h4>Mi Presentador</h4>
        <div className="d-flex align-items-center">
          <FaUserCircle 
            size={30} 
            style={{ cursor: 'pointer', marginRight: '10px' }}
            onClick={() => setMostrarMenu(!mostrarMenu)}
          />
          {mostrarMenu && (
            <div 
              className="position-absolute bg-white text-dark rounded shadow"
              style={{ right: '10px', top: '60px', zIndex: 1000 }}
            >
              {/* Botón de cerrar sesión */}
              <button 
                className="btn btn-danger m-2" 
                onClick={handleCerrarSesion}
              >
                <FaSignOutAlt className="me-2" />
                Cerrar Sesión
              </button>
            </div>
          )}
        </div>
      </header>

      {/* Contenido de la página */}
      <div className="d-flex flex-grow-1">
        {/* Barra izquierda */}
        <aside className="text-white p-3" style={{ width: '200px', backgroundColor: '#2c2f33' }}>
          <h4>Menú</h4>
          <ul className="nav flex-column">
            <li className="nav-item">
              <a className="nav-link text-white" href="/SubirPresentacionPrueba">
                <FaUpload className="me-2" /> Subir Presentación
              </a>
            </li>
            <li className="nav-item">
              <a className="nav-link text-white" href="/DesplegarPresentacionPrueba">
                <FaEye className="me-2" /> Ver Presentaciones
              </a>
            </li>
          </ul>
        </aside>

        {/* Contenido principal */}
        <main className="flex-grow-1 p-4">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

export default LayoutPrueba;
