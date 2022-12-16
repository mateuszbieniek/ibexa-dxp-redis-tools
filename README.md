## Description
This bundle offers a tool to analyze your Redis instance for SPI cache in Ibexa DXP-based projects. It provides basic information about Redis instance, suggestions, or even warns about wrong configuration (`maxmemory-policy`). In addition, memory used by non-evictable keys can be calculated, which helps to determine if the `maxmemory` setting is correctly set.

## Usage
Currently, a single command is available
```
php bin/console ibexa:redis-check
```
with additional options:
- `--calculateMemory -m` will calculate non-evitcable memory. Keep in mind it can take several minutes on big databases.
- `--format -f` allowed values: `text`, `json`. Determines how the command output is formatted.

## Installation
### Requirements
This bundle requires Ibexa DXP v3.3+

### 1. Enable `IbexaDxpRedisToolsBundle`
Edit `config/bundles.php`, and add
```
    MateuszBieniek\IbexaDxpRedisToolsBundle\IbexaDxpRedisToolsBundle::class => ['all' => true],
```
at the end of the array

### 2. Install `mateuszbieniek/ibexa-dxp-redis-tools`
```
composer require mateuszbieniek/ibexa-dxp-redis-tools
```