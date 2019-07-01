# Patreon custom stream

Open-source PHP-based database-less private Patreon stream service.

Utilises Patreon and Google integration for ease of use.

## Setting up

Create a client at [Patreon's API portal](https://www.patreon.com/portal/registration/register-clients).
Set `API Version` to `2`.
Use `https://[host domain/subdomain]/redirect.php` as the redirect URI.
_e.g. "https://stream.example.com/redirect.php"_.

Create a project, then create an OAuth Client ID at [Google's developer console](https://console.developers.google.com/apis/dashboard).
Set `Application Type` to `Web application`.
Insert `https://[host domain/subdomain]` as an Authorised JavaScript origin.
Insert `https://[host domain/subdomain]/redirect.php` as an Authorized redirect URI.
Download the `client_secret.json` file, save it to the project directory.

Create an API Key.
Click on `Restrict Key`, select `HTTP Referrers` under Application Referrers. 
Under Website restrictions, add `https://*.[host domain]/*` as a new item.


Create a file *settings.json* in the base directoryy.

Copy the following JSON data to the file, replacing values with your own.

```json
{
    "creator": {
        "userID": "[PATREON USER ID]",
        "gAccess": "",
        "ytSearch": "[YT META]",
        "ytKeyword": "[YT KEYWORD]"
    },
    "oauth": {
        "clientID":"[PATREON OAUTH CLIENT ID]",
        "clientSecret":"[PATREON OAUTH CLIENT SECRET]",
        "redirectURI":"[REDIRECT URI]"
    },
    "google-oauth": {
        "clientID": "[GOOGLE OAUTH CLIENT ID]",
        "clientSecret": "[GOOGLE OAUTH SECRET]",
        "API": "[GOOGLE API KEY]",
        "redirectURI": "[REDIRECT URI]"
    },
    "baseURL": "[BASE URL]"
}
```
`[PATREON USER ID]` can be obtained by opening `redirect.php` and un-commenting `file_put_contents('latest_patreon_login_id.txt',$patron_response['data']['id']);`. After logging in, this will create a file `latest_patreon_login_id.txt` which will contain the latest Patreon login's user's ID. Make sure to comment the line again after obtaining your user ID.
`[YT META]` can be set either to `title` or `description`. This determines which metadata the scanner will check for the keyword (or phrase) specified in `[YT KEYWORD]`.

`[PATREON OAUTH CLIENT ID]` and `[PATREON OAUTH CLIENT SECRET]` were generated and shown in the dialog box when you created your Patreon client. 
`[REDIRECT URI]` is the redirect URI you entered earlier when creating your Patreon client.

`[GOOGLE OAUTH CLIENT ID]`, `[GOOGLE OAUTH CLIENT SECRET]`, and `[GOOGLE API KEY]` were generated and shown when you created your Google OAuth client earlier. 
`[REDIRECT URI]` is the redirect URI you entered earlier when creating your Google OAuth client.

`[BASE URL]` is the domain/subdomain you are hosting this at. **Include https://** 
_e.g. "https://stream.example.com"_.

### Crontab

Add the following to your crontab file (`sudo crontab -e`), replacing `[PATH TO PROJECT DIRECTORY]` with the appropriate path.

`*/30 * * * * /usr/bin/php [PATH TO PROJECT DIRECTORY]/manage_endpoint.php 'grabYTID' >/dev/null 2>&1`

This facilitates the automatic stream ID grabbing by running `manage_endpoint.php` every 30 minutes.


### Dependencies

You are required to install the PHP cURL library, do the following.
```sh
sudo apt-get install php-curl
sudo service apache2 restart
```