<?php
/**
 * Created by PhpStorm.
 * User: kevincollins
 * Date: 2/5/15
 * Time: 12:39 PM
 */
namespace Craft;

interface MyFactoryInterface
{
    public static function create($type);
}