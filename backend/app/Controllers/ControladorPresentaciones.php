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
            'titulo' => $this->request->getPost('titulo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'file_url' => $resultadoSubida['file_url'],  // Aquí usamos la URL devuelta por subirArchivo()
            'usuario_id' => $this->request->getPost('usuario_id'),
            'creado_en' => date('Y-m-d H:i:s')
        ];

        return $this->respond($this->presentacionModel->crearPresentacion($presentacionData), 200);
    }

}
