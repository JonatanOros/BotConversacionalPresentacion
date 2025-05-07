<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PresentacionModel;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Reader\PowerPoint2007;
use PhpOffice\PhpPresentation\Writer\Pdf\DomPDF;
use PhpOffice\PhpPresentation\PhpPresentation;


class ControladorPresentaciones extends ResourceController
{

    protected $presentacionModel;
    public $dominio="jjk";

    public function __construct()
    {
        $this->presentacionModel = new PresentacionModel();
    }

    public function subirArchivo()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', 'https://cedar-prescribed-meetings-strange.trycloudflare.com');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
        $archivo = $this->request->getFile('presentacion');
    
        if (!$archivo || !$archivo->isValid()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Archivo inválido o no recibido.']);
        }
    
        // Valida el tipo de archivo
        $allowedTypes = ['pdf', 'pptx'];
        $extension = strtolower($archivo->getExtension());
    
        if (!in_array($extension, $allowedTypes)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Tipo de archivo no permitido.']);
        }

        // Obtiene nombre real sin extension
        $NombreRealDelArchivo = pathinfo($archivo->getName(), PATHINFO_FILENAME);

        // Genera nombre unico basado en el nombre real
        $nombreBase = $NombreRealDelArchivo . uniqid('_presentacion_');

        // Finaliza nombre seguro
        $safeName = $nombreBase . '.' . $extension;

    
        if (!$archivo->move(ROOTPATH . 'public/archivosPresentaciones', $safeName)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                  ->setJSON(['error' => 'No se pudo mover el archivo.']);
        }
    
        $filePath = ROOTPATH . 'public/archivosPresentaciones/' . $safeName;
        $fileUrl = base_url("public/archivosPresentaciones/" . $safeName);

        
    
