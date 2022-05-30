<?php

namespace BeeAZ\DeathChestPro;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\world\Position;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Chest;

class DeathChestPro extends PluginBase implements Listener{
  
  public function onEnable(): void{
   $this->getServer()->getPluginManager()->registerEvents($this, $this);
   $this->saveDefaultConfig();
  }
  
  public function onDeath(PlayerDeathEvent $event){
   $player = $event->getPlayer();
   $cfg = $this->getConfig()->getAll();
   $x = (int) $player->getPosition()->getX();
   $y = (int) $player->getPosition()->getY();
   $z = (int) $player->getPosition()->getZ();
   $world = $player->getWorld();
   $this->getServer()->broadcastMessage(str_replace(["{x}", "{y}", "{z}", "{world}", "{player}"], [$x, $y, $z, $world->getFolderName(), $player->getName()], $cfg["message"]));
   $world->setBlock(new Position($x, $y, $z, $world), VanillaBlocks::CHEST());
   $world->setBlock(new Position($x + 1, $y, $z, $world), VanillaBlocks::CHEST());
   $tile = $world->getTile(new Position($x, $y, $z, $world));
   $tile2 = $world->getTile(new Position($x + 1, $y, $z, $world));
     if($tile instanceof Chest && $tile2 instanceof Chest){
       $tile->pairWith($tile2);
       $tile2->pairWith($tile);
         foreach($player->getInventory()->getContents() as $item){
           $tile->getInventory()->addItem($item);
           $player->getInventory()->clearAll();
         }
         foreach($player->getArmorInventory()->getContents() as $item){
           $tile->getInventory()->addItem($item);
           $player->getArmorInventory()->clearAll();
        }
      }
    }
  }