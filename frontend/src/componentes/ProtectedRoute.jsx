import { useEffect, useState } from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import servidor from '../config';
import { useUsuario } from '../contexto/UsuarioContexto';

function ProtectedRoute() {
  const [isAuthenticated, setIsAuthenticated] = useState(null);
  const { setUsuario } = useUsuario(); // Aquí accedemos al setter del contexto

  useEffect(() => {
    fetch(`${servidor}/usuarioLogueado`, { 
      headers: {
      'ngrok-skip-browser-warning': 'true',
    },
      credentials: 'include',
    })
      .then(response => {
        if (response.ok) return response.json();
        throw new Error('No autenticado');
      })
      .then(data => {
        if (data.logueado) {
          setUsuario(data.usuario); // Guarda todo el objeto usuario
          setIsAuthenticated(true);
        } else {
          setIsAuthenticated(false);
        }
      })
      .catch(() => setIsAuthenticated(false));
  }, [setUsuario]);

  if (isAuthenticated === null) return <div>Cargando...</div>;

  if (!isAuthenticated) return <Navigate to="/" replace />;

  return <Outlet />;
}

export default ProtectedRoute;