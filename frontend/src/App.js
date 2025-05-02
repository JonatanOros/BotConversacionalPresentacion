// App.js
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Visor from './componentes/Visor';
import Inicio from './componentes/Inicio';
import ProtectedRoute from './componentes/ProtectedRoute';
import SubirPresentacion from './componentes/SubirPresentacion';
import DesplegarPresentacion from './componentes/DesplegarPresentacion';
import Layout from './componentes/Layout';
import VisorPowerpoint from './componentes/VisorPowerpoint';
import { UsuarioProvider } from './contexto/UsuarioContexto';

function App() {
  return (
    <UsuarioProvider>
      
        <Routes>
          {/* Página pública */}
          <Route path="/" element={<Inicio />} />

          {/* Rutas protegidas */}
          <Route element={<ProtectedRoute />}>
            <Route element={<Layout />}>
              <Route path="/visor/:id" element={<Visor />} />
              <Route path="/visorpptx/:id" element={<VisorPowerpoint />} />
              <Route path="/SubirPresentacion" element={<SubirPresentacion />} />
              <Route path="/DesplegarPresentacion" element={<DesplegarPresentacion />} />
            </Route>
          </Route>

          {/* Fallback */}
          <Route path="*" element={<p className="text-center mt-4">Página no encontrada</p>} />
        </Routes>
      
    </UsuarioProvider>
  );
}

export default App;
