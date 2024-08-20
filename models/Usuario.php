<?php

namespace Model;

class Usuario extends ActiveRecord{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    //Validar el Login de Usuarios
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }

        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatoria';
        }
        return self::$alertas;

    }
    //Validación para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }

        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatoria';
        }

        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'La contraseña ha de contener como mínimo 6 caracteres';
        }

        if($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Las Contraseñas son diferentes';
        }
        
        return self::$alertas;
    }

    //Valida el email
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El Email es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }

    //Valida la contraseña
    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña del Usuario es Obligatoria';
        }

        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'La contraseña ha de contener como mínimo 6 caracteres';
        }
        return self::$alertas;
    }

    public function validar_perfil() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre es Obligatorio';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        return self::$alertas;
    }

    public function nuevo_password() {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'La Contraseña Actual es Obligatoria';
        }

        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'La Contraseña Nueva es Obligatoria';
        }

        if(strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = 'La Contraseña Nueva ha de contener como mínimo 6 caracteres';
        }
        return self::$alertas;
    } 

    public function comprobar_password() {
        return password_verify($this->password_actual, $this->password );
    }

    //Encripta la contraseña
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar el token
    public function crearToken() {
        $this->token = uniqid();
    }


}