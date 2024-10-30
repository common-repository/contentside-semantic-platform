# 🚀 Welcome to the CSP Plugin repository!

This repository contains the source code of the CSP Plugin for the [CSP](https://doc.contentside.io/swagger-ui/index.html?url=/cs-api-definition/api-csp-media.yaml) API.
The goal of this plugin is to provide a simple way to integrate the CSP API into your Wordpress application.

In the V1.0.0, the plugin provides the following features:
 * Recommandation of links to other similar articles (based on the whole content of the article)
 * Recommandation of links to other similar articles (based on a selected portion of the article)
 * A shortcode to display the list of similar articles
 * A global function for theme developers to display the list of similar articles as they want

## Get a CSP API key
To be able to use this plugin, you must provide an API key.
If you do not have one yet, please contact [Contenside](https://www.contentside.com/contact/).

## Installation
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for "ContentSide Semantic Platform" and click "Install now"
2. Alternatively, download the plugin, upload and extract the contents of `csp-plugin.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
3. Activate the plugin
4. Go to *Tools > CSP* and enter your API key
5. Start the article synchronization for related posts generation
6. Change whatever setting you want to match your needs
7. Enjoy!

## Recommandation of similar posts
The plugin provides two ways to recommend similar posts:
 * Based on the whole content of the article
 * Based on a selected portion of the article

