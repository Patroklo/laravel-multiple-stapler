# Manejador de múltiples ficheros para Laravel Stapler

## Índice

* [Instalación](#instalación)
* [Descripción](#descripción)
    * [Métodos](#métodos)
* [Configuración de las propiedades Stapler](#configuración-de-las-propiedades-stapler)
    * [Declarar propiedades de un único fichero](#declarar-propiedades-de-un-único-fichero)
    * [Declarar propiedades de múltiples ficheros](#declarar-propiedades-de-múltiples-ficheros)
* [Inserción de ficheros](#inserción-de-ficheros)
    * [Insertar ficheros en propiedades de un único fichero](#insertar-ficheros-en-propiedades-de-un-único-fichero)
    * [Insertar ficheros en propiedades de un múltiples ficheros](#insertar-ficheros-en-propiedades-de-múltiples-ficheros)
* [Acceso de datos de archivo](#acceso-de-datos-de-archivo)
    * [Parámetros de un único fichero](#parámetros-de-un-único-fichero)
    * [Parámetros de un múltiples ficheros](#parámetros-con-múltiples-ficheros)
* [Borrado de archivos enlazados.](#borrado-de-archivos-enlazados)
    * [Borrado explícito para parámetros de un único fichero](#borrado-explícito-para-parámetros-de-un-único-fichero)
    * [Borrado explícito de parámetros de múltiples ficheros](#borrado-explícito-de-parámetros-de-múltiples-ficheros)

## Instalación

Añade este paquete a composer usando el siguiente comando:

```bash
composer require cyneek/laravel-multiple-stapler
```

Después de actualizar composer, añade los proveedores de servicios al array `providers` en `config/app.php`.

```php
Codesleeve\LaravelStapler\Providers\L5ServiceProvider::class,
Cyneek\LaravelMultipleStapler\LaravelMultipleStaplerProvider::class
```

Desde la línea de comandos, usa el generador de migraciones; éste creará una tabla básica responsable de contener todos los datos de los archivos que estarán enlazados a los Models de la aplicación.

```php
php artisan migrate
```

Y ya estás listos para trabajar!


## Descripción

### Métodos

* **hasAttachedFile**: Método para vincular un parámetro con un único archivo.

* **hasMultipleAttachedFiles:** método que permite vincular múltiples archivos a un mismo parámetro al estilo galería.

Los parámetros que ambos métodos aceptan son:

 * **nombre**: [String|Obligatorio] El nombre que se le asignará al fichero en el Model.
 
 * **options**: [Array|Obligatorio] Las opciones tipo Stapler que se usarán para manipular el fichero asignado. Para más información acerca de las opciones disponibles en esta librería, [haz click en la documentación oficial de Stapler.](https://github.com/CodeSleeve/stapler)
 
 * **attachedModelClass**: [String|Opcional] Permite definir un Model que se encargará de almacenar la información de los ficheros enlazados a este parámetro diferente del estandar: `StaplerFiles`. El Model indicado aquí debe implementar el interface `LaravelStaplerInterface`. 

## Configuración de las propiedades Stapler

### Declarar propiedades de un único fichero

Este es el comportamiento normal de la librería de Stapler para Laravel. Permite subir un único fichero y enlazarlo una propiedad de un Model cargado, permitiendo todas las operaciones que se realizarían sobre un fichero subido en la versión vanilla de Stapler. 

Para crear una nueva propiedad que enlace a un único fichero en un Model predeterminado hay que realizar los siguiente pasos:

1. Añade el trait `MultipleFileTrait` al model.
```php
class Example extends \Eloquent
{
    use MultipleFileTrait;
```

2. En el método `__construct()` inserta las propiedades utilizando el método `hasAttachedFile`.

```php
    function __construct(array $attributes = [] )
    {

        $this->hasAttachedFile('avatar', [
            'styles' => [
                'medium' => '300x300',
                'thumb' => '100x100'
            ]
        ]);
        
        parent::__construct($attributes);

    }
```

Atención: Es muy importante colocar los métodos creadores de parámetros ANTES de la llamada al parent del construct.

### Declarar propiedades de múltiples ficheros

Este comportamiento es posible gracias a las propiedades de las tablas polimórficas de Laravel, que almacenarán los datos de cada fichero enlazándolo a los objetos del Model padre que los contiene gracias a la definición completa de su clase, su campo principal (usualmente el id) y el nombre de la propiedad que lo contiene. Esto permite el reutilizar una misma tabla para múltiples Models y propiedades.

Para crear una propiedad que enlace múltiples ficheros a un Model predeterminado hay que realizar los siguiente pasos:

1. Añade el trait `MultipleFileTrait` al model.
```php
class Example extends \Eloquent
{
    use MultipleFileTrait;
```

2. En el método `__construct()` inserta las propiedades utilizando el método `hasMultipleAttachedFiles`.

```php
    function __construct(array $attributes = [] )
    {

        $this->hasMultipleAttachedFiles('images', [
            'styles' => [
                'medium' => '300x300',
                'thumb' => '100x100'
            ]
        ]);
        
        parent::__construct($attributes);

    }
```
Atención: Es muy importante colocar los métodos creadores de parámetros ANTES de la llamada al parent del construct.

## Inserción de ficheros

### Insertar ficheros en propiedades de un único fichero

Para este ejemplo utilizaremos un formulario que subirá un único fichero a nuestro Model. Es posible utilizar formularios que acepten múltiples archivos para este tipo de propiedades, pero tan sólo guardarán el primero que se suba. El resto serán descartados.

Hay que tener en cuenta, que cuando se esté realizando una operación de `update` en el formulario, en caso de haber algún archivo previamente enlazado al Model cargado, se borrará automáticamente en caso de que el formulario envíe otro fichero.

#### Vista del formulario

```php

<?= Form::open(['url' => action('ExampleController@store'), 'method' => 'POST', 'files' => true]) ?>
    <?= Form::input('name') ?>
    <?= Form::input('description') ?>
    <?= Form::file('avatar') ?>
    <?= Form::submit('save') ?>
<?= Form::close() ?>

```

#### Controller receptor

```php

public function store()
{
    // Crear y guardar un objeto Example, asignando en masa todos los campos tipo input (incluyendo el campo tipo fichero 'avatar').
    $example = Example::create(Input::all());
}

```

Este es el código mínimo necesario para poder subir un fichero mediante un formulario y enlazarlo a un objeto de un Model. La explicación de cómo acceder a los datos de éste está descrita más adelante.


### Insertar ficheros en propiedades de múltiples ficheros

Es necesario disponer de un Model que tenga un parámetro dado de alta como fichero múltiple a través del método "hasMultipleAttachedFiles".

#### Vista del formulario

```php

<?= Form::open(['url' => action('ExampleController@storeMultiple'), 'method' => 'POST', 'files' => true]) ?>
    <?= Form::input('name') ?>
    <?= Form::input('description') ?>
    <!-- Nótese que el nombre del campo ahora está descrito como si fuera un array y la opción adicional añadida con respecto el ejemplo anterior -->
    <?= Form::file('avatar[]', ['multiple' => true]) ?>
    <?= Form::submit('save') ?>
<?= Form::close() ?>

```

#### Controller receptor

```php

public function storeMultiple()
{
    // Crear y guardar un objeto Example, asignando en masa todos los campos tipo input (incluyendo el campo tipo fichero 'avatar').
    $example = Example::create(Input::all());
}

```

Como se puede ver, realmente la única diferencia a la hora de trabajar con ficheros múltiples está en la definición del Model y en el formulario. El sistema se encarga de almacenar automáticamente todos los ficheros subidos en el sistema y enlazarlos con el objeto cargado.


### Acceso de datos de archivo

#### Parámetros de un único fichero

Para acceder a los datos de un archivo enlazado a un objeto cargado tan sólo tendremos que llamar al parámetro como si se tratara de una relación de Laravel.

```php

    $example = Example::find(1);
   
    $example->avatar->createdAt();
   

```


#### Parámetros con múltiples ficheros

La única diferencia en este caso es que, en lugar de retornar el atributo el Model enlazado directamente cuando se le llama, retorna un Collection con todos los ficheros enlazados, por lo que habrá que recorrerlos uno a uno como si se tratara de un array si queremos interactuar con ellos.


```php

    $example = Example::find(1);
   
    foreach ($example->avatar as $avatar)
    {
        echo $avatar->file->createdAt();
    }

```



### Borrado de archivos enlazados.

Hay que tener en cuenta que, si borramos el objeto padre que tiene enlazados los ficheros a través de los parámetros Stapler, éstos ficheros se borrarán automáticamente, así pues:

```php

    Example::delete(1);

```

Borraría el objeto Example de id 1 y también todos los ficheros que tenga enlazados.



#### Borrado explícito para parámetros de un único fichero

Sería el mismo modus operandi que con los enlaces de tipo polimórfico de Laravel.

```php

    $example->avatar()->delete();

```

#### Borrado explícito de parámetros de múltiples ficheros

En este caso habría que ir fichero a fichero.

```php

    foreach ($example->avatar as $avatar)
    {
        echo $avatar->delete();
    }
   
```
