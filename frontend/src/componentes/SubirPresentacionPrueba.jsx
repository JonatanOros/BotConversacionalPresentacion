// src/componentes/SubirPresentacionPrueba.jsx
import React, { useEffect, useState } from 'react';
import servidor from '../config';

function SubirPresentacionPrueba() {
  // Usamos un ID de usuario estático para las pruebas
  const usuarioId = '7438201161';  // Aquí puedes poner el ID que desees para pruebas
  const [presentaciones, setPresentaciones] = useState([]);
  const [archivo, setArchivo] = useState(null);
  const [descripcion, setDescripcion] = useState('');
  const [presentacionSeleccionada, setPresentacionSeleccionada] = useState(null);

  useEffect(() => {
    obtenerPresentaciones();
  }, []);

  async function obtenerPresentaciones() {
    try {
      const response = await fetch(`${servidor}/presentacionesUsuario/${usuarioId}`, {
        headers: {
          'ngrok-skip-browser-warning': 'true'
        }
      });
      const data = await response.json();
      setPresentaciones(data);
    } catch (error) {
      console.error('Error cargando presentaciones:', error);
    }
  }

  async function subirPresentacion(e) {
    e.preventDefault();
    if (!archivo) {
      alert('Selecciona un archivo.');
      return;
    }

    try {
      const formData = new FormData();
      formData.append('presentacion', archivo);
      formData.append('descripcion', descripcion);
      formData.append('usuario_id', usuarioId);  // El ID del usuario se mantiene estático

      const response = await fetch(`${servidor}/crearPresentacion`, {
        method: 'POST',
        body: formData,  // Ya no se manda 'credentials'
      });

      const data = await response.json();
      if (response.ok) {
        alert('Presentacion subida con exito.');
        setArchivo(null);
        setDescripcion('');
        obtenerPresentaciones(); // Recargar la tabla
      } else {
        alert(`Error: ${data.error}`);
      }
    } catch (error) {
      console.error('Error subiendo presentación:', error);
    }
  }

  async function eliminarPresentacion() {
    if (!presentacionSeleccionada) {
      alert('Selecciona una presentación para eliminar.');
      return;
    }

    const confirmar = window.confirm('¿Estás seguro de eliminar esta presentación?');
    if (!confirmar) return;

    try {
      const response = await fetch(`${servidor}/eliminarPresentacion/${presentacionSeleccionada.id}`, {
        method: 'DELETE',  // Ya no se manda 'credentials'
      });

      if (response.ok) {
        alert('Presentación eliminada.');
        setPresentacionSeleccionada(null);
        obtenerPresentaciones();
      } else {
        const data = await response.json();
        alert(`Error: ${data.error}`);
      }
    } catch (error) {
      console.error('Error eliminando presentación:', error);
    }
  }

  return (
    <div className="container bg-light p-4">
      <h1 className="mb-4">Administrar Presentaciones</h1>

      {/* Subir nueva presentación */}
      <form onSubmit={subirPresentacion} className="mb-4">
        <div className="mb-3">
          <label className="form-label">Archivo (PDF o PPTX)</label>
          <input type="file" className="form-control" accept=".pdf,.pptx" placeholder='Ningun Archivo Seleccionado' onChange={(e) => setArchivo(e.target.files[0])} />
        </div>
        <div className="mb-3">
          <label className="form-label">Descripción</label>
          <input type="text" className="form-control" value={descripcion} onChange={(e) => setDescripcion(e.target.value)} />
        </div>
        <button type="submit" className="btn btn-success">Subir Presentación</button>
      </form>

      <hr />

      {/* Tabla de presentaciones */}
      <h2 className="mb-3">Tus Presentaciones</h2>
      <table className="table table-bordered bg-white">
        <thead>
          <tr>
            <th>Título</th>
            <th>Descripción</th>
          </tr>
        </thead>
        <tbody>
          {presentaciones.map((p) => (
            <tr
              key={p.id}
              onClick={() => setPresentacionSeleccionada(p)}
              className={presentacionSeleccionada?.id === p.id ? 'table-primary' : ''}
              style={{ cursor: 'pointer' }}
            >
              <td>{p.titulo}</td>
              <td>{p.descripcion}</td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Botón eliminar */}
      <button className="btn btn-danger" onClick={eliminarPresentacion} disabled={!presentacionSeleccionada}>
        Eliminar Seleccionada
      </button>
    </div>
  );
}

export default SubirPresentacionPrueba;
