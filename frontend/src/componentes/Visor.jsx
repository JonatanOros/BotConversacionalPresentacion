import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { Document, Page, pdfjs} from 'react-pdf';



// Configura el worker desde el CDN — usando la versión correcta de pdfjs usada internamente por react-pdf
//pdfjs.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.worker.min.js`;

pdfjs.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.js';


function Visor() {
  const { id } = useParams();
  const [urlPDF, setUrlPDF] = useState('');
  const [numPages, setNumPages] = useState(null);
  const [paginaActual, setPaginaActual] = useState(1);

  useEffect(() => {
    // Llamar al backend para obtener la URL del archivo por ID
    const fetchFileUrl = async () => {
      try {
        const response = await fetch(`http://localhost:8080/obtenerPresentacion/${id}`);
        const data = await response.json();
        if (data.file_url) {
          const tituloCodificado = encodeURIComponent(btoa(data.titulo));
          setUrlPDF(`http://localhost:8080/verArchivoBase64/${tituloCodificado}`); // Usa el nombre del archivo directamente

        }
      } catch (error) {
        console.error('Error al obtener el archivo:', error);
      }
    };

    fetchFileUrl();
  }, [id]);

  const onDocumentLoadSuccess = ({ numPages }) => {
    setNumPages(numPages);
    setPaginaActual(1);
  };

  return (
    <div className="p-4">
      {urlPDF ? (
        <Document file={urlPDF} onLoadSuccess={onDocumentLoadSuccess}>
          <Page
            pageNumber={paginaActual}
            renderTextLayer={false}         // Esto elimina la capa de texto encima del PDF (que era mi problema porque se repetia el texto de los componentes)
            renderAnnotationLayer={false}   // Esto elimina la capa de anotaciones
          />
        </Document>
      ) : (
        <p>Cargando presentación...</p>
      )}

      {numPages && (
        <div className="mt-4">
          <button
            onClick={() => setPaginaActual((prev) => Math.max(prev - 1, 1))}
            disabled={paginaActual <= 1}
          >
            Anterior
          </button>
          

          <span className="mx-2">{paginaActual} / {numPages}</span>
          <button
            onClick={() => setPaginaActual((prev) => Math.min(prev + 1, numPages))}
            disabled={paginaActual >= numPages}
          >
            Siguiente
          </button>


        </div>
      )}
    </div>
  );
}

export default Visor;

