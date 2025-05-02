<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

use App\Models\PresentacionModel;

class ControladorTelegram extends ResourceController
{
    protected $botToken;

    public function __construct()
    {
        
        $this->botToken = env('TELEGRAM_BOT_TOKEN');

    }


    public function verPresentacion($nombreArchivo)
    {
        $modelo = new PresentacionModel();
        $fileData = $modelo->obtenerPorNombreArchivo($nombreArchivo);
    
        if (isset($fileData['file_url'])) {
            return redirect()->to($fileData['file_url']);
        } else {
            return $this->failNotFound("Archivo no encontrado");
        }
    }


    public function obtenerURLPresentacion($id)
    {
        $presentacionModel = new PresentacionModel();
        $presentacion = $presentacionModel->obtenerPresentacionPorId($id);
    
        if ($presentacion) {
            return $this->response->setJSON([
                'file_url' => $presentacion['file_url']
            ]);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Presentación no encontrada']);
        }
    }

    public function enviarComandoDesdeWeb()
    {
        // CORS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            http_response_code(200);
            exit;
        }
    
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    
        // Obtener datos del cuerpo JSON
        $input = json_decode(file_get_contents('php://input'), true);
    
        if (!$input || !isset($input['chatId']) || !isset($input['comando'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos inválidos']);
        }
    
        $chatId = $input['chatId'];
        $comando = $input['comando'];
    
        // Simulamos el mensaje como si viniera desde Telegram
        $mensajeSimulado = [
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => $comando,
            ],
            'origen' => 'web'
        ];
    
        // Guardamos mensaje del usuario en Firebase
        $mensajeUsuario = [
            'chat_id' => $chatId,
            'usuario_id' => $chatId,
            'texto' => $comando,
            'tipo' => 'invitado',
            'direccion' => 'usuario',
        ];
    
        $mensajeController = new \App\Controllers\ControladorMensaje();
        $mensajeController->guardar($mensajeUsuario);
    
        // Procesar comando
        $respuestaTexto = $this->procesarComando($mensajeSimulado);
    
        // Guardamos respuesta del bot
        $mensajeBot = [
            'chat_id' => $chatId,
            'usuario_id' => $chatId,
            'texto' => $respuestaTexto,
            'tipo' => 'invitado',
            'direccion' => 'bot',
        ];
    
        $mensajeController->guardar($mensajeBot);
    
        // Enviamos mensaje al bot por Telegram usando la función correcta
        $Origen="{Desde La Web}: ";
        $this->enviarMensajeDesdeTelegram($chatId, $Origen.$respuestaTexto);
        

        return $this->response->setJSON(['respuesta' => $respuestaTexto]);
    }
    



    




    private function procesarComando($update)
    {
        $chatId = $update['message']['chat']['id'];
        $texto = $update['message']['text'];
        
    
        switch ($texto) {
            case '/start':
                return "¡Hola! Bienvenido al Bot Presentador, Este Bot sirve para desplegar presentaciones";
            case '/menu':
                return "Estos son los comandos disponibles:\n/start\n/menu\n/desplegar";
            case '/desplegar':
                return "Puedes seleccionar la presentacion que desea desplegar y verlas aqui";
            default:
                return "No reconozco el comando. Escribe /menu para ver opciones";
        }
    }



