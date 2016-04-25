<?php
namespace Cyneek\LaravelMultipleStapler\Models;


use App;
use Cyneek\LaravelMultipleStapler\Classes\Attachment;

/**
 * Class MultipleFileTrait
 *
 * Trait that must be used in each class that holds a Multiple stapler file parameter
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 */
trait MultipleFileTrait
{
    /**
     * All of the instance's current file attachments.
     *
     * @var Attachment[]
     */
    protected $attachedModels = [];

    /**
     * Adds a new attachment object that will handle the instance's
     * attached file models via the defined $name param.
     *
     * @param String $name
     * @param array $options
     * @param String $model // If you want to declare a different model than StaplerFiles to handle
     *                      // the files.
     */
    public function hasAttachedFile($name, array $options = [], $model = NULL)
    {
        $attachment = new Attachment($this, $name, $options, $model);

        $this->attachedModels[$name] = $attachment;
    }

    /**
     * Adds a new attachment object that will handle the instance's
     * attached file models via the defined $name param.
     *
     * @param String $name
     * @param array $options
     * @param String $model // If you want to declare a different model than StaplerFiles to handle
     *                      // the files.
     */
    public function hasMultipleAttachedFiles($name, array $options = [], $model = NULL)
    {
        $attachment = new Attachment($this, $name, $options, $model, TRUE);

        $this->attachedModels[$name] = $attachment;
    }

    /**
     * Instance's boot method
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($instance)
        {
            foreach ($instance->attachedModels as $attachedModel)
            {
                $attachedModel->afterSave();
            }
        });

        static::deleting(function ($instance)
        {
            foreach ($instance->attachedModels as $attachedModel)
            {
                $attachedModel->beforeDelete();
            }
        });

    }



    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        // If $name corresponds with a attached field
        if (array_key_exists($name, $this->attachedModels))
        {
            return $this->attachedModels[$name]->makeRelations();
        }

        return parent::__call($name, $arguments);
    }


    /**
     * Handle the dynamic retrieval of attached files.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        // If $key corresponds with a attached field
        if (array_key_exists($key, $this->attachedModels))
        {
            return $this->attachedModels[$key]->loadAttachments();
        }

        return parent::getAttribute($key);
    }

    /**
     * Handle the dynamic setting of attachment objects.
     *
     * @param string $key
     * @param mixed $value
     */
//    public function setAttribute($key, $value)
//    {
//        if (array_key_exists($key, $this->attachedModels))
//        {
//            if ($value)
//            {
//                /** @var Attachment $attachedFile */
//                $attachedFile = $this->attachedModels[$key];
//                //    $attachedFile->setUploadedFile($value);
//            }
//
//            return;
//        }
//
//        parent::setAttribute($key, $value);
//    }

    /**
     * Get all of the current attributes and attachment objects on the model.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return array_merge($this->attachedModels, parent::getAttributes());
    }

}