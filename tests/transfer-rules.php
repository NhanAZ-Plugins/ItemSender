<?php

declare(strict_types=1);

require dirname(__DIR__) . "/src/NhanAZ/ItemSender/TransferRules.php";

use NhanAZ\ItemSender\TransferRules;

$cases = [
	["1", 1],
	["64", 64],
	["0001", 1],
	["0", null],
	["65", null],
	["-1", null],
	["1.5", null],
	["abc", null],
	["", null],
	["999999999999999999999999999999999999", null],
];

foreach ($cases as [$input, $expected]) {
	if (TransferRules::parseAmount($input) !== $expected) {
		throw new RuntimeException("Unexpected transfer amount result for $input.");
	}
}

echo "PASS: 10 transfer amount cases\n";
