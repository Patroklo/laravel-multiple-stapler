<?php
namespace Cyneek\LaravelMultipleStapler\Models;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Cyneek\LaravelMultipleStapler\Interfaces\LaravelStaplerInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StaplerFiles
 *
 * Will handle all the file and data storing of each Stapler file parameter.
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 *
 * @property integer $id
 * @property integer $fileable_id
 * @property string $fileable_type
 * @property string $fileable_field
 * @property string $file_file_name
 * @property integer $file_file_size
 * @property string $file_updated_at
 * @property string $file_created_at
 * @property $created_at string
 * @property $updated_at string
 */
class StaplerFiles extends Model implements StaplerableInterface, LaravelStaplerInterface
{
    use EloquentTrait;

    public $table = 'stapler_files';

    protected static $fileEventName = 'eloquent.registerStaplerFiles';

    protected $fillable = ['file'];

    /**
     * StaplerFiles constructor.
     * @param array $attributes
     */
    function __construct(array $attributes = [])
    {
        $this->fireFileEvent();

        parent::__construct($attributes);
    }


    protected function fireFileEvent()
    {
        if (isset(static::$dispatcher))
        {
            static::$dispatcher->fire(static::$fileEventName, $this);
        }
    }


    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     */
    public static function registerFileEvent($callback, $priority = 0)
    {
        if (isset(static::$dispatcher))
        {
            static::$dispatcher->listen(static::$fileEventName, $callback, $priority);
        }
    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @return void
     */
    public static function forgetFileEvent()
    {
        if (isset(static::$dispatcher))
        {
            static::$dispatcher->forget(static::$fileEventName);
        }
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        // If $name corresponds with a attached field
        if (method_exists($this->file, $name))
        {
            return $this->file->$name($arguments);
        }

        return parent::__call($name, $arguments);
    }


    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function fileable()
    {
        return $this->morphTo();
    }

}