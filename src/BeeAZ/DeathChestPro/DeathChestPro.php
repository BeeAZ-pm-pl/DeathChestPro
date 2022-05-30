<?php

declare(strict_types=1);

namespace BeeAZ\DeathChestPro;

use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;

class DeathChestPro extends PluginBase implements Listener {
	public function onEnable() : void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
	}

	public function onDeath(PlayerDeathEvent $event) {
		$player = $event->getPlayer();
		$cfg = $this->getConfig()->getAll();
		$x = intval($player->getPosition()->getX());
		$y = intval($player->getPosition()->getY());
		$z = intval($player->getPosition()->getZ());
		$world = $player->getWorld();
		$this->getServer()->broadcastMessage(str_replace(["{x}", "{y}", "{z}", "{world}", "{player}"], [$x, $y, $z, $world->getFolderName(), $player->getName()], $cfg["message"]));
		$world->setBlock(new Position($x, $y, $z, $world), VanillaBlocks::CHEST());
		$world->setBlock(new Position($x + 1, $y, $z, $world), VanillaBlocks::CHEST());
		$chestTile = $world->getTile(new Position($x, $y, $z, $world));
		$doubleChestTile = $world->getTile(new Position($x + 1, $y, $z, $world));
		if ($chestTile instanceof Chest && $doubleChestTile instanceof Chest) {
			$chestTile->pairWith($doubleChestTile);
			$doubleChestTile->pairWith($chestTile);
			foreach ($player->getInventory()->getContents() as $item) {
				$chestTile->getInventory()->addItem($item);
				$player->getInventory()->clearAll();
			}
			foreach ($player->getArmorInventory()->getContents() as $item) {
				$chestTile->getInventory()->addItem($item);
				$player->getArmorInventory()->clearAll();
			}
		}
	}
}
