<?php

declare(strict_types=1);

namespace BeeAZ\DeathChestPro;

use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;
use pocketmine\world\World;

class DeathChestPro extends PluginBase implements Listener {
	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
	}

	private function initChest(World $world, Player $player, int $x, int $y, int $z): void {
		$world->setBlock(new Position($x, $y, $z, $world), VanillaBlocks::CHEST());
		$world->setBlock(new Position($x + 1, $y, $z, $world), VanillaBlocks::CHEST());
		$tile = $world->getTile(new Position($x, $y, $z, $world));
		$tile2 = $world->getTile(new Position($x + 1, $y, $z, $world));
		$this->getServer()->broadcastMessage(str_replace(["{x}", "{y}", "{z}", "{world}", "{player}"], [$x, $y, $z, $world->getFolderName(), $player->getName()], $this->getConfig()->get("message")));
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

	private function handleY(World $world, Player $player, int $x, int $y, int $z, bool $isInWorld): void {
		$maxY = $world->getMaxY();
		$minY = $world->getMinY();
		if ($isInWorld) {
			$this->initChest($world, $player, $x, $y, $z);
		} else {
			if ($y > $maxY) {
				$y = $maxY - 1; // Blame Shogi!
				$this->initChest($world, $player, $x, $y, $z);
				return;
			}
			if ($y < $minY) {
				$y = $minY + 1; // + 1 to avoid breaking bedrock
				$this->initChest($world, $player, $x, $y, $z);
				return;
			}
		}
	}

	public function onDeath(PlayerDeathEvent $event): void {
		$event->setKeepInventory(true);
		$player = $event->getPlayer();
		$playerPos = $player->getPosition();
		$x = (int) $playerPos->getX();
		$y = (int) $playerPos->getY();
		$z = (int) $playerPos->getZ();
		$world = $player->getWorld();
		if (count($player->getInventory()->getContents()) == 0 && count($player->getArmorInventory()->getContents()) == 0) {
			return;
		}
		if ($world->isInWorld($x, $y, $z)) {
			$this->handleY($world, $player, $x, $y, $z, true);
		} else {
			$this->handleY($world, $player, $x, $y, $z, false);
		}
	}
}
