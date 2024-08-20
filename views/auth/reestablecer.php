<div class="contenedor reestablecer">
    <?php include_once __DIR__ .'/../templates/nombre-sitio.php' ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Escribe tu Nueva Contraseña</p>

        <?php include_once __DIR__ .'/../templates/alertas.php' ?>

        <?php if($mostrar) { ?>


        <form class="formulario" method="POST">
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" placeholder="Tu Contraseña" name="password">
            </div>

            <input type="submit" class="boton" value="Guardar Contraseña">
        </form>

        <?php } ?>


        <div class="acciones">
            <a href="/crear">¿Aún no tienes una cuenta? Crear una</a>
            <a href="/olvide">¿Olvidastes la contraseña?</a>
        </div>
    </div> <!--Contenedor sm -->
</div>