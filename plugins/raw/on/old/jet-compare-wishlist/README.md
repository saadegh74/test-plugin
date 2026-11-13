# Jet Compare Wishlist

## Changelog

### 1.5.13
* Updated: JetDashboard module;
* Fixed: JetCompareWishlist links lacked discernible names, causing an accessibility issue;

### 1.5.12.3
* Updated: Added alternative way to store modules data;

### 1.5.12.2
* Fixed: issue with Wishlist Product Template;

### 1.5.12.1
* Fixed: Error 403 on JetEngine Lazy Load;

### 1.5.12
* Fixed: PHPCS compliance - escaped outputs, sanitized SVG/icon/video HTML, validated GET params, and documented safe ignores;

### 1.5.11
* Fixed: Compare Widget don't work with FSE themes;
* Fixed: Compare/Wishlist button doesn't work in listing that is used as template for Ajax Search widget;
* Added: Default text and background colors for Highlight in Compare Widget;

### 1.5.10.1
* Fixed: Vulnerability to Cross Site Scripting;

### 1.5.10
* Fixed: Vulnerability to Local File Inclusion;

### 1.5.9
* Added: Compatibility with Elementor 3.26;
* Fixed: Error when initializing the Wishlist widget;

### 1.5.8.1
* Fixed: Wishlist and compare buttons display in JetWooBuilder plugin widgets;

### 1.5.8
* Updated: JetDashboard module to 2.2.0 version;
* Updated: Vue UI framework to 1.5.2 version;
* Updated: Styles refactor and enqueue;
* Tweak: Scripts structure;
* Fixed: widgets functionality after JetEngine listing grid lazy loading;

