# Laravel Stapler Multiple File Handler

## Index

* ### Install
* ### Description
    * #### Methods
* ### Configuration of Stapler properties
    * #### Declaring single file properties
    * #### Declaring multiple file properties
* ### File insertion
    * #### Inserting files in single file properties
    * #### Inserting files in multiple file properties
* ### Acessing uploaded file data
    * #### Acessing single file parameters
    * #### Acessing multiple file parameters
* ### Deleting linked files
    * #### Explicit deletion of single file properties
    * #### Explicit deletion of multiple file properties

## Install

Add this package with composer with the following command:

```bash
composer require cyneek/laravel-multiple-stapler
```

After updating composer, add the service providers to the `providers` array in `config/app.php`.

```php
Codesleeve\LaravelStapler\Providers\L5ServiceProvider::class,
Cyneek\LaravelMultipleStapler\LaravelMultipleStaplerProvider::class
```

From the command line, use the migration tool; it will make a basic table in charge of handling all the data from the files linked to the application Models.

```php
php artisan migrate
```

Aaaaand it's done! 


## Description

### Methods

* **hasAttachedFile**: Method to link a parameter with only one file.

* **hasMultipleAttachedFiles:** Method that allows linking multiple files gallery-like into one parameter.

The parameters that both methods accept are:

 * **name**: [String|Required] The name that will be assigned to the file parameter in the Model.
 
 * **options**: [Array|Required] Stapler options that wil be used to handle the linked file. If you want to know more about the available options in this library, [you can click here to read the Stapler oficial documentation.](https://github.com/CodeSleeve/stapler)
 
 * **attachedModelClass**: [String|Optional] Here you can define a model classname that will be used to handle the file information instead of the default one: `StaplerFiles`. The Model must implement the `LaravelStaplerInterface` interface.

## Configuration of Stapler properties

### Declaring single file properties

This is the usual behavior in the Stapler library for Laravel. It lets uploading one single file and linking it into a loaded Model property, allowing all the usual operations that could be made in a vanilla Stapler file attachment.

To make a new property that links to a single file in a Model, you have to follow the next steps:

1. Add the `MultipleFileTrait` trait into the model
```php
class Example extends \Eloquent
{
    use MultipleFileTrait;
```

2. Add into the `__construct()` method the properties you want to add using `hasAttachedFile` method.

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

Warning: It's required to add the parameter creation methods BEFORE the construct parent calling.

### Declaring multiple file properties

This behavior it's possible thanks to the polymorphyc tables from Laravel, they will store all the file data linking it with their parent Model objects thanks to the fields `fileable_id`, `fileable_type` and `fileable_field` that will store the parameter name.

To make a multiple file handler property in a model, you have to follow the next steps:

1. Add the `MultipleFileTrait` trait into the model
```php
class Example extends \Eloquent
{
    use MultipleFileTrait;
```

2. Add into the `__construct()` method the properties you want to add using `hasMultipleAttachedFiles` method.

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
Warning: It's required to add the parameter creation methods BEFORE the construct parent calling.

## File insertion

### Inserting files in single file properties

For this example we will use a form that will upload a single file into our Model property. It's possible to use form fields that accept multiple files, but in those cases, all except the first uploaded file will be automatically discarded.

You must keep in mind that when you are making an `update` operation with a form, if there is a previous file linked in that property, it will be automatically deleted if you upload a newer one.

#### Form view

```php

<?= Form::open(['url' => action('ExampleController@store'), 'method' => 'POST', 'files' => true]) ?>
    <?= Form::input('name') ?>
    <?= Form::input('description') ?>
    <?= Form::file('avatar') ?>
    <?= Form::submit('save') ?>
<?= Form::close() ?>

```

#### Controller handler

```php

public function store()
{
    // Create and save a new Example object, mass assigning all of the input fields (including the 'avatar' file field).
    $example = Example::create(Input::all());
}

```

This is the minimum code required to upload a file with a form and linking it into a new object Model. To learn about acessing file data, please read above in this Readme.

### Inserting files in multiple file properties

It's required to have a Model with a file parameter capable of handling multiple files throught the `hasMultipleAttachedFiles` method.

#### Form view

```php

<?= Form::open(['url' => action('ExampleController@storeMultiple'), 'method' => 'POST', 'files' => true]) ?>
    <?= Form::input('name') ?>
    <?= Form::input('description') ?>
    <!-- Note that the field name now it's written as an array and the additional option in the file() method -->
    <?= Form::file('avatar[]', ['multiple' => true]) ?>
    <?= Form::submit('save') ?>
<?= Form::close() ?>

```

#### Controller handler

```php

public function storeMultiple()
{
    // Create and save a new Example object, mass assigning all of the input fields (including the 'avatar' file field).
    $example = Example::create(Input::all());
}

```

As you can see, the only difference when working with multiple or single files it's in the Model definition and the form. The system will handle the storage of every uploaded file and will link it into the loaded Model.

### Acessing uploaded file data

#### Acessing single file parameters

To access the linked file data from a loaded object, you only have to call it's parameter name as if it was a normal Laravel relation.

```php
    $example = Example::find(1);
   
    $example->avatar->createdAt();
```


#### Acessing multiple file parameters

The only difference with this case in particular is that, instead of returning an Attached object as in the previous example, it will return a Collection with all the linked files, so you'll have to go over every file object as if it were an array if you want to interact with them.

```php

    $example = Example::find(1);
   
    foreach ($example->avatar as $avatar)
    {
        echo $avatar->file->createdAt();
    }

```



### Deleting linked files

You have to always keep in mind that, if you delete a parent object with attached linked files, those will also be automatically deleted, so:

```php

    Example::delete(1);

```

Would delete the Example object with id 1 and all its linked files.


#### Explicit deletion of single file properties

It would be the same modus operandi as with the polymorphyc relations from Laravel.

```php

    $example->avatar()->delete();

```

#### Explicit deletion of multiple file properties

In this case, you'll need to go over every file object to delete it.

```php

    foreach ($example->avatar as $avatar)
    {
        echo $avatar->delete();
    }
   
```