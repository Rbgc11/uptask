<?php 

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {
    public static function index(Router $router) {

        if(!isset($_SESSION)) {
            session_start();
        }

        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {
        if(!isset($_SESSION)) {
            session_start();
        }
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            //Validación
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)) {
                // Generar una URL única
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                //Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                //Guardar el proyecto
                $proyecto->guardar();
                
                //Redireccionar 
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'alertas' => $alertas,
            'titulo' => 'Crear Proyecto'
        ]);
    }

    public static function proyecto(Router $router) {
        if(!isset($_SESSION)) {
            session_start();
        }
        isAuth();

        $token = $_GET['id'];

        if(!$token) header('Location: /dashboard');
        //Revisamos que la persona que visita el proyecto, es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router) {
        if(!isset($_SESSION)) {
            session_start();
        }
        isAuth();
        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            
            $alertas = $usuario->validar_perfil();

            if(empty($alertas)) {

                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    Usuario::setAlerta('error', 'Email no válido, ya pertenece a otro usuario');
                    $alertas = $usuario->getAlertas();
                } else {
                    //Guardar el registro
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                    $alertas = $usuario->getAlertas();

                    //Asignar el nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;                
                }
            }
        }
        
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas 
        ]);
    }

    public static function cambiar_password(Router $router) {
        if(!isset($_SESSION)) {
            session_start();
        }
        isAuth();
        $alertas =[];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            $usuario->sincronizar($_POST);
            
            $alertas = $usuario->nuevo_password();

            if(empty($alertas)) {
                $resultado = $usuario->comprobar_password();

                if($resultado) {
                    $usuario->password = $usuario->password_nuevo;

                    //Eliminar propiedades No necesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);

                    //Encriptar la nueva contraseña 
                    $usuario->hashPassword();

                    $usuario->guardar();

                    if($resultado){
                        Usuario::setAlerta('exito', 'Contraseña Guardada Correctamente');
                        $alertas = $usuario->getAlertas();
                    }

                } else {
                    Usuario::setAlerta('error', 'Contraseña Incorrecta');
                    $alertas = $usuario->getAlertas();
                }
            }
        }
        

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Contraseña',
            'alertas' => $alertas 
        ]);
    }
 }