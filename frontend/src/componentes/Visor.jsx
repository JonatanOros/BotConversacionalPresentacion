import React, { useEffect, useState, useRef } from 'react';
import { useParams } from 'react-router-dom';
import { Document, Page, pdfjs } from 'react-pdf';
import { FaExpand, FaChevronLeft, FaChevronRight } from 'react-icons/fa';
import '../Style/Visor.css';
import servidor from '../config';

pdfjs.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.js';

function Visor() {
  const { id } = useParams();
  const [pdfBlob, setPdfBlob] = useState(null);
  const [numPages, setNumPages] = useState(null);
  const [paginaActual, setPaginaActual] = useState(1);
  const [loading, setLoading] = useState(true);
  const [fullscreen, setFullscreen] = useState(false);
  const [mostrarControles, setMostrarControles] = useState(false);
  const [mostrarCursor, setMostrarCursor] = useState(true);
  const [pageWidth, setPageWidth] = useState(800);
  const visorRef = useRef(null);
  const mouseTimer = useRef(null);

  useEffect(() => {
    const fetchFileAsBlob = async () => {
      try {
        const response = await fetch(`${servidor}/obtenerPresentacion/${id}`, {
          headers: { 'ngrok-skip-browser-warning': 'true' }
        });
        const data = await response.json();

        if (data.titulo) {
          const tituloCodificado = encodeURIComponent(btoa(data.titulo));
          const blobResponse = await fetch(`${servidor}/verArchivoBase64/${tituloCodificado}`, {
            headers: { 'ngrok-skip-browser-warning': 'true' }
          });
          const blob = await blobResponse.blob();
          setPdfBlob(blob);
        }
      } catch (error) {
        console.error('Error al obtener el archivo:', error);
      }
    };

    fetchFileAsBlob();

    const handleKeyDown = (e) => {
      if (e.key === 'ArrowRight') {
        setPaginaActual((prev) => Math.min(prev + 1, numPages));
      } else if (e.key === 'ArrowLeft') {
        setPaginaActual((prev) => Math.max(prev - 1, 1));
      }
    };

    const handleFullscreenChange = () => {
      const isFullscreen = !!document.fullscreenElement;
      setFullscreen(isFullscreen);
    };

    window.addEventListener('keydown', handleKeyDown);
    document.addEventListener('fullscreenchange', handleFullscreenChange);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
    };
  }, [id, numPages]);

  useEffect(() => {
    const updateWidth = () => {
      if (fullscreen) {
        setPageWidth(window.innerWidth * 0.95);
      } else {
        const contenedor = visorRef.current;
        if (contenedor) {
          const maxWidth = Math.min(contenedor.offsetWidth, 800);
          setPageWidth(maxWidth);
        }
      }
    };

    updateWidth();
    window.addEventListener('resize', updateWidth);
    document.addEventListener('fullscreenchange', updateWidth);

    return () => {
      window.removeEventListener('resize', updateWidth);
      document.removeEventListener('fullscreenchange', updateWidth);
    };
  }, [fullscreen]);

  const onDocumentLoadSuccess = ({ numPages }) => {
    setNumPages(numPages);
    setPaginaActual(1);
    setLoading(false);
  };

  const toggleFullscreen = () => {
    if (!fullscreen) {
      visorRef.current.requestFullscreen?.();
    } else if (document.fullscreenElement) {
      document.exitFullscreen?.();
    }
  };

  const handleMouseMove = () => {
    setMostrarControles(true);
    setMostrarCursor(true);
    clearTimeout(mouseTimer.current);
    mouseTimer.current = setTimeout(() => {
      setMostrarControles(false);
      setMostrarCursor(false);
    }, 1500);
  };

  return (
    <div
      ref={visorRef}
      className={`visor-container ${fullscreen ? 'fullscreen' : ''} ${!mostrarCursor ? 'hide-cursor' : ''}`}
      onMouseMove={handleMouseMove}
    >
      {loading && (
        <div className="loading-spinner">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Cargando...</span>
          </div>
        </div>
      )}

      {pdfBlob && (
        <Document file={pdfBlob} onLoadSuccess={onDocumentLoadSuccess}>
          <div className="pdf-wrapper">
            <Page
              pageNumber={paginaActual}
              renderTextLayer={false}
              renderAnnotationLayer={false}
              width={pageWidth}
            />
          </div>
        </Document>
      )}

      {numPages && mostrarControles && (
        <>
          <button
            className="navegar izquierda"
            onClick={() => setPaginaActual((prev) => Math.max(prev - 1, 1))}
            disabled={paginaActual <= 1}
          >
            <FaChevronLeft size={30} />
          </button>
          <button
            className="navegar derecha"
            onClick={() => setPaginaActual((prev) => Math.min(prev + 1, numPages))}
            disabled={paginaActual >= numPages}
          >
            <FaChevronRight size={30} />
          </button>
        </>
      )}

      {mostrarControles && (
        <button className="fullscreen-btn" onClick={toggleFullscreen}>
          <FaExpand size={20} />
        </button>
      )}

      {numPages && (
        <div className="contador-pagina">
          Página {paginaActual} / {numPages}
        </div>
      )}
    </div>
  );
}

export default Visor;
