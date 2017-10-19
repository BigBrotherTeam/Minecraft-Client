<?php

/**
 *  ______  __         ______               __    __
 * |   __ \|__|.-----.|   __ \.----..-----.|  |_ |  |--..-----..----.
 * |   __ <|  ||  _  ||   __ <|   _||  _  ||   _||     ||  -__||   _|
 * |______/|__||___  ||______/|__|  |_____||____||__|__||_____||__|
 *             |_____|
 *
 * BigBrother plugin for PocketMine-MP
 * Copyright (C) 2014-2015 shoghicp <https://github.com/shoghicp/BigBrother>
 * Copyright (C) 2016- BigBrotherTeam
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author BigBrotherTeam
 * @link   https://github.com/BigBrotherTeam/BigBrother
 *
 */

namespace MinecraftClient{
    use MinecraftClient\MinecraftClient;
    use MinecraftClient\utils\ClassLoader;

    require_once(__DIR__ . "/src/MinecraftClient/utils/ClassLoader.php");

    $loader = new ClassLoader();
    $loader->addPath(__DIR__ . "/src");
    $loader->register();

    if(php_sapi_name() === "cli"){
        $class = new MinecraftClient(__DIR__);
        $class->getLogger()->info("Thank you for using MinecraftClient by BigBrotherTeam!");
    }else{
        echo "It cannot start from web.<br> Please start from a command-line<br>";
    }
}
