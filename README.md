# TimTim.Live Events for WordPress

**Show live events on your WordPress site. No code.**

A block and a shortcode that show events from TimTim.Live. Each **Get Tickets**
button is your own TimTim.Live link, so people you send are counted for you.

> TimTim.Live Developer Preview. Live demo: https://timtim.live/developers/demo

## Install (2 minutes)

1. Download the plugin zip: https://timtim.live/partner-api/wordpress/timtim-live-events.zip
2. In WordPress: **Plugins → Add New → Upload Plugin**, choose the zip, then **Activate**.
3. Go to **Settings → TimTim.Live Events** and paste your key. A free test key (`tt_test_…`) works.
4. Press **Test connection**.
5. Add the **TimTim.Live Events** block to a page, or paste `[timtim_events city="Miami" category="music"]`.

You need a key. Get a free test key on the
[partner dashboard](https://timtim.live/partners/dashboard): test keys show
sample events only, and no real money moves. For real events, use a website
key (`tt_pk_live_…`). The key stays on your server; visitors never see it.

## What it does

- **Block and shortcode.** City, country, category, number of events and columns; the shortcode also takes `near`, `from`, `to`, `artist` and `earn`.
- **Preview** in the block editor, and a **connection test** on the settings page.
- **Events stay on TimTim.Live.** Answers are kept for a short time as
  WordPress transients, so a slow answer never slows your page; no events are
  copied into your posts. Uninstalling removes the settings and every cached answer.
- **Updates itself from timtim.live.** It is not on WordPress.org. Its
  `Update URI` header stops WordPress.org from ever replacing it with a
  different plugin of the same name.
- **Talks to one place only:** `https://timtim.live`.

## Requirements

WordPress 6.0+, PHP 7.4+. Tested up to WordPress 6.8.

## Where this code lives

The plugin is in [`timtim-live-events/`](timtim-live-events/). Releases are
built and published by TimTim.Live at
`https://timtim.live/partner-api/wordpress/`. This repository is the public
source for that plugin.

## Links

- Live demo: https://timtim.live/developers/demo
- Documentation: https://timtim.live/developers/docs
- Sandbox: https://timtim.live/developers/sandbox
- Developer access: https://timtim.live/developers
- Community: https://timtim.live/developers/community

## Security

Please do not report security problems in public issues. Use **Report a
vulnerability** on this repository's Security tab. See [SECURITY.md](SECURITY.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## TimTim.Live Developer Tools

Open-source tools for connecting websites, apps and platforms to TimTim.Live.

### What is open source

SDKs, widgets, adapters, examples and public API specifications.

### What is not included

This code shows events. It does not include TimTim.Live's own servers. Tickets, payments, payouts, fraud checks and everyone's private data stay with TimTim.Live. You reach them through the API.

These tools connect to the hosted TimTim.Live API at:

https://api.timtim.live

Open-source licenses for client software do not grant ownership of TimTim.Live event data, API services, commercial rights, certification marks or trademarks.

## License

GPL-2.0-or-later, like WordPress itself. See [LICENSE](LICENSE).
