# Easy Engine 4

First attempt at rewriting [Easy Engine](https://easyengine.io/) in PHP around [WP-CLI](http://wp-cli.org/).

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

 * `create` - Creates a new site and add a configuration file.
 
    ***Usage***: `wp ee site create one.com --wp` 
    
    Will create a WordPress website and add a configuration file called `one.com.cnf`
 
 * `list` - List all the site names line-by-line.

    ***Usage***: `wp ee site list`
    

 * `info` - Gives information about a particular site in tabular format.

    ***Usage***: `wp ee site info one.com`
    
 * `show` - Displays configuration file for `one.com`.

    ***Usage***: `wp ee site show one.com`


 * `update` - Updates an existing site based on this [chart](https://easyengine.io/docs/commands/site/update/#update-site).

    ***Usage***: `wp ee site update one.com --wpfc`
    
    This command will also update the configuration file for `one.com`
    
    
* `delete` - Deletes a site.

    ***Usage***: `wp ee site delete one.com`
    
    This command will also delete the configuration file for `one.com`
