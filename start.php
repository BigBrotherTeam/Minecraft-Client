<?php

/*
 *▪   ▄▄▄·       ▄▄· ▄ •▄ ▄▄▄ .▄▄▄▄▄
 *██ ▐█ ▄█▪     ▐█ ▌▪█▌▄▌▪▀▄.▀·•██
 *▐█· ██▀· ▄█▀▄ ██ ▄▄▐▀▀▄·▐▀▀▪▄ ▐█.▪
 *▐█▌▐█▪·•▐█▌.▐▌▐███▌▐█.█▌▐█▄▄▌ ▐█▌·
 *▀▀▀.▀    ▀█▄▀▪·▀▀▀ ·▀  ▀ ▀▀▀  ▀▀▀
 *
 *This program is free software:
 *ComputerEdition Packet Analyze.
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
        $class->getLogger()->info("Thank you for using MinecraftClient by iPocket!");
    }else{
        echo "It cannot start from web.<br> Please start from a command-line<br>";
    }
}
