<?php
namespace Cyneek\LaravelMultipleStapler\Models;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MultipleFiles
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 * 
 * @property integer $id
 * @property integer $fileable_id
 * @property string $fileable_type
 * @property string $fileable_field
 * @property string $field_file_name
 * @property integer $field_file_size
 * @property string $field_updated_at
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
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function fileable()
    {
        return $this->morphTo();
    }

}