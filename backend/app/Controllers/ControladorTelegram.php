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

            //http://localhost:3000/visorpptx/
            $urlVisor = "https://cedar-prescribed-meetings-strange.trycloudflare.com/visorpptx/" . $presentacionId;
        } else {
            //http://localhost:3000/visor/
            $urlVisor = "https://cedar-prescribed-meetings-strange.trycloudflare.com/visor/" . $presentacionId;
        }

        $this->enviarMensajeDesdeTelegram($chatId, "Puedes ver tu presentación en: $urlVisor");

        return $this->response->setJSON([
            'mensaje' => 'Presentación enviada correctamente',
            'url_visor' => $urlVisor
        ]);
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
                    //
                    //http://localhost:3000
                    $urlVisor = "https://cedar-prescribed-meetings-strange.trycloudflare.com/visorpptxTelegram/" . $presentacionId;
                }else{
                    //http://localhost:3000
                    $urlVisor = "https://cedar-prescribed-meetings-strange.trycloudflare.com/visorTelegram/" . $presentacionId;
                }
                
                $this->enviarMensajeDesdeTelegram($chatId, "Archivo guardado correctamente.\n\n Titulo: $fileName\n Link al visor: $urlVisor");
            } else {
                $this->enviarMensajeDesdeTelegram($chatId, "Ocurrio un error al guardar la presentacion");
            }
        } else if (isset($message['text'])) {
            $texto = strtolower(trim($message['text']));
            $textoOriginal = trim($message['text']); // Para conservar mayúsculas en contraseña real
            $patronContraseña = '/^contraseña:\s*(.+)$/i';



            if ($texto === '/start' || $texto === '/menu') {
                $this->enviarComandosDisponibles($chatId);
            } else if ($texto === '/desplegar') {
                $this->enviarMensajeDesdeTelegram($chatId, "Por favor, envia el archivo PDF o PowerPoint que deseas desplegar.");
            } else if ($texto === '/registrarme') {

                // Muestra instrucciones para enviar contraseña
                $this->enviarMensajeDesdeTelegram(
                    $chatId,
                    "Tu usuario para entrar a la Web APP será: $chatId\n\n" .
                    "Para completar tu registro, escribe el siguiente mensaje:\n\n" .
                    "`contraseña:TuContraseñaAqui`\n\n" .
                    "Por ejemplo:\n`contraseña:JuanLol2025`\n\n" .
                    "Después de eso, se guardarán tus credenciales.",
                    "Markdown"
                );

            } else if (preg_match($patronContraseña, $textoOriginal, $coincidencias)) {
                $contrasena = trim($coincidencias[1]);
        
                if (empty($contrasena)) {
                    $this->enviarMensajeDesdeTelegram($chatId, "No detecté una contraseña válida. Intenta de nuevo usando el formato: contraseña:TuContraseña");
                    return $this->respond(['mensaje' => 'Contraseña vacía']);
                }
        
                // Verifica si el usuario ya fue creado antes de registrar contraseña
                $this->crearUsuarioDesdeTelegram($usuarioId, $nombreUsuario, $nombre, $apellido);
                $this->crearCredencialesDesdeTelegram($usuarioId, $nombre, $chatId, $contrasena);
        
                
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
    $comandos = "Comandos disponibles:\n/menu - Volver al inicio\n/desplegar - Enviar presentación\n
    /registrarme - Crear Contraseña para acceder a la Web App";
    $this->enviarMensajeDesdeTelegram($chatId, $comandos);
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
            'usuarioWeb' => '',
            'contrasena' => '',
            'creado_en' => date('Y-m-d H:i:s')
        ]);
    }
}

   


    private function crearCredencialesDesdeTelegram($usuarioId, $nombre, $chatId, $contrasena)
{
    $usuarioModel = new \App\Models\UsuarioModel();

    if ($usuarioModel->camposCredencialesLlenos($chatId)) {
        $this->enviarMensajeDesdeTelegram($chatId, "Tu ya haz pasado por este proceso por lo que ya has completado el proceso de registro.\n Usa tu usuario y contraseña que ya habias creado para iniciar sesion en la Web App.");
        return;
    }

    $usuarioWeb = $nombre . $chatId;
    // Actualiza Firebase
    $usuarioModel->actualizarCredenciales($usuarioId, $usuarioWeb, $contrasena);

    $this->enviarMensajeDesdeTelegram($chatId, "Registro completado.\n\nUsuario Web: $chatId\nContraseña: $contrasena\n\nGuarda esta información para ingresar a la Web App.");
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
