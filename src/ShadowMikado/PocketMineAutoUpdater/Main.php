<?php

declare(strict_types=1);

namespace ShadowMikado\PocketMineAutoUpdater;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use ShadowMikado\PocketMineAutoUpdater\Updater\UpdateCheckerMixin;

class Main extends PluginBase implements Listener
{
    use SingletonTrait;

    public function onLoad(): void
    {

        $Updater = new UpdateCheckerMixin($this->getServer());
        $Updater->doCheck();

        self::setInstance($this);

        if (!is_dir($this->getDataFolder() . "update")) {
            @mkdir($this->getDataFolder() . "update");
        }

        if (!is_dir($this->getDataFolder() . "old")) {
            @mkdir($this->getDataFolder() . "old");
        }
    }



    public function onEnable(): void
    {
    }

    public function onDisable(): void
    {
    }
}
