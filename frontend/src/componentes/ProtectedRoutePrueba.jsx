// ProtectedRoutePrueba.jsx
import { Navigate, Outlet } from 'react-router-dom';

function ProtectedRoutePrueba() {
  // Usamos el usuario estático como si estuviera autenticado
  const usuarioId = "7438201161";  // Aquí es donde se pone el ID del usuario simulado

  if (!usuarioId) {
    // Si no hay usuarioId, redirige al inicio
    return <Navigate to="/" />;
  }

  // Si hay usuarioId, permite acceder a la ruta protegida
  return <Outlet />;
}

export default ProtectedRoutePrueba;
