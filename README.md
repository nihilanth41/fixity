# Fixity

## Introduction 

The purpose of this project is to provide a mechanism for tracking file fixity.
The project utilizes a custom version of [Cwilper's Fixi](https://github.com/cwilper/fixi) which has been modified to:
 1. Run as a background process, tracking changes without user input.  
 2. Provide a web interface to view changes that have occured.  

## Requirements 

- Ruby 2.3 \* 
- Ruby gems:
  * Rake
  * Bundler
  * Mail

- SQLite3 development headers 
- PHP 5.x (Tested with 5.3.3 and 5.5.9) 
  * php-pdo

 \* Technically the requirement is Ruby v1.9 or greater, but I ran into issues with broken gem depedencies that are automatically resolved by using a newer version.  
## Installation 

```
git clone https://github.com/nihilanth41/fixity.git
cd fixity/fixi
[rake test]
sudo rake install 
sudo ../install.sh 
```

### RHEL6 

```
[sudo] yum install -y php-pdo.x86_64 sqlite3-devel.x86_64
[sudo] service httpd restart
```

#### PHP

- Set date.timezone in `/etc/php.ini` to suppress a php warning:

```
date.timezone = 'America/Chicago'
```

