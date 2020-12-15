<?php


namespace Manager\Domain\Entities;


class AppMenu
{
    public $id;
    public $name;
    public $parent_id;
    public $url;
    public $icon;
    public $module_name;
    public $sort_value;
    public $status;
}