<?php

namespace tdt4237\webapp\models;

class File
{
    protected $id;
    protected $name;
    protected $type;
    protected $hash;
    protected $time;

    function __construct($id, $name, $type, $hash, $time)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->hash = $hash;
        $this->time = $time;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getTime()
    {
        return $this->time;
    }
}
