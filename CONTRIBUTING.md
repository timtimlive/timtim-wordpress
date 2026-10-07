# Contributing

Thank you for helping. Small, clear changes are the easiest to accept.

## How it works

1. Open an issue first for anything bigger than a typo, so we can agree on it.
2. Fork, change the code in `timtim-live-events/`, and open a pull request.
3. CI checks every PHP file on PHP 7.4 to 8.3. Please keep it green.

## The rules this plugin keeps

- It talks to one place only: `https://timtim.live`.
- The key never reaches a visitor's browser. Every API call happens on the server.
- Every output is escaped (`esc_html`, `esc_attr`, `esc_url`); every input is sanitized.
- Settings changes check `manage_options` and a nonce.
- Words a person reads go through `__()` with the `timtim-live-events` text domain.
- Keep it simple enough that anyone can set it up in two minutes.

## How a change ships

TimTim.Live builds and signs off each release, then publishes it at
`https://timtim.live/partner-api/wordpress/`, where installed copies update
from. A merged pull request goes out in the next release.

## Be kind

This project follows the [Code of Conduct](https://github.com/timtimlive/.github/blob/main/CODE_OF_CONDUCT.md).

## Security

Never in public. See [SECURITY.md](SECURITY.md).
