# ItemSender

ItemSender lets one player send part of the item stack in their hand to another online player through `/itemsender` or `/itemsend`.

## Requirements and compatibility

- Axolotl-PM API 5.0.0, as declared in [`plugin.yml`](plugin.yml). PHPStan at maximum level passes against the 5.0.0 and 5.49.1 source revisions. Intermediate releases have not been tested.
- PHP 8.1 or newer, as required by those Axolotl-PM source revisions.
- No external plugin or runtime library dependency.

The exact release PHAR has been checked for startup on Axolotl-PM 5.49.1. A Bedrock client transfer and sound check is still needed to verify in-game behavior.

## Installation

Download the PHAR from [GitHub Releases](https://github.com/NhanAZ-Plugins/ItemSender/releases), place it in the server's `plugins` directory, and restart Axolotl-PM.

## Command and permission

```text
/itemsender <player> <amount>
/itemsend <player> <amount>
```

`<player>` must be the complete name of an online player. `<amount>` must be a whole number from 1 through 64 and no greater than the number of items in the sender's held stack. The command cannot be used from the server console or to send to yourself. The `itemsender.command` permission defaults to all players.

ItemSender checks whether the target inventory can accept the requested stack. It adds the items to the target and subtracts only the accepted amount from the sender's held slot. It reports the amount actually received if an inventory change limits the transfer. Sending and error messages are customizable in [`resources/config.yml`](resources/config.yml), which is copied to the plugin data directory on first startup. Invalid message values disable the plugin at startup with an error.

## Build and verification

The [build workflow](.github/workflows/build.yml) tests amount validation and runs PHPStan at maximum level. It then uses [DevTools](https://github.com/NhanAZ/DevTools) to build and inspect a downloadable PHAR. The workflow pins DevTools and Axolotl-PM source to reviewed commits. Run local amount cases with `php tests/transfer-rules.php`.

For a local build, use the [DevTools CLI guide](https://github.com/NhanAZ/DevTools/blob/main/docs/cli.md) with this repository as the project directory. PHAR inspection and a clean server startup do not prove item transfer or client sound behavior.

## Support and license

Report bugs in [GitHub Issues](https://github.com/NhanAZ-Plugins/ItemSender/issues) or contact [NhanAZ Discord](https://discord.gg/j2X83ujT6c). ItemSender retains its existing [GPL-3.0 license](LICENSE).
