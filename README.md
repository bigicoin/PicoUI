# Pico UI

This repositoru contains various UI-related plugins for Pico CMS.

## Why use these

Pico CMS is a great, simple CMS and there are a lot of themes and templates available. It is however
still primarily designed for text content sites.

Most themes can provide some sort of neatly designed homepage, that primarily uses the auto menu
(`pages` array generated by Pico).

If you actually maintain a blog-like content site with featured content and want to manually maintain
your featured articles on the homepage, this becomes a challenge -- you have to create manual HTML/CSS
for your featured links.

Worse yet, if you have a moderately-sized site with hundreds of pages, loading the pages array slow
down Pico a lot, and you may have to use something like my [TooManyPages](https://github.com/bigicoin/PicoTooManyPages)
plugin to keep your site running fast, and you won't get the `pages` array populated at all for the
auto-menus on most themes.

# Pico UI: Card

Create a card (with a hyperlink) such as this:

![image](https://cloud.githubusercontent.com/assets/5854176/24847145/fc8b6728-1d73-11e7-9f25-11b93a3ae564.png)

Using code that looks like below in your markdown file:

```
[ui.card href="https://google.com" img="http://i.imgur.com/TkTgq3L.jpg"]
[title]Cute Dog[/title]
[text]This page is about this cute dog.[/text]
[/ui.card]
```

## Installation

Installation is simple. Simply drop the `PicoUICard.php` file into the `plugins` directory of your Pico installation.

## Configuration

The plugin will not be enabled by default, so simply add the following line to your
`config/config.php` file to enable it:

```
$config[ 'PicoUICard.enabled' ] = true;
```

*Bootstrap*: Bootstrap is a CSS and JS framework used commonly on many web sites. Pico UI Card relies on
some Bootstrap CSS elements to style and position cards properly.

If your theme already includes Bootstrap, you are good. Default config will not include an extra copy of it.

If you don't, you can enable this setting to load Bootstrap from their official CDN.

```
$config[ 'PicoUICard.loadBootstrap' ] = true;
```

*Card size and positioning*: Pico UI Cards are wrapped within a `row` class (Bootstrap), and so each card
has a definition of column size. (See [Bootstrap: Grid System](http://getbootstrap.com/css/#grid))

Use the following config to change it. The default value is: `col-md-4 col-sm-6 col-xs-6`.

```
$config[ 'PicoUICard.cssClass.cardPosition' ] = 'col-md-4 col-sm-6 col-xs-6';
```

*Title text and description text*: You can define your own CSS classes for the title text and description text.
Typically the use case for this would be customizing font sizes, colors, etc.

```
$config[ 'PicoUICard.cssClass.cardTitle' ] = 'my-title-class';
$config[ 'PicoUICard.cssClass.cardText' ] = 'my-description-class';
```
