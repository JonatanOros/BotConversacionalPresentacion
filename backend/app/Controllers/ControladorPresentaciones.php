<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\PresentacionModel;

class ControladorPresentaciones extends ResourceController
{

    protected $presentacionModel;

    public function __construct()
    {
        $this->presentacionModel = new PresentacionModel();
    }

    public function subirArchivo()
    {
        $archivo = $this->request->getFile('presentacion');

        if (!$archivo->isValid()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => $archivo->getErrorString()]);
        }

        // Validar tipo de archivo
        $allowedTypes = ['pdf', 'pptx'];
        if (!in_array($archivo->getExtension(), $allowedTypes)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Tipo de archivo no permitido.']);
        }

        // Mover el archivo a la carpeta archivosPresentaciones
        // Usa el nombre original del archivo
        $NombreArchivo = $archivo->getName(); 
        
        $archivo->move('archivosPresentaciones', $NombreArchivo);

        // Guardar referencia en Firebase o Base de datos
        $fileUrl = base_url("public/archivosPresentaciones/" . $NombreArchivo);
        
        // TODO: Guarda $filePath en Firebase si es necesario

        return $this->response->setJSON([
            'message' => 'Archivo subido con éxito.',
            'file_url' => $fileUrl,
            'nombre_Archivo' => $NombreArchivo,
        ]);
    }



    public function crearPresentacion()
    {

        // Primero, subimos el archivo
        $response = $this->subirArchivo();

        // Convertirmos la respuesta a JSON 
        $resultadoSubida = json_decode($response->getBody(), true);
        
        // Si hay error en la subida, devolvemos la respuesta
        if (isset($resultadoSubida['error'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON($resultadoSubida);
        }

        

        // Luego, creamos la presentación con la URL del archivo
        $presentacionData = [
            'presentacion_id' => uniqid(),
            'titulo' => $resultadoSubida['nombre_Archivo'],
            'descripcion' => $this->request->getPost('descripcion'),
            'file_url' => $resultadoSubida['file_url'],  // Aquí usamos la URL devuelta por subirArchivo()
            'usuario_id' => $this->request->getPost('usuario_id'),
            'creado_en' => date('Y-m-d H:i:s')
        ];

        return $this->respond($this->presentacionModel->crearPresentacion($presentacionData), 200);
    }





    public function subirArchivoDesdeTelegram($archivoTelegram, $nombreArchivo)
    {
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
    
        $allowedTypes = ['pdf', 'pptx'];
        if (!in_array($extension, $allowedTypes)) {
            return ['error' => 'Tipo de archivo no permitido.'];
        }
    
        $rutaFinal = WRITEPATH . '../public/archivosPresentaciones/' . $nombreArchivo;
    
        file_put_contents($rutaFinal, $archivoTelegram); // Ya deberías tener el contenido en binario
    
        $fileUrl = base_url("public/archivosPresentaciones/" . $nombreArchivo);
    
        return [
            'file_url' => $fileUrl,
            'nombre_Archivo' => $nombreArchivo
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
        $model = new \App\Models\PresentacionModel();
        $data = $model->obtenerPresentacionPorId($id);
    
        return $this->respond($data);
    }

    public function obtenerPresentacionesPorUsuario($usuarioId)
    {
        // Agregar CORS headers manuales
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        $model = new \App\Models\PresentacionModel();
        $data = $model->getPresentacionesPorUsuarioId($usuarioId);

        return $this->respond($data);
    }


    public function verArchivo($nombreArchivo)
    {
       
        $ruta = WRITEPATH . '../public/archivosPresentaciones/' . $nombreArchivo;

        if (!is_file($ruta)) {
            log_message('error', 'Archivo no encontrado: ' . $ruta);
            header('Access-Control-Allow-Origin: *', true);
            http_response_code(404);
            echo 'Archivo no encontrado';
            return;
        }

        $mime = mime_content_type($ruta);

        //  Headers manuales, asegurando CORS y tipo de contenido
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($nombreArchivo) . '"');
        header('Content-Length: ' . filesize($ruta));

        //  Envía directamente el archivo
        readfile($ruta);
        exit;
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


    
}
