# Lite YouTube Embed

A WordPress mu-plugin that replaces standard YouTube oEmbed iframes with the lightweight [`<lite-youtube>`](https://github.com/nickreese/lite-youtube-embed) custom element for faster page loads.

## Requirements

- PHP 8.2+
- WordPress 6.5+

## Installation

Install via Composer:

```sh
composer require puredazzle/wp-lite-youtube-embed
```

## Development

```sh
pnpm install
pnpm dev     # Vite dev server on localhost:5174
pnpm build   # Production build to /build
```

## Settings

Navigate to **Settings > Lite YouTube Embed** in the WordPress admin to clear the oEmbed cache. This forces WordPress to re-fetch all embeds so existing posts pick up the lite player.

## Credits

This plugin uses [`lite-youtube-embed`](https://www.npmjs.com/package/lite-youtube-embed) by [Paul Irish](https://github.com/nickreese/nickreese/lite-youtube-embed).

## License

[MIT](LICENSE)