    public function enviarPresentacionDesdeWeb()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            http_response_code(200);
            exit;
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        // Leer datos del cuerpo JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['chatId'], $data['presentacionId'], $data['nombreArchivo'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Faltan parámetros']);
        }

        $chatId = $data['chatId'];
        $presentacionId = $data['presentacionId'];
        $nombreArchivo = $data['nombreArchivo'];

        // Extraemos la extensión del archivo usando pathinfo
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

        // Mandar mensajes al bot
        $this->enviarMensajeDesdeTelegram($chatId, "{Desde la Web APP}: La presentación es la siguiente: $nombreArchivo");


        // Verificamos la extensión para elegir el visor adecuado
        if ('pptx' === strtolower($extension)) {
            $urlVisor = "http://localhost:3000/visorpptx/" . $presentacionId;
        } else {
            $urlVisor = "http://localhost:3000/visor/" . $presentacionId;
        }

        $this->enviarMensajeDesdeTelegram($chatId, "Puedes ver tu presentación en: $urlVisor");

        return $this->response->setJSON([
            'mensaje' => 'Presentación enviada correctamente',
            'url_visor' => $urlVisor
        ]);
    }

    
    
    


    //Aqui son las antiguas funciones para el frontend
    public function enviarComandoAlBot()
    {
        $mensaje = $this->request->getPost('mensaje');
        $chatId = $this->request->getPost('chat_id');

        if (!$mensaje || !$chatId) {
            return $this->fail('Faltan parámetros: mensaje o chat_id');
        }

        $respuestaBot = $this->enviarMensaje($chatId, $mensaje);

        return $this->respond([
            'mensaje_enviado' => $mensaje,
            'respuesta_bot' => $respuestaBot
        ]);
    }
      

    public function enviarArchivoAlBot()
    {
        $archivo = $this->request->getFile('archivo');
        $chatId = $this->request->getPost('chat_id');
        $presentacionId = $this->request->getPost('presentacion_id'); 
    
        if (!$archivo || !$chatId || !$archivo->isValid()) {
            return $this->fail('Faltan parámetros o archivo inválido');
        }
    
        $rutaTemporal = $archivo->getTempName();
        $nombre = $archivo->getName();
    
        $respuesta = $this->enviarDocumento($chatId, $rutaTemporal, $nombre);
    
        // Aquí se forma el URL del visor para mandárselo por mensaje al bot
        $urlVisor = "http://localhost:3000/visor/" . $presentacionId;
    
        // Enviar ese enlace como mensaje al usuario por el bot
        $this->enviarMensajeDesdeTelegram($chatId, "{Respuesta desde la WebApp}: Puedes ver tu presentación en: $urlVisor");
    
        return $this->respond([
            'mensaje' => 'Archivo enviado al bot y URL generada',
            'respuesta_bot' => $respuesta,
            'url_visor' => $urlVisor
        ]);
    }
    

    

    private function enviarMensaje($chatId, $texto)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $params = [
            'chat_id' => $chatId,
            'text' => $texto
        ];

        return $this->realizarPeticionCurl($url, $params);
    }


    public function enviarPresentacionPorID()
    {
    
        $chatId = $this->request->getPost('chat_id');
    
        $presentacionId = $this->request->getPost('presentacion_id');

    
        if (!$chatId || !$presentacionId) {
        return $this->fail('Faltan parámetros');
        }

    
        $modelo = new PresentacionModel();
    
        $presentacion = $modelo->obtenerPresentacionPorID($presentacionId);

    
        if (!$presentacion) {
        return $this->failNotFound('Presentación no encontrada');
        }

   
        $ruta = WRITEPATH . '../public/archivosPresentaciones/' . $presentacion['nombre_archivo']; // asegúrate del nombre correcto
   
        if (!file_exists($ruta)) {
        return $this->failNotFound('Archivo no encontrado en el servidor');
        }

   
        $this->enviarDocumento($chatId, $ruta, $presentacion['nombre_archivo']);

   
        $urlVisor = "http://localhost:3000/visor/" . $presentacionId;
   
        $this->enviarMensaje($chatId, "Aquí está tu presentación: $urlVisor");

   
        return $this->respond([
        'status' => 'ok',
        'url_visor' => $urlVisor
   
         ]);
     }
  





    private function enviarDocumento($chatId, $rutaArchivo, $nombreArchivo)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendDocument";

        $documento = new \CURLFile($rutaArchivo, mime_content_type($rutaArchivo), $nombreArchivo);

        $params = [
            'chat_id' => $chatId,
            'document' => $documento
        ];

        return $this->realizarPeticionCurl($url, $params);
    }






    private function realizarPeticionCurl($url, $params)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $respuesta = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        return $error ?: json_decode($respuesta, true);
    }



    //De aqui en adelante se encuentra la logica para manejar mensajes o documentos enviados desde telegram


    public function manejarMensajesTelegram()
    {
        // Leer el contenido JSON recibido directamente del webhook
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);
    
        if (!$update) {
            return $this->fail("No se recibió ningún mensaje válido");
        }
    
        $message = $update['message'] ?? null;
        if (!$message) {
            return $this->fail("No se recibió ningún mensaje válido");
        }
    
        $chatId = $message['chat']['id'];
        $nombre = $message['chat']['first_name'] ?? '';
        $apellido = $message['chat']['last_name'] ?? '';
        $nombreUsuario = $message['chat']['username'] ?? '';
        $usuarioId = (string)$chatId;
    
        // Verificar si es archivo
        if (isset($message['document'])) {
            $documento = $message['document'];
            $fileName = $documento['file_name'];
            $fileId = $documento['file_id'];
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
    
            if (!in_array(strtolower($extension), ['pdf', 'ppt', 'pptx'])) {
                $this->enviarMensajeDesdeTelegram($chatId, " No se acepta este tipo de archivo.\nIntenta con un archivo PDF o PowerPoint.");
                $this->enviarComandosDisponibles($chatId);
                return $this->respond(['mensaje' => 'Mensaje procesado']);
            }
    
            // Crear usuario si no existe
            $this->crearUsuarioDesdeTelegram($usuarioId, $nombreUsuario, $nombre, $apellido);
    
            $presentacionCtrl = new \App\Controllers\ControladorPresentaciones();
    
            // Paso 1: Descargar el archivo
            $contenidoBinario = $this->obtenerArchivoDesdeTelegram($fileId);
            $resultadoSubida = $presentacionCtrl->subirArchivoDesdeTelegram($contenidoBinario, $fileName);
            
            // Paso 2: Subir a Firebase
            if (!isset($resultadoSubida['error'])) {
                $presentacionId = $presentacionCtrl->crearPresentacionDesdeTelegram($usuarioId, $resultadoSubida['nombre_Archivo'], $resultadoSubida['file_url']);
            }
    
            // Se envía el URL
            if ($presentacionId) {

                if('pptx'===$resultadoSubida['extension']){
                    $urlVisor = "http://localhost:3000/visorpptx/" . $presentacionId;
                }else{
                    $urlVisor = "http://localhost:3000/visor/" . $presentacionId;
                }
                
                $this->enviarMensajeDesdeTelegram($chatId, "Archivo guardado correctamente.\n\n Titulo: $fileName\n Link al visor: $urlVisor");
            } else {
                $this->enviarMensajeDesdeTelegram($chatId, "Ocurrió un error al guardar la presentación.");
            }
        } else if (isset($message['text'])) {
            $texto = strtolower(trim($message['text']));
    
            if ($texto === '/start' || $texto === '/menu') {
                $this->enviarComandosDisponibles($chatId);
            } else if ($texto === '/desplegar') {
                $this->enviarMensajeDesdeTelegram($chatId, "Por favor, envía el archivo PDF o PowerPoint que deseas desplegar.");
            } else {
                $this->enviarMensajeDesdeTelegram($chatId, "No entiendo ese comando.");
                $this->enviarComandosDisponibles($chatId);
            }
        }
    
        return $this->respond(['mensaje' => 'Mensaje procesado']);
    }
    




