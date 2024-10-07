# pinghook
Simple WordPress plugin that posts a JSON webhook to an external API URL to notify when a post or page is published, updated, or deleted.

We ignore draft and autosaves, and only trigger these webhooks when a post or page is explicitly saved, published or deleted.

## Installation
Simply upload the zip file to your WordPress plugins directory and activate.

## Setup
In WordPress, click on "pinghook" in the menu. Enter the URL of the external API, and--if required--an API secret to be provided to that API with each request.
