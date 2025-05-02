// contexto/UsuarioContexto.js
import { createContext, useContext, useState } from 'react';

// Creamos el contexto que en react es como una caja global de variables donde puedes guardar datos
const UsuarioContext = createContext();

// Este componente envuelve a las paginas .jsx y guarda el usuario en su estado
export function UsuarioProvider({ children }) {
  const [usuario, setUsuario] = useState(null); // Guarda todo el objeto usuario que se  obtuvo de
                                               //  protectRoute.jsx y que regreso el controlador

  return (
    <UsuarioContext.Provider value={{ usuario, setUsuario }}>
      {children}
    </UsuarioContext.Provider>
  );
}

// Este hook personalizado lo usas en cualquier parte para acceder a usuario
export function useUsuario() {
  return useContext(UsuarioContext);
}