### 1.5.7
* Added: New Editor widgets icons
* Updated: Small Compare widget improvements ([#7487](https://github.com/Crocoblock/suggestions/issues/7487));
* Fixed: Deprecated warnings ([#7488](https://github.com/Crocoblock/suggestions/issues/7488));
* Fixed: Compare & Wishlist buttons functionality in JetPopups called from JetEngine listings;
* Fixed: Compare Widget remove icon initial display;

### 1.5.6
* Added: Hooks for handling actions before adding/removing products in lists;
* Added: JS triggers on adding/removing products in lists;
* Updated: Vue UI module to 1.4.10;
* Updated: JetDashboard to 2.1.7;
* Tweak: Widgets registration;

### 1.5.5.2
* Fixed: Security issue.

### 1.5.5.1
* Updated: JetDashboard module to 2.1.4.

### 1.5.5
* Tweak: Widgets image sizes;
* Tweak: Compare/Wishlist button widgets icon size units;
* Tweak: Required plugin check, admin notification system;
* Fixed: Counts widgets icon spaces ([#6873](https://github.com/Crocoblock/suggestions/issues/6873));
* Fixed: Global link styles override Wishlist/Compare widgets title typography.

### 1.5.4
* Added: Integration with WooCommerce High-Performance Order Storage;
* Added: Elementor 3.10 compatibility with custom size unit;
* Added: `'jet-cw/template-functions/visible-attributes'`, `'jet-cw/template-functions/exclude-attributes'` hooks to controls products attributes

### 1.5.3
* Added: Polylang compatibility;
* Updated: WPML compatibility;
* Updated: JetDashboard module to 2.0.10;
* Updated: Editor control UX;
* Fixed: Variation product title in Compare Table widget.

### 1.5.2
* Added: Add to Wishlist data source in Compare Table widget;
* Added: Possibility to change max items compare message;
* Added: Box shadow style controls;
* Tweak: Dashboard templates;
* Fixed: Default and controls styles.

### 1.5.1
* Fixed: Empty attribute exclude options in Compare Table widget;
* Fixed: Scripts conflict;
* Fixed: Some minor styles issues.

### 1.5.0
* Added: Compatibility with JetWooBuilder 2.0.0 version;
* Added: Clear compare table action button;
* Update: JetDashboard to 2.0.9;
* Tweak: `jet-cw/compare/empty_text` and `jet-cw/wishlist/empty_text` for empty message handle;
* Fixed: JS error on pages without Elementor;
* Fixed: Minor issues.

### 1.4.7
* Added: Compare Table widget Attributes exclude control;
* Added: Compare Table widget Custom Field before & after controls;
* Fixed: Buttons styles.

### 1.4.6
* Updated: Some styles controls;
* Fixed: Session store type site health issue.

### 1.4.5
* Fixed: JS errors;
* Fixed: Wishlist price color style.

### 1.4.4
* Added: Permalink for widgets thumbnails option;
* Tweak: Wishlist widget price styling;
* Fixed: Products id availability in the lists.

### 1.4.3
* Fixed: Minor bugs.

### 1.4.2
* Fixed: Missed buttons icons in JetWooBuilder Products List/Grid widget.

### 1.4.1
* Fixed: Elementor motion effects in Wishlist widget;
* Fixed: Widget localize data.

### 1.4.0
* Added: Compatibility with Elementor Custom Breakpoint;
* Added: Hide empty count functionality in Count Button widgets;
* Added: Cookie store type;
* Fixed: Wishlist & Compare Buttons widget in listing grid with the source of WC_Product_Query.

### 1.3.5
* Updated: String translation;
* Updated: Register widgets category;
* Fixed: Critical error after the product added to list was deleted form site;
* Fixed: Wishlist&Compare Buttons after ajax loading.

### 1.3.4
* Added: Text trim functionality for wishlist title;
* Added: Elementor require version warning;
* Updated: JetDashboard to 2.0.8;
* Updated: String translation.

### 1.3.3
* Added: Additional output validation;
* Fixed: Functionality for not login users;

### 1.3.2
* Updated: JetDashboard options labels;
* Fixed: Count buttons SVG icons color;
* Fixed: Buttons display in up-sales archive cards when variation chosen;
* Fixed: WooCommerce hooks for themes compatibility.

### 1.3.1
* Updated: String translation;
* Fixed: DB Updater.

### 1.3.0
* Added: Wishlist widget card presets;
* Added: Upgrader;
* Fixed: Not a product ID in Compare&Wishlist sessions;
* Fixed: Wishlist widget remove button position when featured image is empty.

### 1.2.4
* Added: filter `jet-cw/template-functions/compare-custom-field/$field_key` for handling custom fields values;
* Tweak: Compare and Wishlist getting data;
* Fixed: Minor bugs.

### 1.2.3
* Added: Custom fields in Compare Table widget;
* Added: Difference controls in compare table widget;
* Updated: JetDashboard to 2.0.6;
* Updated: String translation;
* Fixed: WPML module error.

### 1.2.2
* Added: JetWooBuilder product card template in Wishlist widget;
* Added: Cell highlight functionality in the Compare Table widget;
* Updated: JetDashboard 2.0.5;
* Updated: String translation;
* Fixed: wrong products counter when added product not publish;
* Fixed: Undefined variable in quick view popup;
* Fixed: Compare table scrolling.

### 1.2.1
* Fixed: View details popup.

### 1.2.0
* Added: Better RTL compatibility;
* Added: Possibility to remove the product from the compare/wishlist without having to go to the page;
* Added: Wishlist thumbnail border radius controls;
* Updated: JetDashboard to 1.1.0;
* Tweak: Compare&Wishlist Button widgets state tabs control position;
* Fixed: Compare&Wishlist Button widgets default background colors;
* Fixed: Compare&Wishlist Button widgets added icon color;
* Fixed: Visibility of the Hidden border container control for the Compare widget.

### 1.1.2
* Fixed: Compare/Wishlist widgets style controls;
* Fixed: Check JetCompareWishlist widgets availability in quick view popup;
* Fixed: Wishlist default icon.

### 1.1.1
* Updated: JetDashboard to 1.0.12;
* Fixed: Minor bugs.

### 1.1.0
* Added: Option for customizing Heading Tags in widgets;
* Added: Compatibility with new Icons control;
* Added: Jet Dashboard;
* Updated: Stock status style controls;
* Fixed: Minor bugs.

### 1.0.2
* Added: Need helps links to widgets;
* Added: Changelog;
* Added: JetPopups compatibility;
* Fixed: Minor bugs.

### 1.0.1
* Fixed: Bug with add default option.

### 1.0.0