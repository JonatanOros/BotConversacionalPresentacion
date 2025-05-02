// src/componentes/DesplegarPresentacion.jsx
import React, { useEffect, useState } from 'react';
import servidor from '../config';
import { useUsuario } from '../contexto/UsuarioContexto';

function DesplegarPresentacion() {
  const { usuario } = useUsuario();
  const [presentaciones, setPresentaciones] = useState([]);
  const [chat, setChat] = useState([]);
  const [comando, setComando] = useState('');

  useEffect(() => {
    if (usuario?.id_telegram) {
      obtenerPresentaciones(usuario.id_telegram);
    }
  }, [usuario]);

  async function obtenerPresentaciones(usuarioId) {
    try {
      const response = await fetch(`${servidor}/presentacionesUsuario/${usuarioId}`, {
        headers: {
          'ngrok-skip-browser-warning': 'true'
        },
        credentials: 'include'
      });
      const data = await response.json();
      setPresentaciones(data);
    } catch (error) {
      console.error('Error cargando presentaciones:', error);
    }
  }

  function agregarMensaje(texto, tipo) {
    setChat(prev => [...prev, { texto, tipo }]);
  }

  async function enviarComando() {
    if (!comando.trim() || !usuario?.id_telegram) return;

    agregarMensaje(comando, 'usuario');
    setComando('');

    try {
      const response = await fetch(`${servidor}/enviarComandoDesdeWeb`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'ngrok-skip-browser-warning': 'true'
        },
        credentials: 'include',
        body: JSON.stringify({
          chatId: usuario.id_telegram,
          comando: comando,
        }),
      });

      const data = await response.json();
      agregarMensaje(data.respuesta, 'bot');
    } catch (error) {
      console.error('Error enviando comando:', error);
      agregarMensaje('Error al enviar comando.', 'bot');
    }
  }

  async function seleccionarPresentacion(presentacion) {
    if (!usuario?.id_telegram) return;

    agregarMensaje(`Seleccionó: ${presentacion.titulo}`, 'usuario');

    try {
      const response = await fetch(`${servidor}/enviarPresentacionDesdeWeb`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'ngrok-skip-browser-warning': 'true'
        },
        credentials: 'include',
        body: JSON.stringify({
          chatId: usuario.id_telegram,
          presentacionId: presentacion.id,
          nombreArchivo: presentacion.titulo,
        }),
      });

      const data = await response.json();
      agregarMensaje(
        <>
          <strong>{data.mensaje}</strong><br />
          <a href={data.url_visor} className="btn btn-sm btn-outline-primary mt-2" target="_blank" rel="noopener noreferrer">
            Abrir en visor
          </a>
        </>,
        'bot'
      );
    } catch (error) {
      console.error('Error enviando presentación:', error);
      agregarMensaje('Error al enviar la presentación.', 'bot');
    }
  }

  return (
    <div className="container bg-light p-4">
      <h1 className="mb-4">Selecciona una Presentación</h1>

      <div className="list-group mb-4">
        {presentaciones.map(p => (
          <button
            key={p.id}
            className="list-group-item list-group-item-action"
            onClick={() => seleccionarPresentacion(p)}
          >
            {p.titulo}
          </button>
        ))}
      </div>

      <hr />

      <h2 className="mt-4">Chat con el bot</h2>
      <div className="border rounded p-3 mb-3 bg-white" style={{ height: '300px', overflowY: 'auto' }}>
        {chat.map((m, index) => (
          <div key={index} className={m.tipo === 'usuario' ? 'text-end mb-2' : 'text-start mb-2'}>
            <div className={`d-inline-block p-2 rounded ${m.tipo === 'usuario' ? 'bg-success text-white' : 'bg-danger text-white'}`}>
              {m.texto}
            </div>
          </div>
        ))}
      </div>

      <div className="input-group mb-3">
        <input
          type="text"
          className="form-control"
          value={comando}
          onChange={e => setComando(e.target.value)}
          placeholder="Escribe un comando..."
        />
        <button className="btn btn-primary" onClick={enviarComando}>
          Enviar
        </button>
      </div>
    </div>
  );
}

export default DesplegarPresentacion;
