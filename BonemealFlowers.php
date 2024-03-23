<?php

/*
__PocketMine Plugin__
name=BonemealFlowers
description=Allows the use of bonemeal on flowers
version=1.0
author=Kran
class=BMFlower
apiversion=11,12,12.1
*/

class BMFlower implements Plugin{

    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
    }

    public function init(){
        $this->api->addHandler("player.block.touch", [$this, "eventHandle"]);
    }

    public function eventHandle($data, $event){
        $player = $data["player"];
        $target = $data["target"];
        $targetID = $target->getID();
        $itemHeld = $player->getSlot($player->slot);
        $itemHeldID = $itemHeld->getID();
        $itemHeldMeta = $itemHeld->getMetadata();
    
        // Check if the target is a dandelion or rose and the item held is bone meal
        if(($targetID === DANDELION || $targetID === CYAN_FLOWER) && $itemHeldID === 351 && $itemHeldMeta === 15){
            $pos = new Vector3($target->x, $target->y, $target->z, $target->level);
            $level = $target->level;
            $flowerCount = rand(2, 5); // Random number of flowers to generate
            $placedCount = 0;
            $differentFlowerPlaced = false;
    
            $positions = [];
            for($x = -3; $x <= 3; $x++){
                for($y = -1; $y <= 2; $y++){
                    for($z = -3; $z <= 3; $z++){
                        $positions[] = new Vector3($pos->x + $x, $pos->y + $y, $pos->z + $z);
                    }
                }
            }
            
            usort($positions, function($a, $b) use ($pos) {
                return $a->distance($pos) <=> $b->distance($pos);
            });
            
            $firstPart = array_splice($positions, 0, count($positions) / 3);
            shuffle($firstPart);
            
            $positions = array_merge($firstPart, $positions);
            
            foreach($positions as $newPos){
                if($placedCount >= $flowerCount){
                    break;
                }
            
                if($level->getBlock($newPos)->getID() === AIR && 
                   ($level->getBlock($newPos->subtract(0, 1, 0))->getID() === GRASS || 
                    $level->getBlock($newPos->subtract(0, 1, 0))->getID() === DIRT)){
                   
                    $flowerType = $targetID;
                    if(!$differentFlowerPlaced && rand(0, 9) === 0){
                        $flowerType = $targetID === DANDELION ? CYAN_FLOWER : DANDELION;
                        $differentFlowerPlaced = true;
                    }
            
                    $flower = BlockAPI::get($flowerType);
                    $level->setBlock($newPos, $flower, true, false, true);
                    $placedCount++;
                }
            }
    
            if($player->getGamemode() === "survival"){
                $player->removeItem($itemHeldID, $itemHeld->getMetadata(), 1, true);
            }
        }
    }

    public function __destruct() {}
}