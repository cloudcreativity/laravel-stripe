# Console

## `stripe:query`

You can query Stripe resources using the `stripe:query` Artisan command.
For example, to query charges on your application's account:

```bash
$ php artisan stripe:query charge
```

Or to query a specific charge on a connected account:

```bash
$ php artisan stripe:query charge ch_4X8JtIYiSwHJ0o --account=acct_hrGMqodSZxqRuTM1
```

The options available are:

```
Usage:
  stripe:query [options] [--] <resource> [<id>]

Arguments:
  resource                 The resource name
  id                       The resource id

Options:
  -A, --account[=ACCOUNT]  The connected account
  -e, --expand[=EXPAND]    The paths to expand (multiple values allowed)
```

> This command is provided for debugging data in your Stripe API.
