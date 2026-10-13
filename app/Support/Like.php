<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * "Contains" searches where % and _ in the user's term match literally.
 * "!" is the escape character because it behaves the same on MySQL and SQLite.
 */
class Like
{
    public static function pattern(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }

    /**
     * @param  string  $column  a trusted column name, never user input
     */
    public static function where(Builder $query, string $column, string $term, string $boolean = 'and'): Builder
    {
        return $query->whereRaw("{$column} like ? escape '!'", [self::pattern($term)], $boolean);
    }
}