        return $this->response->setJSON([
            'message' => 'Archivo subido con éxito.',
            'file_url' => $fileUrl,
            'nombre_Archivo' => $safeName,
            'extension' => $extension
        ]);
    }
    


    public function crearPresentacion()
    {


        $this->response->setHeader('Access-Control-Allow-Origin', 'https://cedar-prescribed-meetings-strange.trycloudflare.com');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Verificar datos mínimos
        $descripcion = $this->request->getPost('descripcion');
        $usuarioId = $this->request->getPost('usuario_id');
    
        if (empty($descripcion) || empty($usuarioId)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Faltan campos requeridos.']);
        }
    
        // Subir el archivo
        $response = $this->subirArchivo();
        $resultadoSubida = json_decode($response->getBody(), true);
    
        if (isset($resultadoSubida['error'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON($resultadoSubida);
        }
    
        // Crear presentación
        $presentacionData = [
            'presentacion_id' => uniqid('pres_'),
            'titulo' => $resultadoSubida['nombre_Archivo'],
            'descripcion' => $descripcion,
            'file_url' => $resultadoSubida['file_url'],
            'usuario_id' => $usuarioId,
            'creado_en' => date('Y-m-d H:i:s')
        ];
    
        return $this->respond($this->presentacionModel->crearPresentacion($presentacionData), 200);
    }




    public function subirArchivoDesdeTelegram($archivoTelegram, $nombreArchivo)
    {
        // Obtener extensión
        $extensionOriginal = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    
        // Validar tipo de archivo permitido
        $allowedTypes = ['pdf', 'pptx'];
        if (!in_array($extensionOriginal, $allowedTypes)) {
            return ['error' => 'Tipo de archivo no permitido.'];
        }
    
        // Obtener nombre base (sin extensión) y limpiar caracteres especiales
        $nombreBaseLimpio = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($nombreArchivo, PATHINFO_FILENAME));
    
        // Generar nombre seguro y único
        $nombreUnicoBase = $nombreBaseLimpio . uniqid('_presentacion_');
    
        // Nombre final con extensión
        $nombreArchivoFinal = $nombreUnicoBase . '.' . $extensionOriginal;
    
        // Ruta donde se va a guardar
        $rutaFinal = WRITEPATH . '../public/archivosPresentaciones/' . $nombreArchivoFinal;
    
        // Guardar el archivo
        file_put_contents($rutaFinal, $archivoTelegram);
    
        
            
        $fileUrl = base_url("public/archivosPresentaciones/" . $nombreArchivoFinal);
        
    
        // Retorna info para registrar la presentacion
        return [
            'file_url' => $fileUrl,
            'nombre_Archivo' => $nombreArchivoFinal,
            'extension' => $extensionOriginal
        ];
    }
    

   
    
    public function crearPresentacionDesdeTelegram($usuario_id, $titulo, $file_url)
    {

        $presentacionId = uniqid();
        $presentacionData = [
            'presentacion_id' => $presentacionId,
            'titulo' => $titulo,
            'descripcion' => '',
            'file_url' => $file_url,
            'usuario_id' => $usuario_id,
            'creado_en' => date('Y-m-d H:i:s')
        ];
    
        $resultado= $this->presentacionModel->crearPresentacion($presentacionData);

        if (isset($resultado['error'])) {
            // Regresa null si no se guardo la presentacion
            return null;
        }

        // Regresa el id solo si fue exitoso
    
        return $presentacionId; 

    }
    


    public function obtenerPresentacion($id)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

        $model = new \App\Models\PresentacionModel();
        $data = $model->obtenerPresentacionPorId($id);
    
        return $this->respond($data);
    }

    public function obtenerPresentacionesPorUsuario($usuarioId)
    {
        // Agregar CORS headers manuales
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");



        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        
        $model = new \App\Models\PresentacionModel();
        $data = $model->getPresentacionesPorUsuarioId($usuarioId);

        return $this->response->setJSON($data);

    }


    public function verArchivo($nombreArchivo)
{
    // Ruta del archivo en el servidor
    $ruta = WRITEPATH . '../public/archivosPresentaciones/' . $nombreArchivo;

    // Verifica si el archivo existe
    if (!is_file($ruta)) {
        log_message('error', 'Archivo no encontrado: ' . $ruta);
        return $this->response
                    ->setStatusCode(404)
                    ->setHeader('Access-Control-Allow-Origin', '*')
                    ->setBody('Archivo no encontrado');
    }

    // Detecta extensión
    $extension = pathinfo($ruta, PATHINFO_EXTENSION);


    // Con la extension se establece el mime que ira en el header content type aunque se pudiera hacer
    // solo con lo que esta en default aqui nos aseguramos de que se mande lo que ocupamos
    switch ($extension) {
        case 'pptx':
            $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            break;
        case 'pdf':
            $mime = 'application/pdf';
            break;
        default:
            $mime = mime_content_type($ruta);
    }

    // Lee el contenido del archivo como binario
    $contenido = file_get_contents($ruta);

    // Devuelve la respuesta como Blob
    return $this->response
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'inline; filename="' . basename($nombreArchivo) . '"')
                ->setHeader('Content-Length', strlen($contenido))
                ->setBody($contenido);
}


    public function verArchivoBase64($nombreCodificado)
    {
        // Decodificar primero la parte de URL
        $nombreCodificado = urldecode($nombreCodificado);
    
        // Validar que sea una cadena base64 válida antes de decodificar
        if (!preg_match('/^[A-Za-z0-9\/\+\=]+$/', $nombreCodificado)) {
            return $this->response->setStatusCode(400)->setBody('Nombre codificado inválido.');
        }
    
        // Decodificar
        $nombreArchivo = base64_decode($nombreCodificado, true); // true hace que devuelva false si falla
    
        if ($nombreArchivo === false) {
            return $this->response->setStatusCode(400)->setBody('Error al decodificar nombre.');
        }
    
        return $this->verArchivo($nombreArchivo);
    }    



    

    public function eliminarPresentacion($id)
    {
        // Encabezados CORS
        $this->response->setHeader('Access-Control-Allow-Origin', 'https://cedar-prescribed-meetings-strange.trycloudflare.com');
        $this->response->setHeader('Access-Control-Allow-Methods', 'DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
        // Primero, obtenemos la presentación por ID
        $presentacion = $this->presentacionModel->obtenerPresentacionPorId($id);
    
        if (!$presentacion) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                                  ->setJSON(['error' => 'Presentación no encontrada.']);
        }
    
        // Borramos el archivo físico si existe
        $filePath = FCPATH . 'archivosPresentaciones/' . $presentacion['titulo'];

        // Verificamos que exista y sea un archivo antes de intentar borrar
        if (is_file($filePath)) {
            if (!unlink($filePath)) {
                log_message('error', 'No se pudo eliminar el archivo fisico: ' . $filePath);
                // Aquí decides: puedes continuar o detener todo si quieres. Yo recomiendo continuar.
            }
        } else {
            log_message('warning', 'Archivo físico no encontrado: ' . $filePath);
        }

        // Ahora eliminamos el registro de la base de datos (Firebase)
        $resultado = $this->presentacionModel->eliminarPresentacion($id);
    
        if ($resultado) {
            return $this->response->setJSON(['message' => 'Presentacion eliminada exitosamente.']);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                  ->setJSON(['error' => 'Error al eliminar la presentacion.']);
        }
    }


    

    public function subirArchivoLibreOffice()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    
        $archivo = $this->request->getFile('presentacion');
    
        if (!$archivo || !$archivo->isValid()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Archivo inválido o no recibido.']);
        }
    
        $allowedTypes = ['pdf', 'pptx'];
        $extension = strtolower($archivo->getExtension());
    
        if (!in_array($extension, $allowedTypes)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Tipo de archivo no permitido.']);
        }
    
        // Generar nombre seguro y mover archivo
        $safeName = uniqid('presentacion_') . '.' . $extension;
    
        if (!$archivo->move(ROOTPATH . 'public/archivosPresentaciones', $safeName)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                  ->setJSON(['error' => 'No se pudo mover el archivo.']);
        }
    
        $filePath = ROOTPATH . 'public/archivosPresentaciones/' . $safeName;
    
        // Si es pptx, convertir a PDF
        if ($extension === 'pptx') {
            // Ejecutar LibreOffice para convertir
            $outputDir = ROOTPATH . 'public/archivosPresentaciones';
            $command = 'soffice --headless --convert-to pdf "' . $filePath . '" --outdir "' . $outputDir . '"';
            exec($command, $output, $return_var);
    
            if ($return_var !== 0) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                      ->setJSON(['error' => 'Error al convertir el archivo usando LibreOffice.']);
            }
    
            // Opcional: eliminar archivo original pptx después de convertir
            // unlink($filePath);
    
            // El nombre del nuevo archivo PDF
            $safeName = pathinfo($safeName, PATHINFO_FILENAME) . '.pdf';
        }
    
        $fileUrl = base_url("public/archivosPresentaciones/" . $safeName);
    
        return $this->response->setJSON([
            'message' => 'Archivo subido y convertido con éxito.',
            'file_url' => $fileUrl,
            'nombre_Archivo' => $safeName,
        ]);
    }
    
    


    
}
