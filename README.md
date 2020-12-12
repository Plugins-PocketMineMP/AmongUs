# AmongUs

A PocketMine-MP mini-game Plugin called AmongUs
This is simlar to the AmongUs game but in MCBE.

Join our AmongUs test server (1.16.200)
```
🔹️IP: test.alvin0319.ml
🔸️Port: 25578
```

## This is yet not completed!

This project is still in progress, if you want to contribute my project, make a pull request!

You can get any updates or progress on my [Discord Server](https://discord.gg/Py2vSwg3B3).

If you have any problems please open a issue or join our discord server for support.

## Changelogs

- 0.0.1: Initial commit.
- 0.0.2: Implemented basic core API.
- 0.0.3: Implemented objectives.
- 0.0.4: Implemented DeadPlayerEntity.
- 0.0.5: Implemented event handlers.
- 0.0.6: Implemented FilledMap.
- 0.0.7: Implemented DisplayTextTask.
- 0.0.8: Added basic event API.
- 0.0.9: Now we can play this, but not completed.
- 0.0.10: Fixed my bad english (thanks to @HydroGames-dev)
- 0.0.11: Added Task Complete message & sound.
- 0.0.12: Added bossbar API.
- 0.1.0: Ditched Map-related methods.
- 0.1.1: Implemented Scoreboard API, Added vent entity (Special thanks to [@iMasterProX](https://github.com/iMasterProX))

### Download & Installation

- Check the [Poggit-dev builds](https://poggit.pmmp.io/ci/alvin0319/AmongUs) or [Source code](https://github.com/alvin0319/AmongUs/archive/master.zip) from github.
- Put the plugin in ``/plugins`` Folder
- and put the virions in ``/virions`` folder after adding devirion.
- and restart server and edit config.yml
- If you want the AmongUs Skeld Map [Here](https://cdn.discordapp.com/attachments/773847823955263518/776089161765486613/world.zip),   
it's not Fixed, so manually fix it.

### Dependencies

##### Plugins

* [SimpleMapRenderer](https://poggit.pmmp.io/p/SimpleMapRenderer/1.0.0),   [DEVirion](https://github.com/poggit/devirion)    
> DEVirion will allow virions to work on your server. (these are not virions, put these in ``/plugins`` folder)  

##### Virions

* [InvMenu](https://github.com/Muqsit/InvMenu),   [array-utils](https://github.com/PresentKim/arrayutils),   [png-converter](https://github.com/PresentKim/png-converter) 
> These virions are needed in order for the plugin to function properly.

##### Extensions
* [GD](https://www.php.net/manual/en/book.image.php) extension **MUST** installed in your php binary.

### Default Configuration


```yaml
max_games: 3

world_name: amongus
