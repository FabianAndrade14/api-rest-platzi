# api-rest-platzi
API REST Creada a partir del curso ofrecido por la plataforma PLATZI, utilizando el lenguaje PHP para el backend.

Todo lo que han de saber, se encuentra descrito aquí:

**¿QUÉ ES UNA API?**

Son las siglas de Application Programming Interface, un conjunto de reglas que indican como interactuan 2 aplicaciones.

**HERRAMIENTAS PARA EL CURSO**

1. Interprete PHP
2. Conocimiento now.sh
3. curl (Para pedidos *http*)
4. JQ para JSON

**¿QUÉ ES HTTP?**

Es un protocolo de comunicación entre aplicaciones. Basado en el intercambio de texto (Hyper Text Transfer Protocol)

**¿QUÉ ES REST?**

Permite mandar JSON, XML, binarios (imágenes, documentos), texto, es un estilo de arquitectura de software enfocado en el intercambio de recursos y basado en *http.*

1. Recurso → URI → Acción
2. GET → PUT → DELETE (esto para peticiones REST)


**Conviene utilizar REST cuando las interacciones son simples y los recursos son limitados**

Cuando se usa JQ, retorna el archivo JSON de una forma más ordenada.

```jsx
curl https://xkcd.com/info.0.json
```

Nota: Instalar Vim para editar código en la terminal

Luego de instalarlo crea el siguiente script:

```jsx
<?php

echo file_get_contents('https://xkcd.com/info.0.json').PHP_EOL;
```

El resultado será practicamente el mismo del comando CURL. Seguido a esto, si solo se quiere mostrar una propiedad del formato JSON que se muestra al final del comando se utiliza los siguiente en el script PHP

```jsx
<?php

$json = file_get_contents('https://xkcd.com/info.0.json');
$data = json_decode( $json, true );

echo $data['img'].PHP_EOL;
```

**Nota:** Si alguno de los scripts salta un error, debe revisar el archivo php.ini, y quitar el comentario en `;extension=openssl`

**LOS VERBOS UTILIZADOS EN HTTP**

GET → Buscar recursos y traerlos

POST → Crear recursos en el servidor

PUT → Modificar un recurso en el servidor

DELETE → Eliminar un recurso en el servidor

Para poner en ejecución el servidor que viene por defecto en php, se ha de utilizar el siguiente comando en el cmd:

```php
php -S localhost:8000 server.php
```

La estructura es simple: *php -S (server) [localhost:8000](http://localhost:8000) (puerto donde se escucha) server.php (el archivo que se elige como raíz)*

**CUANDO ES UN SISTEMA DE AUTENTICACIÓN**

```php
$user = array_key_exists( 'PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
$pwd = array_key_exists( 'PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : '';

if( $user !== 'fabian' || $pwd !== 'Admin1234'){
    echo "no podrás ingresar";
    die;
}
```

Para el sistema de autenticación, la ejecución por medio de la terminal se realiza de la siguiente forma:

```php
curl http://mauro:1234@localhost:8000/books
```

Estableciendo que el usuario y la contraseña van antes del [localhost](http://localhost) y siendo separadas por “:”. para ser más claro “*mauro:1234” **→ También es llamada Autenticación HTTP***

**Nota:** La autenticación HTTP es muy poco segura, las credenciales se envían en cada petición/request.

De igual forma es ineficiente, la autenticación se realiza en cada petición.

**HMAC → MÉTODO DE AUTENTICACIÓN MÁS SEGURO**

Son las siglas para *Hash Made Authorization Code,* un hash es una función que convierte un texto en un token.

1. **HASH →** Difícil de romper, que sea conocida por el cliente y servidor.
2. **CLAVE SECRETA →** Solamente la pueden saber el cliente y el servidor, será utilizada para corroborar el hash.
3. **UID →** El id del usuario, será utilizado dentro de la función hash junto con la clave secreta y un timestamp.

```php
if (
    !array_key_exists('HTTP_X_HASH', $_SERVER) ||
    !array_key_exists('HTTP_X_TIMESTAMP', $_SERVER) ||
    !array_key_exists('HTTP_X_UID', $_SERVER)
){
    die;
}

list( $hash, $uid, $timestamp) = [
    $_SERVER['HTTP_X_HASH'],
    $_SERVER['HTTP_X_TIMESTAMP'],    
    $_SERVER['HTTP_X_UID'],
];

//Se utiliza la llave secreta que solo conoce el servidor y el cliente
$secret = 'sh! No se lo cuentes a nadie!';

$newHash = sha1($uid.$timestamp.$secret);

if( $newHash !== $hash ) {
    die;
}
```

```php
curl http://localhost:8000/books -H 'X-HASH: ******' -H 'X-UID: 1' -H 'X-TIMESTAMP:*****'
```

**ACCESS TOKENS / ACCESO TEMPORAL**

Esta forma es la más compleja de todas, pero también es la forma más segura utilizada para información muy sensible. El servidor al que le van a hacer las consultas se va a partir en 2:

- Uno se va a encargar específicamente de la autenticación.
- El otro se va a encargar de desplegar los recursos de la API.

El flujo de la petición será el siguiente:

1. Nuestro usuario hace una petición al servidor de autenticación para pedir un token.
2. El servidor le devuelve el token.
3. El usuario hace una petición al servidor para pedir recursos de la API.
4. El servidor con los recursos hace una petición al servidor de autenticación para verificar que el token sea válido.
5. Una vez verificado el token, el servidor le devuelve los recursos al cliente.

**Son 3 actores los que vienen involucrados**


```php
header( 'Content-Type: application/json' );

if ( !array_key_exists( 'HTTP_X_TOKEN', $_SERVER ) ) {

	die;
}

$url = 'https://'.$_SERVER['HTTP_HOST'].'/auth';

$ch = curl_init( $url );
curl_setopt( $ch, CURLOPT_HTTPHEADER, [
	"X-Token: {$_SERVER['HTTP_X_TOKEN']}",
]);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$ret = curl_exec( $ch );

if ( curl_errno($ch) != 0 ) {
	die ( curl_error($ch) );
}

if ( $ret !== 'true' ) {
	http_response_code( 403 );

	die;
}
```

**TRATAMIENTO DE ERRORES EN EL SERVIDOR Y CLIENTE**

En el archivo de servidor ya se encuentran los cambios realizados, voy a comentarlos para cualquier consulta o excepción, pero en el cliente el código es el siguiente:

```php
<?php

$ch = curl_init( $argv[1] );
curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);

$response = curl_exec( $ch );
$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

switch ( $httpCode ) {
        case 200:
            echo 'Todo ha salido bien';
            break;
        case 400:
            echo 'Pedido incorrecto';
            break;
        case 404:
            echo 'Recurso no encontrado';
            break;
        case 500:
            echo 'El servidor falló';
            break;
}
```

**COMUNICACIÓN FRONTEND / BACKEND**

Es muy común tener comunicaciones con API REST al momento de tener una aplicación de una sola página o SPA, ya sea para obtener o guardar datos. Esta comunicación se realizar a través de AJAX (*Asynchronous Javascript XML).* La clave es la parte de asincronismo pues el cliente no se queda bloqueado.

Ejecutar el servidor sin definir ningún archivo:

```php
php -S localhost:8000
```

Una forma de cargar los datos en el Javascript
