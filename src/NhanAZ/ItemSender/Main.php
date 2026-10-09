<?php

declare(strict_types=1);

namespace NhanAZ\ItemSender;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

	private const MESSAGE_KEYS = [
		"cmdInTerminal", "argumentsBlank", "invalidTarget", "invalidInput", "sendYourself",
		"sendAir", "notEnoughItems", "notEnoughSpace", "sentSuccessfully", "receivedSuccessfully"
	];

	/** @var array<string, string> */
	private array $messages = [];

	protected function onEnable(): void {
		$this->saveDefaultConfig();
		foreach (self::MESSAGE_KEYS as $key) {
			$message = $this->getConfig()->get($key);
			if (!is_string($message)) {
				$this->getLogger()->error("ItemSender config entry $key must be a string.");
				$this->getServer()->getPluginManager()->disablePlugin($this);
				return;
			}
			$this->messages[$key] = $message;
		}
	}

	/** @param array<string, string> $replacements */
	private function message(string $key, array $replacements = []): string {
		$template = $this->messages[$key] ?? throw new \LogicException("Missing ItemSender message $key.");
		return TextFormat::colorize(str_replace(array_keys($replacements), array_values($replacements), $template));
	}

	private function playSound(Player $player, string $soundName): void {
		$position = $player->getPosition();
		$packet = new PlaySoundPacket();
		$packet->soundName = $soundName;
		$packet->x = $position->getX();
		$packet->y = $position->getY();
		$packet->z = $position->getZ();
		$packet->volume = 1.0;
		$packet->pitch = 1.0;
		$player->getNetworkSession()->sendDataPacket($packet);
	}

	/** @param array<string, string> $replacements */
	private function reject(Player $sender, string $key, array $replacements = []): bool {
		$sender->sendMessage($this->message($key, $replacements));
		$this->playSound($sender, "mob.villager.no");
		return true;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if ($command->getName() !== "itemsender") {
			return false;
		}
		if (!$sender instanceof Player) {
			$sender->sendMessage($this->message("cmdInTerminal"));
			return true;
		}
		if (!isset($args[0], $args[1])) {
			return $this->reject($sender, "argumentsBlank");
		}
		$target = $this->getServer()->getPlayerExact($args[0]);
		if ($target === null) {
			return $this->reject($sender, "invalidTarget", ["{target}" => $args[0]]);
		}
		$amount = TransferRules::parseAmount($args[1]);
		if ($amount === null) {
			return $this->reject($sender, "invalidInput");
		}
		if ($target === $sender) {
			return $this->reject($sender, "sendYourself");
		}

		$senderInventory = $sender->getInventory();
		$item = $senderInventory->getItemInHand();
		$itemName = $item->getName();
		$targetName = $target->getName();
		if ($item->isNull()) {
			return $this->reject($sender, "sendAir", ["{air}" => $itemName, "{target}" => $targetName]);
		}
		if ($item->getCount() < $amount) {
			return $this->reject($sender, "notEnoughItems", ["{itemCount}" => (string) $amount, "{itemName}" => $itemName, "{target}" => $targetName]);
		}

		$sendStack = clone $item;
		$sendStack->setCount($amount);
		$targetInventory = $target->getInventory();
		if (!$targetInventory->canAddItem($sendStack)) {
			return $this->reject($sender, "notEnoughSpace", ["{target}" => $targetName]);
		}
		$leftovers = $targetInventory->addItem($sendStack);
		$notAdded = 0;
		foreach ($leftovers as $leftover) {
			$notAdded += $leftover->getCount();
		}
		$sent = $amount - $notAdded;
		if ($sent <= 0) {
			return $this->reject($sender, "notEnoughSpace", ["{target}" => $targetName]);
		}
		$item->setCount($item->getCount() - $sent);
		$senderInventory->setItemInHand($item);

		$details = ["{itemCount}" => (string) $sent, "{itemName}" => $itemName];
		$sender->sendMessage($this->message("sentSuccessfully", $details + ["{target}" => $targetName]));
		$this->playSound($sender, "mob.villager.yes");
		$target->sendMessage($this->message("receivedSuccessfully", $details + ["{target}" => $sender->getName()]));
		$this->playSound($target, "mob.villager.yes");
		return true;
	}
}
