<?php

namespace ResponsiveMenuPro\Database\Migrations;
use ResponsiveMenuPro\Collections\OptionsCollection;

class Migrate_3_1_14_3_1_15 extends Migrate {

    protected $migrations = [
      'hide_on_desktop' => 'mobile_only'
    ];

}