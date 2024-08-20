<div class="contenedor olvide">
    <?php include_once __DIR__ .'/../templates/nombre-sitio.php' ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recupera tu contraseña</p>

    <?php include_once __DIR__ .'/../templates/alertas.php' ?>

        <form class="formulario" method="POST" action="/olvide" novalidate>

            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu Email" name="email">
            </div>

            <input type="submit" class="boton" value="Enviar">
        </form>

        <div class="acciones">
            <a href="/">¿Ya tienes una cuenta? Iniciar Sesión</a>
            <a href="/crear">¿Aún no tienes una cuenta? Crear una</a>
        </div>
    </div> <!--Contenedor sm -->
</div>