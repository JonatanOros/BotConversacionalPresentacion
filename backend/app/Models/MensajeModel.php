<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Firebase;

class MensajeModel extends Model
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new Firebase();
    }

    public function crearMensaje($data)
    {
        try {

            $this->firebase->getDatabase()->collection('mensajes')->document($data['mensaje_id'])->set($data);
            
            return ['message' => 'Mensaje registrado exitosamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }


    }



    public function guardarMensaje($mensajeData)
    {
        try {
            $this->firebase->getDatabase()
                ->collection('mensajes')
                ->add($mensajeData);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    public function obtenerMensajesPorUsuario($usuarioId)
    {
        try {
            $mensajes = [];
            $documentos = $this->firebase->getDatabase()
                ->collection('mensajes')
                ->where('usuario_id', '=', $usuarioId)
                ->documents();

            foreach ($documentos as $doc) {
                if ($doc->exists()) {
                    $mensajes[] = $doc->data();
                }
            }

            return $mensajes;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    
}
