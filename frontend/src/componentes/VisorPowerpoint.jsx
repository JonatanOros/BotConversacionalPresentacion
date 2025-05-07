import React, { useEffect, useState, useRef } from 'react';
import { useParams } from 'react-router-dom';
import { FaExpand } from 'react-icons/fa';
import servidor from '../config';
import '../Style/Visor.css';

function VisorPowerpoint() {
  const { id } = useParams();
  const [urlPowerPoint, setUrlPowerPoint] = useState('');
  const [loading, setLoading] = useState(true);
  const [fullscreen, setFullscreen] = useState(false);
  const [mostrarControles, setMostrarControles] = useState(false);
  const [ocultarCursor, setOcultarCursor] = useState(false);
  const visorRef = useRef(null);
  let mouseTimer;

  useEffect(() => {
    const fetchFileUrl = async () => {
      try {
        const response = await fetch(`${servidor}/obtenerPresentacion/${id}`, {
          headers: {
            'ngrok-skip-browser-warning': 'true'
          }
        });
        const data = await response.json();
        if (data.titulo) {
          //const fileUrl = `${servidor}/archivosPresentaciones/${encodeURIComponent(data.titulo)}`;
          const localFileUrl=`https://cedar-prescribed-meetings-strange.trycloudflare.com/presentacionPruebaFija/Conceptos%20de%20cultura%2C%20arte%20y%20sociedad.pptx`;
          const urlMicrosoftViewer = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(localFileUrl)}`;
          setUrlPowerPoint(urlMicrosoftViewer);
        }
      } catch (error) {
        console.error('Error al obtener el archivo:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchFileUrl();
  }, [id]);

  const toggleFullscreen = () => {
    if (!fullscreen) {
      visorRef.current.requestFullscreen?.();
    } else {
      document.exitFullscreen?.();
    }
    setFullscreen(!fullscreen);
  };

  const handleMouseMove = () => {
    setMostrarControles(true);
    setOcultarCursor(false);

    clearTimeout(mouseTimer);
    mouseTimer = setTimeout(() => {
      setMostrarControles(false);
      setOcultarCursor(true);
    }, 1500); // 1500 milisegundos = 1.5 segundos
  };

  return (
    <div
      ref={visorRef}
      className={`visor-container ${fullscreen ? 'fullscreen' : ''} ${ocultarCursor ? 'ocultar-cursor' : ''}`}
      onMouseMove={handleMouseMove}
    >
      {loading && (
        <div className="loading-spinner">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Cargando...</span>
          </div>
        </div>
      )}

      {urlPowerPoint && (
        <iframe
          src={urlPowerPoint}
          width="100%"
          height="600px"
          frameBorder="0"
          title="Visor PowerPoint"
          allowFullScreen
        ></iframe>
      )}

      {mostrarControles && (
        <button className="fullscreen-btn" onClick={toggleFullscreen}>
          <FaExpand size={20} />
        </button>
      )}
    </div>
  );
}

export default VisorPowerpoint;
