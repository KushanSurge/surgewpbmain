# SurgeWP Boilerplate

A modular, shortcode-based WordPress plugin boilerplate with automatic asset loading, AJAX security, library dependency management, and WP-CLI scaffolding.

---

## Framework Overview

The boilerplate provides a zero-friction foundation for shortcode plugins. Shortcodes are fully self-contained modules. Adding a new shortcode requires only creating a folder and a class — everything else (registration, asset loading, AJAX wiring) is automatic.

---

## Folder Structure

```
surgewpbmain/
├── surgewpbmain.php                  # Plugin entry point
├── includes/
│   ├── core/
│   │   ├── loader.php               # Shortcode auto-discovery & registration
│   │   ├── asset-loader.php         # Per-shortcode CSS/JS enqueue
│   │   ├── lib-loader.php           # Shared library enqueue
│   │   └── cli.php                  # WP-CLI generator command
│   ├── common/
│   │   ├── surgewpb-common.js       # AJAX helper, module init, post-loop
│   │   └── surgewpb-common.css      # Shared utility classes
│   ├── libs/
│   │   ├── swiper.php               # Swiper library definition
│   │   └── aos.php                  # AOS library definition
│   └── shortcodes/
│       ├── helloworld/
│       │   └── helloworld.php
│       ├── otp-generator/
│       │   ├── otp-generator.php
│       │   └── otp-generator.js
│       └── post-loop/
│           ├── post-loop.php
│           └── post-loop.css
└── assets/
    └── libs/
        ├── swiper/
        │   ├── swiper.js
        │   └── swiper.css
        └── aos/
            ├── aos.js
            └── aos.css
```

---

## Shortcode Auto-Discovery

The loader scans `includes/shortcodes/` for subdirectories. For each folder:

1. Loads `<folder>/<folder>.php`
2. Derives the class name from the folder name: `otp-generator` → `Surgewpb_Otp_Generator`
3. Instantiates the class (registering any AJAX handlers in the constructor)
4. On the `wp` hook, checks if the shortcode tag appears in the current post content
5. Calls `add_shortcode()` only if the tag is found

Detection uses `has_shortcode()` with a `strpos` fallback for edge cases.

---

## Shortcode Class Structure

Every shortcode class must:

- Be named `Surgewpb_<PascalCase>` matching its folder name
- Define `public $tag` containing the shortcode tag string
- Define a `render( $atts, $content )` method
- Call `Surgewpb_Asset_Loader::load()` at the top of `render()`

Optional properties:

```php
public $use_ajax = true;       // Enables JS localization with ajax_url + nonce
public $libs     = ['swiper']; // Declares required shared libraries
```

Minimal example:

```php
class Surgewpb_My_Widget {

    public $tag      = 'my_widget';
    public $use_ajax = false;
    public $libs     = [];

    public function render( $atts, $content = null ) {
        Surgewpb_Asset_Loader::load( 'my-widget', $this->use_ajax, $this->libs );
        return '<div class="surgewpb-my-widget">Hello</div>';
    }
}
```

---

## Automatic Asset Loading

When `Surgewpb_Asset_Loader::load( $name, $use_ajax, $libs )` is called inside `render()`:

1. Any declared `$libs` are loaded via the library loader
2. If `includes/shortcodes/<name>/<name>.css` exists → enqueued
3. If `includes/shortcodes/<name>/<name>.js` exists → enqueued
4. Duplicate enqueues are prevented via an internal registry

Assets depend on `surgewpb-common-css` and `surgewpb-common-js` automatically.

---

## Library Dependency System

Declare a library in your shortcode class:

```php
public $libs = ['swiper'];
```

Define the library in `includes/libs/swiper.php`:

```php
return [
    'handle' => 'surgewpb-swiper',
    'css'    => [ 'assets/libs/swiper/swiper.css' ],
    'js'     => [ 'assets/libs/swiper/swiper.js' ],
];
```

Place the actual vendor files in `assets/libs/swiper/`. Each library is loaded only once per page regardless of how many shortcodes declare it.

---

## Enabling AJAX

Set `$use_ajax = true` on your shortcode class:

```php
public $use_ajax = true;
```

Register your AJAX handler in the constructor:

```php
public function __construct() {
    add_action( 'wp_ajax_surgewpb_my_action',        [ $this, 'ajax_handler' ] );
    add_action( 'wp_ajax_nopriv_surgewpb_my_action', [ $this, 'ajax_handler' ] );
}
```

In your handler, always verify the nonce first:

```php
public function ajax_handler() {
    check_ajax_referer( 'surgewpb_nonce', 'nonce' );
    // sanitize inputs, process, respond
    wp_send_json_success( [ 'key' => sanitize_text_field( $_POST['key'] ) ] );
}
```

The shortcode's JS file will receive a localized object named `surgewpb_<name>_data` containing `ajax_url` and `nonce`. Use the common AJAX helper for convenience:

```js
surgewpb.ajax( 'surgewpb_my_action', { key: 'value' }, function ( response ) {
    if ( response.success ) { /* handle */ }
} );
```

---

## JS Module Pattern

Shortcode JS files register named modules into `surgewpb.modules`. Common JS auto-initializes them by matching `data-surgewpb-module` attributes in the DOM.

In your shortcode PHP render output:

```html
<div data-surgewpb-module="my_widget">...</div>
```

In your shortcode JS:

```js
( function ( surgewpb ) {
    surgewpb.modules.my_widget = function ( element ) {
        var $el = jQuery( element );
        // initialise here
    };
} )( window.surgewpb = window.surgewpb || { modules: {} } );
```

No `DOMContentLoaded` needed. Common JS calls every registered module once on document ready.

---

## WP-CLI Generator

Generate a new shortcode module scaffold:

```bash
wp surgewpb make:shortcode <name>
```

Example:

```bash
wp surgewpb make:shortcode my-slider
```

Creates:

```
includes/shortcodes/my-slider/
├── my-slider.php   (class Surgewpb_My_Slider, tag [my_slider])
├── my-slider.js
└── my-slider.css
```

The generated class includes `render()`, `Surgewpb_Asset_Loader::load()`, and the module JS pattern — ready to use immediately.

---

## Security Practices

- All AJAX handlers call `check_ajax_referer( 'surgewpb_nonce', 'nonce' )` before any logic
- All user inputs are passed through `absint()`, `sanitize_text_field()`, `sanitize_key()`, or equivalent
- All output is escaped with `esc_html()`, `esc_url()`, or `esc_attr()` before rendering
- Nonces are generated fresh per page load and localized via `wp_localize_script()`
- Direct file access is blocked on all PHP files with `defined( 'ABSPATH' ) || exit`

---

## Renaming the Prefix

All identifiers use the `surgewpb` prefix. To rename for a specific project:

1. Run a case-sensitive find-and-replace across the entire plugin folder:
   - `surgewpb_` → `myplugin_`  (PHP functions, constants, AJAX actions, nonces, JS objects)
   - `surgewpb-` → `myplugin-`  (CSS/JS handles, CSS class prefixes)
   - `surgewpb`  → `myplugin`   (textdomain, class name prefix, folder name)
2. Rename the plugin folder and main PHP file to match
3. Update the `Plugin Name`, `Text Domain`, and constant values in the main file

Using a tool like VS Code's workspace-wide replace or `sed -i` ensures nothing is missed.
