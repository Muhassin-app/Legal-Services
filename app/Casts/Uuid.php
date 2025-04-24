<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Ramsey\Uuid\UuidInterface;
use ReflectionClass;

class Uuid implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            return \Ramsey\Uuid\Uuid::fromString($value);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        $valueType = rescue(fn () => (new ReflectionClass($value))->getName(), gettype($value));

        throw new \TypeError(sprintf(
            '[%s::%s] should be an instance of [%s] or a string... found [%s].',
            (new ReflectionClass($model))->getName(),
            $key,
            UuidInterface::class,
            $valueType,
        ));
    }
}
