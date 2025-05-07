<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Firebase;

class UsuarioModel extends Model
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new Firebase();
    }

    public function crearUsuario($data)
    {
        try {

            $this->firebase->getDatabase()->collection('usuarios')->document($data['usuario_id'])->set($data);

            
            return ['message' => 'Usuario creado exitosamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    public function existeUsuario($usuarioId)
    {
        try {
            $document = $this->firebase->getDatabase()->collection('usuarios')->document($usuarioId)->snapshot();
            return $document->exists();
        } catch (\Exception $e) {
            return false;
        }
    }


    public function actualizarCredenciales($usuarioId, $usuarioWeb, $contrasena)
    {
        try {
            $this->firebase->getDatabase()->collection('usuarios')->document($usuarioId)
                ->update([
                    ['path' => 'usuarioWeb', 'value' => $usuarioId],
                    ['path' => 'contrasena', 'value' => $contrasena]
                ]);

            return ['message' => 'Credenciales actualizadas correctamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function camposCredencialesLlenos($usuarioId)
    {
        try {
            $document = $this->firebase->getDatabase()->collection('usuarios')->document($usuarioId)->snapshot();

            if ($document->exists()) {
                $data = $document->data();
                return !empty($data['usuarioWeb']) && !empty($data['contrasena']);
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function verificarCredenciales($usuarioWeb, $contrasena)
    {
        try {
            // Validación previa
            if (empty($usuarioWeb) || empty($contrasena)) {
                return [
                    'valido' => false,
                    'mensaje' => 'Usuario o contraseña vacíos',
                ];
            }
    
            // Obtener el documento directamente por ID (usando el chat_id)
            $document = $this->firebase->getDatabase()->collection('usuarios')->document($usuarioWeb)->snapshot();
    
            if ($document->exists()) {
                $data = $document->data();
    
                // Comparar contraseñas
                if (isset($data['contrasena']) && $data['contrasena'] === $contrasena) {
                    return [
                        'valido' => true,
                        'usuario_id' => $usuarioWeb,
                        'usuario' => $data,
                    ];
                } else {
                    return [
                        'valido' => false,
                        'mensaje' => 'Contraseña incorrecta',
                    ];
                }
            }
    
            return [
                'valido' => false,
                'mensaje' => 'Usuario no encontrado',
            ];
        } catch (\Exception $e) {
            return [
                'valido' => false,
                'mensaje' => 'Error al verificar credenciales: ' . $e->getMessage(),
            ];
        }
    }
    
    


    
}
