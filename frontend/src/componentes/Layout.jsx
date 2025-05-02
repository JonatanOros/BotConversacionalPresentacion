// Layout.jsx
import { Outlet, useNavigate } from 'react-router-dom';
import { FaUserCircle, FaUpload, FaEye, FaSignOutAlt } from 'react-icons/fa';
import { useState, useContext } from 'react';
import { useUsuario } from '../contexto/UsuarioContexto';
import servidor from '../config';

function Layout() {
  const [mostrarMenu, setMostrarMenu] = useState(false);
  const navigate = useNavigate();
  const { usuario } = useUsuario(); 
  const { setUsuario } = useUsuario();// Vaciar el contexto al cerrar sesión

  const handleLogout = () => {
    fetch(`${servidor}/cerrarSesion`, {
      method: 'POST',
      credentials: 'include', // para que se incluya la cookie
    })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'ok') {
          setUsuario(null); // Limpiar el contexto del usuario
          navigate('/');
        } else {
          alert('Error al cerrar sesión');
        }
      })
      .catch(error => {
        console.error('Error de red:', error);
      });
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
              <button 
                className="btn btn-danger m-2" 
                onClick={handleLogout}
              >
                <FaSignOutAlt className="me-2" />
                Cerrar Sesión
              </button>
            </div>
          )}
        </div>
      </header>

      {/* Contenido */}
      <div className="d-flex flex-grow-1">
        <aside className="text-white p-3" style={{ width: '200px', backgroundColor: '#2c2f33' }}>
          <h4>Menú</h4>
          <ul className="nav flex-column">
            <li className="nav-item">
              <a className="nav-link text-white" href="/SubirPresentacion">
                <FaUpload className="me-2" /> Subir Presentación
              </a>
            </li>
            <li className="nav-item">
              <a className="nav-link text-white" href="/DesplegarPresentacion">
                <FaEye className="me-2" /> Ver Presentaciones
              </a>
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

export default Layout;