private function enviarMensajeDesdeTelegram($chatId, $mensaje)
{
    
    file_get_contents("https://api.telegram.org/bot{$this->botToken}/sendMessage?chat_id=$chatId&text=" . urlencode($mensaje));

}

private function enviarComandosDisponibles($chatId)
{
    $comandos = "Comandos disponibles:\n/menu - Volver al inicio\n/desplegar - Enviar presentación";
    $this->enviarMensajeDesdeTelegram($chatId, $comandos);
}

private function guardarArchivoDesdeTelegram($fileId, $nombre)
{
    

    // Obtener ruta del archivo en Telegram
    $fileInfo = json_decode(file_get_contents("https://api.telegram.org/bot{$this->botToken}/getFile?file_id=$fileId"), true);
    $filePath = $fileInfo['result']['file_path'];

    $contenido = file_get_contents("https://api.telegram.org/file/bot{$this->botToken}/$filePath");

    $ruta = WRITEPATH . '../public/archivosPresentaciones/';
    $nombreUnico = uniqid() . '_' . $nombre;
    file_put_contents($ruta . $nombreUnico, $contenido);

    return $nombreUnico;
}

private function crearUsuarioDesdeTelegram($id, $username, $nombre, $apellido)
{
    $usuarioModel = new \App\Models\UsuarioModel();
    $usuarioDoc = $usuarioModel->existeUsuario($id);

    if (!$usuarioDoc) {
        $usuarioModel->crearUsuario([
            'usuario_id' => $id,
            'nombreDeUsuario' => $username,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'rol' => 'invitado',
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }
}

private function crearPresentacionDesdeTelegram($data)
{
    $presentacionModel = new \App\Models\PresentacionModel();

    $presentacionModel->crearPresentacion([
        'usuario_id' => $data['usuario_id'],
        'nombre' => $data['nombre'],
        'file_url' => base_url('archivosPresentaciones/' . $data['archivo']),
        'archivo' => $data['archivo'],
        'creado_en' => date('Y-m-d H:i:s')
    ]);
}

private function obtenerArchivoDesdeTelegram($fileId)
{
    $token = 'TU_TOKEN_DEL_BOT';

   

    // Paso 1: Obtener la ruta del archivo
    $url = "https://api.telegram.org/bot{$this->botToken}/getFile?file_id=$fileId";
    $res = file_get_contents($url);
    $json = json_decode($res, true);

    if (!isset($json['result']['file_path'])) {
        return null;
    }

    $filePath = $json['result']['file_path'];

    // Paso 2: Descargar el archivo
    $fileUrl = "https://api.telegram.org/file/bot{$this->botToken}/$filePath";
    return file_get_contents($fileUrl); // Devuelve binario
}








}
