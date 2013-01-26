Plugin for MTG Card Tooltips
============================

Enables card images to show on mouseover for Magic the Gathering cards.

Part of the code is taken from the *Snippets* joomla plugin, by Peter
 van Westen. This plugin is thus licensed GPL v2.


Description
-----------

[deckbox.org](http://deckbox.org) provides a javascript utility that enables links to its 
Magic the Gathering card pages to automatically show card image tooltips on hover. 
For more information see [the tooltips integration page](http://deckbox.org/help/tooltips).

This **Joomla** plugin provides a `{mtg}` tag that turns a simple card list into links 
to card pages. It automatically includes the tooltip.js file that provides the
mouseover functionality.

This plugin works across all joomla extensions such as the Kuena forum software.

Joomla 1.x and 2.x are supported. Joomla 3.x is **not** supported yet.

Usage
-----

To install use the Extension Manager page of your Joomla administrative interface. You
can get the plugin file 
[by clicking here](https://github.com/SebastianZaha/joomla_mtg_tooltips/archive/master.zip).


After installing and **activating it** you can use the following syntax in your
articles to create a card link:
```
{mtg Lightning Bolt}
```

Multiple line card listings also work:
```
{mtg
     2 Black Lotus
     2 Taiga
}
```

Examples
--------

[Atlantis Comics](http://www.atlantis-comics.com/trading-card-games/deck-lists/216-star-city-iq-january-20th-1st-place-scott-fagen.html) 
use this plugin. The deck listing in the linked page shows the result of wrapping a card list
in the `{mtg}` tag.


Support and development
-----------------------

If you run into problems installing or using the plugin, please contact 
[support@deckbox.org](mailto:support@deckbox.org).

If you would like to contribute, I will gladly accept pull requests.
