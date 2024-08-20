<?php 

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                //Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El Usuario No existe o No esta Confirmado');
                } else {
                    // El usuario existe
                    if( password_verify($_POST['password'], $usuario->password) ) {
                       //Iniciar la sesión
                       if(!isset($_SESSION)) {
                        session_start();
                        }
                       $_SESSION['id'] = $usuario->id;
                       $_SESSION['nombre'] = $usuario->nombre;
                       $_SESSION['email'] = $usuario->email;
                       $_SESSION['login'] = true;
                        
                       //Redireccionar 
                       header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta('error', 'Contraseña Incorrecta');
                    }
                }
            }
        }
        $alertas = Usuario::getAlertas();
        
        $router->render('auth/login', [
            'titulo' =>'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        if(!isset($_SESSION)) {
            session_start();
        }

        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router) {
        $alertas = [];
        $usuario = new Usuario;
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if ($existeUsuario) {
                    Usuario::setAlerta('error', 'El Usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    //Encriptar la contraseña
                    $usuario->hashPassword(); 

                    //Eliminar password2
                    unset($usuario->password2);

                    //Generar un token
                    $usuario->crearToken();
                    
                    //Crear un nuevo usuario
                    $resultado = $usuario->guardar();

                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear', [
            'titulo' =>'Crea Tu Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                //Buscar usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado) {
                    //Generar un nuevo token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    //Actualizar el usuario
                    $usuario->guardar();
                    //Enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    //Imprimir alerta
                    Usuario::setAlerta('exito', 'Las instrucciones están en tu Email');
                }else {
                    Usuario::setAlerta('error', 'El usuario no existe o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' =>'Olvide Mi Contraseña',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {
        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('Location: /');

        //Identificar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token No Válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);

            //Validar la contraseña nueva
            $alertas = $usuario->validarPassword();

            if(empty($alertas)) {
                //Encriptar la contraseña
                $usuario->hashPassword();
                
                //Eliminar el token
                $usuario->token = null;

                //Guardar el usuario en la BD
                $resultado = $usuario->guardar();

                //Redireccionar 
                if($resultado) {
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/reestablecer', [
            'titulo' =>'Reestablecer Contraseña',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje', [
            'titulo' =>'Cuentra Creada Exitosamente'
        ]);
    }

    public static function confirmar(Router $router) {

        $token = s($_GET['token']);
        
        if(!$token) header('Location: /');

        //Encontrar el usuario
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            //NO se encontro un usuario con ese token
            Usuario::setAlerta('error', 'Token No Valido');
        } else {
            //Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta Comprobada Correctamente');

        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' =>'Confirma tu cuenta',
            'alertas' => $alertas
        ]);    
    }

}