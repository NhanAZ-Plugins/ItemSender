<?php

declare(strict_types=1);

namespace NhanAZ\ItemSender;

final class TransferRules {

	public static function parseAmount(string $input): ?int {
		if ($input === "" || !ctype_digit($input)) {
			return null;
		}
		$amount = (int) $input;
		return $amount >= 1 && $amount <= 64 ? $amount : null;
	}
}
