<?php
namespace Cyneek\LaravelMultipleStapler\Classes;

use Cyneek\LaravelMultipleStapler\Interfaces\LaravelStaplerInterface;
use Request;
use App;

/**
 * Class Attachment
 *
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 */
class Attachment
{

    /**
     * @var string
     */
    var $name;

    /**
     * @var array
     */
    var $options;

    /**
     * @var bool
     */
    var $multiple;

    /**
     * @var \Eloquent
     */
    var $instance;

    /**
     * @var String
     */
    var $model;

    /**
     * Attachment constructor.
     * @param \Eloquent $instance
     * @param string $name
     * @param array $options
     * @param string $model
     * @param bool $multiple
     */
    function __construct($instance, $name, array $options, $model, $multiple = FALSE)
    {
        $this->instance = $instance;
        $this->name = $name;
        $this->options = $options;
        $this->multiple = $multiple;

        if (is_null($model))
        {
            $this->model = LaravelStaplerInterface::class;
        }
        else
        {
            $this->model = $model;
        }
    }

    /**
     * Saves the new files uploaded in the request
     * into the system and links them into the instance
     * via Polymorphic relation.
     *
     */
    public function afterSave()
    {
        // 1 - Get the files for this model
        $requestFiles = $this->getRequestFiles();

        if (empty($requestFiles))
        {
            return NULL;
        }

        $this->registerAttachmentEvents();

        $fieldName = $this->name;

        if ($this->multiple == FALSE && $this->instance->$fieldName)
        {
            $this->instance->$fieldName->delete();
        }

        foreach ($requestFiles as $requestFile)
        {
            $this->saveAttachments($requestFile);
        }

        $this->forgetAttachmentEvents();
    }

    /**
     * Will delete all files and attached models from the
     * declared instance.
     *
     */
    public function beforeDelete()
    {
        $fieldName = $this->name;

        $fileModels = $this->instance->$fieldName;

        if ($this->multiple == FALSE)
        {
            $fileModels = [$fileModels];
        }

        foreach ($fileModels as $file)
        {
            $file->delete();
        }
    }


    /**
     * Adds a new event into the instance's attachment class
     * that will be used to add the attachment field configuration
     * into the system every time the system loads a new object of
     * this class.
     *
     */
    public function registerAttachmentEvents()
    {
        $modelClass = $this->getModelClass();

        $modelClass::registerFileEvent(function (LaravelStaplerInterface $staplerFileModel)
        {
            $staplerFileModel->hasAttachedFile('file', $this->options);
        });
    }


    /**
     * Deletes all the events attached to the instance's attachment class
     * referencing its defined fieldEventName
     *
     * Should be used after calling the registerAttachmentEvents method
     */
    public function forgetAttachmentEvents()
    {
        $modelClass = $this->getModelClass();

        $modelClass::forgetFileEvent();
    }


    /**
     * Returns the classname assigned as model attachment to the interface.
     *
     * @return LaravelStaplerInterface
     */
    protected function getModelClass()
    {
        return with(App::make($this->model))->getMorphClass();
    }


    /**
     * Will stablish a one on one or one to may polimorphic relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|\Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function makeRelations()
    {
        /** @var string $modelClass */
        $modelClass = $this->getModelClass();

        if ($this->multiple)
        {
            /** @var \Illuminate\Database\Eloquent\Relations\MorphMany MorphMany $relation */
            $relations = $this->instance->morphMany($modelClass, 'fileable');
            $query = $relations->getQuery();
            $query->where('fileable_field', $this->name);
        }
        else
        {
            /** @var \Illuminate\Database\Eloquent\Relations\MorphOne $relation */
            $relations = $this->instance->morphOne($modelClass, 'fileable');
            $query = $relations->getQuery();
            $query->where('fileable_field', $this->name);
        }

        return $relations;
    }


    /**
     * Method that will load all the attached files of the declared instance
     * Will be called from MultipleFileTrait.
     *
     * @return mixed
     */
    public function loadAttachments()
    {
        $this->registerAttachmentEvents();

        $relations = $this->makeRelations();

        $this->instance->setRelation($this->name, $results = $relations->getResults());

        $this->forgetAttachmentEvents();

        return $results;
    }


    /**
     * Returns all the uploaded files linked to the field assigned
     * in the $name attribute.
     *
     * @return array|\Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected function getRequestFiles()
    {
        if (Request::hasFile($this->name) === FALSE)
        {
            return [];
        }

        $requestFiles = Request::file($this->name);

        if (!is_array($requestFiles))
        {
            $requestFiles = [$requestFiles];
        }

        if ($this->multiple == FALSE)
        {
            $requestFiles = [reset($requestFiles)];
        }

        return $requestFiles;
    }

    /**
     * Returns a new object defined by the $model string
     *
     * @return \Eloquent
     */
    protected function getAttachedModel()
    {
        // Uses make method to use the Service Container
        $this->registerAttachmentEvents();
        $model = App::make($this->model);
        $this->forgetAttachmentEvents();

        return $model;
    }

    /**
     * Saves a UploadedFile in the system and attaches it into the
     * declared instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $requestFile
     */
    protected function saveAttachments($requestFile)
    {

        $staplerFileModel = $this->getAttachedModel();

        $staplerFileModel->file = $requestFile;
        $staplerFileModel->fileable_field = $this->name;
        $fieldName = $this->name;


        $this->instance->$fieldName()->save($staplerFileModel);
    }


}