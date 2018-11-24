<?php

namespace Main;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;

class main extends PluginBase implements Listener{
 
    public function onEnable(){
        $this->getLogger()->info('Informationが読み込まれました');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{
	    switch (strtolower($command->getName())) {//コマンド名で条件分岐
		    case "myxyz": //コマンドが「myxyz」だったら以下の処理を実行
			    $name = $sender->getName(); //ユーザー名
			    $x = floor($sender->getX()); //座標
			    $y = floor($sender->getY()); //座標
			    $z = floor($sender->getZ()); //座標
			    $this->getServer()->broadcastMessage("§a[INFO] §f{$name}は座標{$x},{$y},{$z}にいます");
			    break;
		    case "myworld": //コマンドが「myworld」だったら以下の処理を実行
			    $name = $sender->getName(); //ユーザー名
			    $world = $sender->getLevel(); //ワールド取得
			    $LevelName = $sender->getLevel()->getFolderName(); //ワールド取得
			    $this->getServer()->broadcastMessage("§a[INFO] §f{$name}は{$LevelName}にいます");
			    break;
		    case "info": //コマンドが「info」だったら以下の処理を実行
			    $name = $sender->getName(); //ユーザー名
			    $x = floor($sender->getX()); //座標
			    $y = floor($sender->getY()); //座標
			    $z = floor($sender->getZ()); //座標
			    $world = $sender->getLevel(); //ワールド取得
			    $LevelName = $sender->getLevel()->getFolderName(); //ワールド取得
			    $this->getServer()->broadcastMessage("§a[INFO] §f{$name}は{$LevelName}の座標{$x},{$y},{$z}にいます");
			    break;
	    }

   	    //条件に一致しなかった場合処理を終了する
	    //plugin.ymlに記載した使用方法がプレイヤーにメッセージとして送信されます。
	    return true;
    }
    public function onTap(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $name = $player->getName();
    
        // 一定時間タップ出来ないようにする (PCだと意図しない高速タップが発生するため)
        if(!isset($this->interactTick[$name])){
            $this->interactTick[$name] = 0;
        }
        $tick = $this->getServer()->getTick();
        if($tick - $this->interactTick[$name] < 20){ //1秒
            return;
        }
        $this->interactTick[$name] = $tick;
    
        $block = $event->getBlock();
        $sign = $block->getLevel()->getTile($block);
        if($sign instanceof Sign){
            $text = implode("", $sign->getText()); //文字列をすべて繋げる
        
            // 規制したいコマンド
            $commands = [
                "myxyz",
                "myworld",
                "info",
            ];
        
            foreach($commands as $command){
                if(strpos($text, "##".$command) !== false){
                    // 実行した時間(tick)を記録するための準備
                    if(!isset($this->spamTick[$name])){
                        $this->spamTick[$name] = 0;
                    }
                    // スパムの検出(簡易的)
                    if($tick - $this->spamTick[$name] < 20 * 5){ //5秒
                        $event->setCancelled();
                        $player->sendMessage("§a[INFO] §f連続で使用できません");
                        $player->kick("§a[INFO] §e連続での使用禁止 (スパム行為禁止)",false);//「Kicked by admin」非表示
                        $this->getServer()->broadcastMessage("§a[INFO] §e".$name."はスパムと判断されたためキックされました。");
                        // コードを追加する場合はここに追加してください
                        break;
                    }
                    //実行した時間(tick)を記録
                    $this->spamTick[$name] = $tick;
                }
            }
        }
    }
}