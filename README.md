#Google Photos Deleter
A tool I wrote for myself, to cut ties with Google Photos / Picasa Web Albums

## Dependencies
* PHP 5.5+
* [Composer](https://getcomposer.org/)

##Installation
```shell
git clone git@github.com:andreas-glaser/google-photos-deleter.git
cd google-photos-deleter
composer install
```

Create google API credentials + OAuth Client
[https://console.developers.google.com/apis/credentials](https://console.developers.google.com/apis/credentials)

Paste your details into `/config.ini`

##Usage
```shell
./run-server
```
Visit [http://localhost:8000](http://localhost:8000)
