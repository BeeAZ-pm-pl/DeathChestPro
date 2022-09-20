<?php

namespace BeeAZ\DeathChestPro;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Chest;

class DeathChestPro extends PluginBase implements Listener {

    protected function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $event->setKeepInventory(true);
        $player = $event->getPlayer();
        $playerPos = $player->getPosition();
        $x = (int) $playerPos->getX();
        $y = (int) $playerPos->getY();
        $z = (int) $playerPos->getZ();
        $world = $player->getWorld();
        $this->getServer()->broadcastMessage(str_replace(["{x}", "{y}", "{z}", "{world}", "{player}"], [$x, $y, $z, $world->getFolderName(), $player->getName()], $this->getConfig()->get("message")));
        $world->setBlock($playerPos, VanillaBlocks::CHEST());
        $world->setBlock($playerPos->add(1, 0, 0), VanillaBlocks::CHEST());
        $tile = $world->getTile($playerPos);
        $tile2 = $world->getTile($playerPos->add(1, 0, 0));
        if ($tile instanceof Chest && $tile2 instanceof Chest) {
            $tile->pairWith($tile2);
            $tile2->pairWith($tile);
            foreach ($player->getInventory()->getContents() as $item) {
                $tile->getInventory()->addItem($item);
                $player->getInventory()->clearAll();
            }
            foreach ($player->getArmorInventory()->getContents() as $item) {
                $tile->getInventory()->addItem($item);
                $player->getArmorInventory()->clearAll();
            }
        }
    }
}
