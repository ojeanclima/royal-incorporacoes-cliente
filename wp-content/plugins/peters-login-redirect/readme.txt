=== LoginWP (Formerly Peter's Login Redirect) ===
Contributors: marketingfire
Tags: after registration redirect, login redirect, logout redirect
Requires at least: 4.9
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 3.0.8.6
License: GPL-2.0+

Redirect users to different locations after they log in, log out and register based on different conditions.

== Description ==

[LoginWP](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion) (formerly Peter's Login Redirect) lets you define a set of redirect rules for specific users, users with specific roles, users with specific capabilities, and a blanket rule for all other users. Also, set a redirect URL for post-registration.

You can use the following placeholders in your URLs so that the system will build a dynamic URL upon each login: **{{username}}**, **{{user_slug}}**, **{{website_url}}**.

Upgrade to [LoginWP PRO](https://loginwp.com/pricing/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion) to redirect users to the current page they are logging in from or back to the previous (or referrer) page after login using **{{current_page}}** and **{{previous_page}}** placeholders. [Learn more](https://loginwp.com/wordpress-redirect-referrer-page-after-login/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion)

You can add your own code logic before and between any of the plugin's normal redirect checks if needed. [See our documentation](https://loginwp.com/docs/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion). Some examples include: redirecting the user based on their IP address and redirecting users to a special page on the first login.

[Website](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion) | [Documentation](https://loginwp.com/docs/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion) | [Support](https://loginwp.com/support/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion)

### Pro Integrations

This is the lite version that works with the default WordPress login page and limited other user registration and login form plugins. Upgrade to Pro to avail the support for the following features and plugins.

* [Redirect After First Login](https://loginwp.com/article/redirect-wordpress-users-after-first-login/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [WooCommerce](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Gravity Forms](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [WPForms](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [LearnDash](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Uncanny Toolkit](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [LifterLMS](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Tutor LMS](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [ProfilePress](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [MemberPress](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [MemberMouse](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [LearnPress](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Easy Digital Downloads](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Restrict Content Pro](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Ultimate Member](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [WP User Manager](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [WP User Frontend](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Paid Memberships Pro](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [WishList Member](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Theme My Login](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [User Registration (WPEverest)](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Elementor Login Form](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [BuddyBoss Theme Login/Registration Form](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)
* [Divi Theme Login/Registration Form](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion#pro-integrations)


== Installation ==

Installing this plugin is just like any other WordPress plugin.
Navigate to your WordPress “Plugins” page, inside of your WordPress dashboard, and follow these instructions:

1. In the search field enter **LoginWP**. Click "Search Plugins", or hit Enter.
1. Select **LoginWP** and click either "Details" or "Install Now".
1. Once installed, click "Activate".

== Screenshots ==

1. Redirection rules overview.
2. Adding a redirection rule.
3. Other settings.
3. Redirect Placeholders.

== Frequently Asked Questions ==

Please visit the [plugin page](https://loginwp.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteversion) with any questions.

Login redirects not working? This plugin uses WordPress's standard login_redirect hook. The usual cause of problems is that another plugin is using the hook first, or there is a custom login form that isn't even running through the standard WordPress login functions.

== Changelog ==

= 3.0.8.6 =
* Fixed: Custom login page redirects to the default WordPress login page when incorrect credentials are entered.

= 3.0.8.5 =
* Fixed: Cookie issue

= 3.0.8.4 =
* Fixed: Advert dismissing issue

= 3.0.8.3 =
* Improved: Compatibility PHP 8.1
* Added: UserSync (Collaboration with FuseWP)

= 3.0.8.0 =
* Improved: Compatibility PHP 8
* Improved: Compatibility with WP 6.2
* Fixed: Assets URL
* Updated: Plugin’s Description

= 3.0.7.0 =
* Added [LearnPress](https://loginwp.com/redirect-wordpress-users-after-login-learnpress/?ref=changelog) integration.
* PHP 8 improvements.
* Fixed Warning: Undefined array key "rul_first_login"
* Added filter to execute integrations conditions last.

See the [changelog file](https://plugins.svn.wordpress.org/peters-login-redirect/trunk/changelog.txt) for full changelog information.
