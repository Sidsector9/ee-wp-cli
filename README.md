# WP CLI Commands for EE

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them:

 1. You need to have **PHP version >= 5.3** installed on your system.
 2. Install WP-CLI on your system. You can find the installation instructions [here](http://wp-cli.org/#installing).
 3. Install SQLite3 driver or enable the extension if driver is already installed.

### Installing

Once your system meets the prerequisites, run the below command:

```
wp package install git@github.com:Sidsector9/ee-wp-cli.git

```

## List of Commands

This first iteration supports the following 6 commands:

 * `create` - Creates a site and it's corresponding configuration file.
    * `wp ee site create example.com`
 * `list` - Lists all the sites in the database.
    * `wp ee site list` 
 * `info` - Shows info about a particular site in tabular format.
    * `wp ee site info example.com` 
 * `show` - Shows configuration details about a particular site.
    * `wp ee site show example.com` 
 * `update` - Updates a site and updates its configuration file.
    * `wp ee site update example.com --wpfc` 
 * `delete` - Deletes a particular site and it's corresponding configuration file.
    * `wp ee:site:delete example.com` 
