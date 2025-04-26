// src/App.js
import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Visor from './componentes/Visor';

function App() {
  return (
    <Routes>
      <Route path="/visor/:id" element={<Visor />} />
      <Route path="*" element={<p className="text-center mt-4">Página no encontrada</p>} />
    </Routes>
  );
}

export default App;
