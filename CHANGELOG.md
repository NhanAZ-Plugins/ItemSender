# Changelog

## 0.0.7 - 2026-10-10

- Resolve recipients by full online player name to avoid ambiguous transfers.
- Subtract accepted items from the sender's held slot rather than removing matching items elsewhere in the inventory.
- Build sound packets using fields available in Axolotl-PM API 5.0.0 and current protocol revisions.
- Validate message configuration and correct the amount error text.
- Add amount tests, PHPStan maximum level and a pinned DevTools PHAR build workflow.
