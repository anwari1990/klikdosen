<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Database;

use ArrayAccess;
use Carbon\Carbon;
use JsonSerializable;
use DateTimeInterface;

abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * Set table name
     *
     * @var string
     */
    protected $table;
    /**
     * We need dependency on the application for get a model instance through the constructor
     * and get a connection after register the database
     *
     * @var \Mild\App
     */
    protected static $app;
    /**
     * @var bool
     */
    public $exists = false;
    /**
     * @var array
     */
    protected $original = [];
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * Set primary key on the relation
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Set date format on the time
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * @param $model
     * @param string $relation
     * @param string $primaryKey
     * @param string $foreignKey
     * @return Relations\BelongsTo
     */
    public function belongsTo($model, $relation = '', $primaryKey = '', $foreignKey = '')
    {
        if ($model instanceof Model === false) {
            $model = $model::instance();
        }
        if ($relation === '') {
            $relation = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1]['function'];
        }
        if ($primaryKey === '') {
            $primaryKey = $model->getPrimaryKey();
        }
        if ($foreignKey === '') {
            $foreignKey = $relation.'_'.$primaryKey;
        }
        return new Relations\BelongsTo($model->query()->where($primaryKey, '=', $this->{$foreignKey}));
    }

    /**
     * @param $model
     * @param string $foreignKey
     * @param string $primaryKey
     * @return Relations\HasMany
     */
    public function hasMany($model, $foreignKey = '', $primaryKey = '')
    {
        if ($model instanceof Model === false) {
            $model = $model::instance();
        }
        if ($foreignKey === '') {
            $segments = explode('\\', static::class);
            $foreignKey = strtolower(end($segments)).'_'.$model->getPrimaryKey();
        }
        if ($primaryKey === '') {
            $primaryKey = $this->getPrimaryKey();
        }
        return new Relations\HasMany($model->query()->where($foreignKey, $this->{$primaryKey}));
    }

    /**
     * @param $model
     * @param string $foreignKey
     * @param string $primaryKey
     * @return Relations\HasOne
     */
    public function hasOne($model, $foreignKey = '', $primaryKey = '')
    {
        if ($model instanceof Model === false) {
            $model = $model::instance();
        }
        if ($foreignKey === '') {
            $class = explode('\\', static::class);
            $foreignKey = strtolower(end($class)).'_'.$model->getPrimaryKey();
        }
        if ($primaryKey === '') {
            $primaryKey = $this->getPrimaryKey();
        }
        return new Relations\HasOne($model->query()->where($foreignKey, $this->{$primaryKey}));
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setOriginal($attributes)
    {
        $this->original = $attributes;
        return $this;
    }

    /**
     * @param $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this->setOriginal($attributes);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        if (!$this->hasAttribute($name)) {
            if (method_exists($this, $name)) {
                $this->setAttribute($name, $default = $this->$name()->get());
            }
            return $default;
        }
        $value = $this->attributes[$name];
        if (strtotime($value) !== false) {
            $parsers = date_parse($value);
            if (checkdate($parsers['month'], $parsers['day'], $parsers['year'])) {
                $value = Carbon::create($value);
            }
        }
        return $value;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function setAttribute($name, $value)
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format($this->dateFormat);
        }
        $this->attributes[$name] = $value;
    }

    /**
     * @param $name
     * @return void
     */
    public function putAttribute($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->attributes[$name]);
        }
    }

    /**
     * @param \Mild\App $app
     * @return void
     */
    public static function setApp($app)
    {
        static::$app = $app;
    }

    /**
     * @return \Mild\App
     */
    public static function getApp()
    {
        return static::$app;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        if (is_null($this->table)) {
            $table = explode('\\', strtolower(static::class));
            $table = end($table);
            if ($table[-1] !== 's') {
                $table .= 's';
            }
            $this->table = $table;
        }
        return $this->table;
    }

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
        return $this;
    }

    /**
     * @return static
     * @throws \ReflectionException
     */
    public static function instance()
    {
        return static::$app->instance(static::class);
    }

    /**
     * @param array $attributes
     * @param bool $exists
     * @return Model
     * @throws \ReflectionException
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $model = static::instance()->setAttributes($attributes);
        $model->exists = $exists;
        return $model;
    }

    /**
     * @return int
     * @throws \ReflectionException
     */
    public function save()
    {
        $query = $this->query();
        if ($this->exists === false) {
            if (($saved = $query->insert($this->attributes)) !== false) {
                $this->setAttribute($this->primaryKey, $query->getDatabase()->lastInsertId());
            }

        } else {
            $values = [];
            foreach ($this->attributes as $key => $value) {
                if ($this->original[$key] !== $value) {
                    $values[$key] = $value;
                }
            }
            $saved = $query->where($this->primaryKey, '=', $this->original[$this->primaryKey])->update($values);
        }
        $this->original = $this->attributes;
        return $saved;
    }

    /**
     * @return Queries\Query
     * @throws \ReflectionException
     */
    public function query()
    {
        return static::$app->get('db')->table($this->getTable())->setModel($this);
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        return $this->query()->$name(...$arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->$name(...$arguments);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        $this->putAttribute($name);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->putAttribute($offset);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
