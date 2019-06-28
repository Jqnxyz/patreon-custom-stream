# Patreon-authenticated stream

## Setting up

Create a client at [Patreon's API portal](https://www.patreon.com/portal/registration/register-clients).
Set `API Version` to `2`.
Use `[host domain/subdomain]/redirect.php` as the redirect URI. **Include https://** 
_e.g. "https://stream.example.com/redirect.php"_.

Create a file *settings.json* in the base directory.

Copy the following JSON data to the file, replacing values with your own.

`[CLIENT ID]` and `[CLIENT SECRET]` were generated and shown in the dialog box when you created your Patreon client. 
`[BASE URL]` is the domain/subdomain you are hosting this at. **Include https://** 
_e.g. "https://stream.example.com"_.
`[REDIRECT URI]` is the redirect URI you entered earlier when creating your Patreon client.
`[YOUR EMAIL]` and `[YOUR PATREON ID]` are self-explanatory.

```JSON
{
    "creator":{
        "email":"[YOUR EMAIL]",
        "ID":"[YOUR PATREON ID (get using API)]"
    },
    "oauth":{
        "clientID":"[CLIENT ID]",
        "clientSecret":"[CLIENT SECRET]",
        "redirectURI":"[REDIRECT URI]"
    },
    "baseURL":"[BASE URL]"
}
```

### Dependencies

You are required to install the PHP cURL library, do the following.
```sh
sudo apt-get install php-curl
sudo service apache2 restart
```