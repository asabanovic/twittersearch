# Twitter Search

Application Authentication Only - Search Twitter API

## Installation

Pull the project using composer:

```sh
composer require asabanovic/twittersearch
```

## Usage

Twitter Search is a light version, with app-only authentication (all that is required is an app key and app secret), used to search tweets by its queries defined in the official Twitter documentation (https://dev.twitter.com/rest/public/search).

## Example

```sh
require ('vendor/autoload.php');
use TwitterSearch\Twitter;

$app_key = 'xxxxxxxxxxxxxxxxxxx';
$app_secret = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

$twitter = new Twitter($app_key, $app_secret);
$twitter->searchTweets('#Berlin filter:safe', 5);

$status_code = $twitter->getStatusCode(); // Example: 200 OK
$tweets = $twitter->getResponse(); // Array of objects
```
