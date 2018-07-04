# Mirotic

Mirotic is an MVC framework for Hack + HHVM

Latest Version: 0.0 -- In Development

## Overview

Project Structure:
- `config/`
  - `Mirotic.php` -- Stores app-wide configuration information. Customize at will.
  - `config.ini` -- Additional configuration settings for HHVM
- `bin/`
  - `mir.php` -- Framework util app. Used for code generation, etc.
- `src/`
  - `alive.php` -- File to check if site is up and running
  - `controllers/` -- The logic for a particular route
  - `elements/` -- Place for all XHP elements
  - `exceptions/` -- Place for all exceptions
  - `generated/` -- Place for any (auto)generated code
  - `pages/` -- The HTML for a particular route
  - `utils/` -- Various utility classes/functions
- `public/`
  - `css` -- Where all the CSS lives
  - `img` -- Where all the images live
  - `index.php` -- Entrypoint to the application
  - `js` -- Where all the JS lives


## Installation

## Running

To run the server locally for debugging,

```
hhvm -m server -c config/config.ini -p 8080
```

The site will be available at `http://localhost:8080`.

