<?php
namespace Cyneek\LaravelMultipleStapler\Interfaces;


/**
 * Interface LaravelStaplerInterface
 * Needed to be implemented in every model that handles
 * attached files.
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 */
interface LaravelStaplerInterface
{

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     */
    public static function registerFileEvent($callback, $priority = 0);

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     */
    public static function forgetFileEvent();

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function fileable();

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function hasAttachedFile($name, array $options = []);

    /**
     * Return the image paths (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function pathsForAttachment($attachmentName);

    /**
     * Return the image urls (across all styles) for a given attachment.
     *
     * @param  string $attachmentName
     * @return array
     */
    public function urlsForAttachment($attachmentName);
}